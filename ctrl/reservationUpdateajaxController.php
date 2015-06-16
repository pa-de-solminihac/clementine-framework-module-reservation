<?php
class reservationUpdateajaxController extends reservationUpdateajaxController_Parent
{
    /**
     *  updateajaxAction : requete AJAX s'occupant principalement du drag and drop des évenements
     *
     *  @access public
     *  @return void
     */
    public function updateajaxAction($request, $params = null)
    {
        $this->data['id_ressource'] = $request->post('int', 'id_ressource');
        if ($request->post('int', 'id_reservation') != 0) {
            $this->data['id_reservation'] = $request->post('int', 'id_reservation');
        } else {
            $this->data['start_date_before'] = $request->post('string', 'date_start_before');
            $fullcalendar_mdl = $this->getModel('fullcalendarresa');
            $this->data['id_reservation'] = $fullcalendar_mdl->getTabId($this->data['start_date_before'], $this->data['id_ressource']);
        }
        $this->data['date'] = $request->post('string', 'date');
        $this->data['nb_recherche'] = $request->get('int', 'nb_recherche');
        $this->data['time_creneaux'] = $request->post('string', 'time_creneaux');
        $this->valide();
    }
    /**
     *  valide : valide correspond aux validate d'un formulaire crud sous update
     *            mais sur une fonction destinée a la requete AJAX d'un drag and drop.
     *
     *
     *  @access public
     *  @return void
     */
    public function valide()
    {
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');
        $fullcalendar_ctrl = $this->getController('fullcalendarresa');
        $fullcalendar_helper = $this->getHelper('fullcalendarresa');
        $reservation_mdl = $this->getModel('reservation');
        $ressource_mdl = $this->getModel('ressource');
        $horaire_mdl = $this->getModel('horaire');
        $number_place_max = $ressource_mdl->getMaximumNumberPlace($this->data['id_ressource']);
        list($year, $month, $days) = explode('-', $this->data['date']);
        list($days, $other) = explode(' ', $days);
        $day = date("Y-m-d", mktime(0, 0, 0, $month, $days, $year));
        $next_day = date("Y-m-d", mktime(0, 0, 0, $month, $days + 1, $year));
        $list_horaire_util = $fullcalendar_mdl->getTotalHorraireResa($this->data['id_ressource'], false, false, $day, $next_day);
        $plage_horaire_horaire = $fullcalendar_mdl->getListCreneauxPossible($this->data['id_ressource'], $day, $next_day, $list_horaire_util);
        $plage_horaire = $fullcalendar_mdl->getListCreneauxSansResa($this->data['id_ressource'], $plage_horaire_horaire, $day, $next_day);
        $plage_horaire_util = $fullcalendar_ctrl->createCalendarUtilisateur($plage_horaire, $this->data['id_ressource'], null, $day, $next_day, $plage_horaire_horaire, $list_horaire_util, $this->data['nb_recherche']);
        $creneaux = $this->data['time_creneaux'];
        $creneaux_sec = $fullcalendar_helper->timeToSecond($creneaux);
        list($start_day, $start_hour) = explode(' ', $this->data['date']);
        $start_hour_sec = $fullcalendar_helper->timeToSecond($start_hour);
        $end_hour_sec = $start_hour_sec + $creneaux_sec;
        $end_hour = $fullcalendar_helper->secondToTime($end_hour_sec);
        $end_date = $start_day . ' ' . $end_hour;
        $my_errors = array();

        $id_horaire_dest = $horaire_mdl->getIdByDateAndRessource($this->data['date'], $end_date, $this->data['id_ressource']);
        if (is_array($this->data['id_reservation'])) {
            $date = $reservation_mdl->getDateById($this->data['id_reservation'][0]['id']);
        } else {
            $date = $reservation_mdl->getDateById($this->data['id_reservation']);
        }
        $id_horaire_act = $horaire_mdl->getIdByDateAndRessource($date['start_date'], $date['end_date'], $this->data['id_ressource']);

        if ($id_horaire_act != $id_horaire_dest) {
            $my_errors['end_date'] = 'La réservation modifié n\'a pas la même horaire';
        }
        $number_place_take = $reservation_mdl->getNbPlacePrise($this->data['date'], $end_date, $this->data['id_ressource']);
        $verif_possible_creneaux = $fullcalendar_ctrl->verifDatePossible($this->data['date'], $end_date, $plage_horaire_util);
        $number_place_remain = $number_place_max - $number_place_take;
        if (is_array($this->data['id_reservation'])) {
            $nb_place_on_resa = 0;
            foreach ($this->data['id_reservation'] as $idR) {
                $nb_place_on_resa = $nb_place_on_resa + $reservation_mdl->getNbPlaceByIdReservation($idR['id']);
            }
        } else {
            $nb_place_on_resa = $reservation_mdl->getNbPlaceByIdReservation($this->data['id_reservation']);
        }

        if ($number_place_remain == 0) {
            $my_errors['number_people'] = 'Il n\'y a plus de place pour ce créneaux';
        } else {
            if ($number_place_remain < $number_place_max) {
                if ($nb_place_on_resa - $number_place_remain > 0) {
                    $my_errors['number_people'] = 'Il ne reste plus que ' . $number_place_remain . ' places pour ce créneaux';
                }
            }
        }

        if ($verif_possible_creneaux == false) {
            $my_errors['start_date'] = 'Le créneaux séléctioné n\'est pas disponible';
        }
        if (!(isset($my_errors['start_date'])) && !(isset($my_errors['number_people']))) {
            if (is_array($this->data['id_reservation'])) {
                foreach ($this->data['id_reservation'] as $idr) {
                    $reservation_mdl->updateAjax($this->data['date'], $end_date, $idr['id']);
                }
            } else {
                $reservation_mdl->updateAjax($this->data['date'], $end_date, $this->data['id_reservation']);
            }
            $result = 1;
            echo $result;
        } else {
            echo '- ';
            foreach ($my_errors as $key => $value) {
                echo $value . ', ';
            }
        }
    }
}
