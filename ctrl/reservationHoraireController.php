<?php
class reservationHoraireController extends reservationHoraireController_Parent
{
    /**
     *  deleteAction : Surcharge l'action delete, pour effacer l'horaire si et seulement si elle n'a pas
     *                 de reservation sur elle
     *
     *  @access public
     *  @return void
     *
     */
    public function deleteAction($request, $params = null)
    {
        $db = $this->getModel('db');
        $id_horaire = $request->GET['clementine_reservation_horaire-id'];
        $id_ressource = $request->GET['clementine_reservation_ressource-id'];
        $reservation_mdl = $this->getModel('reservation');
        $horaire_mdl = $this->getModel('horaire');

        $date_horaire = $horaire_mdl->getDateById($id_horaire);
        $date_reserv = $reservation_mdl->getIdAndDateById($id_ressource);
        if ($this->getReservationHasHoraire($date_horaire, $date_reserv)) {
            header('Location: ' . __WWW__ . '/horaire/update?clementine_reservation_ressource-id=' . $request->GET['clementine_reservation_ressource-id'] . '&clementine_reservation_ressource_has_horaire-ressource_id=' . $request->GET['clementine_reservation_ressource_has_horaire-ressource_id'] . '&clementine_reservation_ressource_has_horaire-horaire_id=' . $request->GET['clementine_reservation_ressource_has_horaire-horaire_id'] . '&clementine_reservation_horaire-id=' . $request->GET['clementine_reservation_horaire-id'] . '&has_horaire=1');
        } else {
            $sql = "DELETE FROM clementine_reservation_ressource_has_horaire WHERE horaire_id = $id_horaire";
            $db->query($sql);
            $sql = "DELETE FROM clementine_reservation_horaire WHERE id = $id_horaire";
            $db->query($sql);
            header('Location: ' . __WWW__ . '/reservation/calendar');
        }

    }

    /**
     *  getReservationHasHoraire : Vérifie qu'un horaire possède au moins une réservation
     *
     *  @access public
     *  @return void
     *
     */
    public function getReservationHasHoraire($date_horaire, $date_reserv)
    {
        $fullcalendar_helper = $this->getHelper('fullcalendarresa');
        foreach ($date_reserv as $key => $value) {
            list($start_date, $start_hour) = explode(' ', $value[0]);
            list($end_date, $end_hour) = explode(' ', $value[1]);
            if ($start_date > $date_horaire["start_date"] && $end_date < $date_horaire["end_date"]) {
                return true;
            }
            if ($start_date == $date_horaire["start_date"]) {
                if ($fullcalendar_helper->timeToSecond($end_hour) > $fullcalendar_helper->timeToSecond($date_horaire["start_hour"]) && $fullcalendar_helper->timeToSecond($end_hour) <= $fullcalendar_helper->timeToSecond($date_horaire["end_hour"])) {
                    return true;
                }
                if ($fullcalendar_helper->timeToSecond($start_hour) > $fullcalendar_helper->timeToSecond($date_horaire["start_hour"]) && $fullcalendar_helper->timeToSecond($start_hour) <= $fullcalendar_helper->timeToSecond($date_horaire["end_hour"])) {
                    return true;
                }
                if ($fullcalendar_helper->timeToSecond($start_hour) == $fullcalendar_helper->timeToSecond($date_horaire["start_hour"])) {
                    return true;
                }
            }
            if ($end_date == $date_horaire["end_date"]) {
                if ($fullcalendar_helper->timeToSecond($end_hour) > $fullcalendar_helper->timeToSecond($date_horaire["start_hour"]) && $fullcalendar_helper->timeToSecond($end_hour) <= $fullcalendar_helper->timeToSecond($date_horaire["end_hour"])) {
                    return true;
                }
                if ($fullcalendar_helper->timeToSecond($start_hour) > $fullcalendar_helper->timeToSecond($date_horaire["start_hour"]) && $fullcalendar_helper->timeToSecond($start_hour) <= $fullcalendar_helper->timeToSecond($date_horaire["end_hour"])) {
                    return true;
                }
                if ($fullcalendar_helper->timeToSecond($start_hour) == $fullcalendar_helper->timeToSecond($date_horaire["start_hour"])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     *  move_fields : Ce charge de placer les champs dans l'ordre
     *
     *  @access public
     *  @return void
     *
     */
    public function move_fields($request, $params = null)
    {
        $ret = parent::move_fields($request, $params);
        $this->moveField('clementine_reservation_horaire.end_date', 'clementine_reservation_horaire.end_hour');
        $this->moveField('clementine_reservation_horaire.maximum_number_place', 'clementine_reservation_horaire.maximum_number_place_by_reservation');
        return $ret;
    }

    /**
     *  override_fields_create_or_update : Ce charge de surcharger les champs dans les creates or update
     *
     *  @access public
     *  @return void
     *
     */
    public function override_fields_create_or_update($request, $params = null)
    {
        $ret = parent::override_fields_create_or_update($request, $params);
        if (clementine::$config['module_fullcalendar']['lang'] == "fr") {
            $add = array(
                1 => 'Horaire normal', -1 => 'Exception suppression'
            );
            $option = array(
                0 => "Non",
                1 => "Oui"
            );
        } else {
            $add = array(
                1 => 'Normal schedule', -1 => 'Exception removal'
            );
            $option = array(
                0 => "No",
                1 => "Yes"
            );
        }
        $this->setFieldValues('clementine_reservation_horaire.option', $option);
        $this->setFieldValues('clementine_reservation_horaire.to_add', $add);
        $this->overrideField('clementine_reservation_ressource.maximum_number_place', array(
            'type' => 'hidden'
        ));
        $this->overrideField('clementine_reservation_ressource.time_creneaux', array(
            'type' => 'hidden'
        ));
        $this->setMandatoryField('clementine_reservation_horaire.start_date');
        $this->setMandatoryField('clementine_reservation_horaire.start_hour');
        $this->setMandatoryField('clementine_reservation_horaire.end_date');
        $this->setMandatoryField('clementine_reservation_horaire.end_hour');
        if ($this->data['formtype'] == 'update') {
            $id_horaire = $request->GET['clementine_reservation_horaire-id'];
            $id_ressource = $this->getModel('horaire')->getRessourceIdById($id_horaire);
            $this->data['id_ressource'] = $id_ressource;
            $reservation_mdl = $this->getModel('reservation');
            $horaire_mdl = $this->getModel('horaire');
            $date_horaire = $horaire_mdl->getDateById($id_horaire);
            $date_reserv = $reservation_mdl->getIdAndDateById($id_ressource);
            if ($this->getReservationHasHoraire($date_horaire, $date_reserv)) {
                $this->overrideField('clementine_reservation_horaire.maximum_number_place', array(
                    'readonly' => 'true'
                ));
                $this->overrideField('clementine_reservation_horaire.maximum_number_place_by_reservation', array(
                    'readonly' => 'true'
                ));
                $this->overrideField('clementine_reservation_horaire.time_creneaux', array(
                    'readonly' => 'true'
                ));
            }
            $this->overrideField('clementine_reservation_ressource.libelle', array(
                'readonly' => 'true'
            ));
        } else {
            $this->overrideField('clementine_reservation_ressource.libelle', array(
                'type' => 'select'
            ));
            $ressource_mdl = $this->getModel('ressource');
            $libelles = $ressource_mdl->getToutLibelle();
            $this->setFieldValues('clementine_reservation_ressource.libelle', $libelles);
        }
        return $ret;
    }
    /**
     *  hide_fields : Affiche le champ libelle
     *
     *  @access public
     *  @return void
     *
     */
    public function hide_fields($request, $params = null)
    {
        $this->unhideField('clementine_reservation_ressource.libelle');
    }

    /**
     *  override_fields_index : Surcharge les champs sur la vue index
     *
     *  @access public
     *  @return void
     *
     */
    public function override_fields_index($request, $params = null)
    {
        if (clementine::$config['module_fullcalendar']['lang'] == "fr") {
            $add = array(-1 => 'Exception suppression',
                1 => 'Horaire normal',
            );
        } else {
            $add = array(-1 => 'Exception removal',
                1 => 'Normal schedule',
            );
        }
        $this->setFieldValues('clementine_reservation_horaire.to_add', $add);
    }
    /**
     *  createAction : surcharge le create pour plus d'information regardé aux docbloc de crud controller, createAction
     *
     *  @access public
     *  @return void
     *
     */
    public function createAction($request, $params = null)
    {
        if ($request->POST) {
            $params['url_retour'] = __WWW__ . '/reservation/calendar?clementine_reservation_ressource-id=' . ($request->POST['clementine_reservation_ressource-libelle'] + 1);
        }
        $reservation_ctrl = $this->getController('reservation');
        $privileges = array(
            'clementine_reservation_gerer_reservation' => true
        );
        $reservation_ctrl->tryAccess($privileges);
        return parent::createAction($request, $params);
    }
    /**
     *  updateAction : surcharge le update pour plus d'information regardé le docbloc de crud controller, updateAction
     *
     *  @access public
     *  @return void
     *
     */
    public function updateAction($request, $params = null)
    {
        $params['url_retour'] = __WWW__ . '/reservation/calendar?clementine_reservation_ressource-id=' . $request->get('int', 'clementine_reservation_ressource-id');
        $reservation_ctrl = $this->getController('reservation');
        $privileges = array(
            'clementine_reservation_gerer_reservation' => true
        );
        $reservation_ctrl->tryAccess($privileges);
        return parent::updateAction($request, $params);
    }
    /**
     *  indexAction : surcharge l'index pour plus d'information regardé le docbloc de crudController, indexAction
     *
     *  @access public
     *  @return void
     *
     */
    public function indexAction($request, $params = null)
    {
        $params['url_retour'] = __WWW__ . '/reservation/calendar';
        $reservation_ctrl = $this->getController('reservation');
        $privileges = array(
            'clementine_reservation_gerer_reservation' => true
        );
        if (isset($request->GET['is_modif']) && $request->GET['is_modif'] == 1) {
            $params['where'] = " clementine_reservation_ressource.id = " . $request->GET['clementine_reservation_ressource-id'];
        }
        $reservation_ctrl->tryAccess($privileges);
        return parent::indexAction($request, $params);
    }
    /**
     *  alter_post : S'occupe de donner des valeurs à des champs caché
     *
     *  @access public
     *  @return void
     *
     */
    public function alter_post($insecure_array, $params = null)
    {
        $insecure_array = parent::alter_post($insecure_array, $params);
        $insecure_array['clementine_reservation_ressource_has_horaire-ressource_id'] = $insecure_array['clementine_reservation_ressource-libelle'] + 1;
        return $insecure_array;
    }
    /**
     *  alter_values : Donne des valeurs par défault à l'affichage
     *
     *  @access public
     *  @return void
     *
     */
    public function alter_values($request, $params = null)
    {
        $ret = parent::alter_values($request, $params);
        $date_debut = $request->get('string', 'date_debut');
        $date_fin = $request->get('string', 'date_fin');
        $id_ressource = $request->get('int', 'id_ressource');
        if ($date_debut != null && $date_fin != null && $id_ressource != null) {
            $this->setDefaultValue('clementine_reservation_horaire.start_date', $date_debut);
            $this->setDefaultValue('clementine_reservation_horaire.end_date', $date_fin);
            $this->setDefaultValue('clementine_reservation_ressource.libelle', $id_ressource - 1);
        } else if (isset($request->GET['clementine_reservation_ressource-id']) && $request->GET['clementine_reservation_ressource-id'] > 0) {
            $this->setDefaultValue('clementine_reservation_ressource.libelle', $request->GET['clementine_reservation_ressource-id'] - 1);
        }
        return $ret;
    }
    /**
     *  rename_fields : Renomme les champs pour qu'ils soient conformes à la vue
     *
     *  @access public
     *  @return void
     *
     */
    public function rename_fields($request, $params = null)
    {
        $ret = parent::rename_fields($request, $params);
        if (clementine::$config['module_fullcalendar']['lang'] == "fr") {
            $this->mapFieldName('clementine_reservation_ressource.libelle', 'Ressource associé');
            if (!empty(clementine::$config['module_fullcalendar']['ressource'])) {
                $this->mapFieldName('clementine_reservation_ressource.libelle', ucfirst(clementine::$config['module_fullcalendar']['ressource']) . ' associé');
            }
            $this->mapFieldName('clementine_reservation_horaire.start_hour', 'Heure de début de l\'exception');
            $this->mapFieldName('clementine_reservation_horaire.start_date', 'Date de début de l\'exception');
            $this->mapFieldName('clementine_reservation_horaire.end_hour', 'Heure de fin de l\'exception');
            $this->mapFieldName('clementine_reservation_horaire.end_date', 'Date de fin de l\'exception');
            $this->mapFieldName('clementine_reservation_horaire.to_add', 'Choix d\'ajout de l\'horraire');
            $this->mapFieldName('clementine_reservation_horaire.maximum_number_place_by_reservation', 'Nombre maximum de place par réservation');
            $this->mapFieldName('clementine_reservation_horaire.comment', 'Commentaire');
            $this->mapFieldName('clementine_reservation_horaire.maximum_number_place', 'Nombre maximum de place durant cette horraire');
            $this->mapFieldName('clementine_reservation_horaire.time_creneaux', 'Temps par créneaux');
        } else {
            $this->mapFieldName('clementine_reservation_ressource.libelle', 'Associated resource');
            if (!empty(clementine::$config['module_fullcalendar']['ressource'])) {
                $this->mapFieldName('clementine_reservation_ressource.libelle', ucfirst(clementine::$config['module_fullcalendar']['ressource']) . ' associate');
            }
            $this->mapFieldName('clementine_reservation_horaire.start_hour', 'Start except time');
            $this->mapFieldName('clementine_reservation_horaire.start_date', 'Start except date');
            $this->mapFieldName('clementine_reservation_horaire.end_hour', 'End except Time');
            $this->mapFieldName('clementine_reservation_horaire.end_date', 'End except date');
            $this->mapFieldName('clementine_reservation_horaire.to_add', 'Choosing addition of horraire');
            $this->mapFieldName('clementine_reservation_horaire.maximum_number_place_by_reservation', 'Maximum space per booking');
            $this->mapFieldName('clementine_reservation_horaire.comment', 'Comment');
            $this->mapFieldName('clementine_reservation_horaire.maximum_number_place', 'Maximum number of places during this horraire');
            $this->mapFieldName('clementine_reservation_horaire.time_creneaux', 'Time by slots');
        }
        return $ret;
    }
    /**
     *  validate : Valide différent principe par exemple une date de fin ne peut pas être inférieur à une date de départ
     *             Pour plus de précisions ce réferer aux validate de crud
     *
     *  @access public
     *  @return void
     *
     */
    public function validate($insecure_values, $insecure_primary_key = null, $params = null)
    {
        $my_errors = parent::validate($insecure_values, $insecure_primary_key, $params);
        $errors = false;
        list($start_year, $start_month, $start_day) = explode('-', $insecure_values['clementine_reservation_horaire-start_date']);
        list($end_year, $end_month, $end_day) = explode('-', $insecure_values['clementine_reservation_horaire-end_date']);
        if (mktime(0, 0, 0, $end_month, $end_day, $end_year) < mktime(0, 0, 0, $start_month, $start_day, $start_year)) {
            $my_errors['start_date'] = 'La date de fin est inférieur à la date de début';
            $errors = true;
        } else if (mktime(0, 0, 0, $end_month, $end_day, $end_year) == mktime(0, 0, 0, $start_month, $start_day, $start_year)) {
            $fullcalendar_helper = $this->getHelper('fullcalendarresa');
            if ($fullcalendar_helper->timeToSecond($insecure_values['clementine_reservation_horaire-end_hour']) < $fullcalendar_helper->timeToSecond($insecure_values['clementine_reservation_horaire-start_hour'])) {
                $my_errors['start_date'] = 'La date de fin est inférieur à la date de début';
                $errors = true;
            }
        }
        $ressource_mdl = $this->getModel('ressource');
        $fullcalendar_ctrl = $this->getController('fullcalendarresa');
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');
        list($year, $month, $days) = explode('-', $insecure_values['clementine_reservation_horaire-start_date']);
        $next_day = date("Y-m-d", mktime(0, 0, 0, $month, $days + 1, $year));

        $horr_start = $fullcalendar_ctrl->getListCreneauxParJour($insecure_values['clementine_reservation_horaire-start_date'], $fullcalendar_mdl->getListCreneauxPossible($insecure_values['clementine_reservation_ressource_has_horaire-ressource_id'], $insecure_values['clementine_reservation_horaire-start_date'], $next_day));

        list($year, $month, $days) = explode('-', $insecure_values['clementine_reservation_horaire-end_date']);
        $next_day_end = date("Y-m-d", mktime(0, 0, 0, $month, $days + 1, $year));
        $horr_end = $fullcalendar_ctrl->getListCreneauxParJour($insecure_values['clementine_reservation_horaire-end_date'], $fullcalendar_mdl->getListCreneauxPossible($insecure_values['clementine_reservation_ressource_has_horaire-ressource_id'], $insecure_values['clementine_reservation_horaire-end_date'], $next_day_end));

        if (!empty($insecure_values['clementine_reservation_horaire-maximum_number_place']) && $insecure_values['clementine_reservation_horaire-maximum_number_place_by_reservation'] > $insecure_values['clementine_reservation_horaire-maximum_number_place']) {
            $my_errors['number_people'] = 'Le nombre de place maximum pour une réservation sur cette horaire est supérieur au nombre de place maximum sur cette horaire';
        } else if (empty($insecure_values['clementine_reservation_horaire-maximum_number_place']) && !empty($insecure_values['clementine_reservation_horaire-maximum_number_place_by_reservation']) && $insecure_values['clementine_reservation_horaire-maximum_number_place_by_reservation'] > $ressource_mdl->getMaximumNumberPlace($insecure_values['clementine_reservation_ressource_has_horaire-ressource_id'])) {
            $my_errors['number_people'] = 'Le nombre de place maximum par réservation est supérieur au nombre de place maximum de la ressource';
        } else if (!$errors) {

            if ($this->data['formtype'] == 'update') {
                // if ($insecure_values['clementine_reservation_horaire-to_add'] >= 1 && ($ressource_mdl->ressourcehasHoraireInTimeById($insecure_values['clementine_reservation_ressource-libelle'] + 1, $insecure_values['clementine_reservation_horaire-start_date'], $insecure_values['clementine_reservation_horaire-end_date'], $insecure_values['clementine_reservation_horaire-start_hour'], $insecure_values['clementine_reservation_horaire-end_hour'], $insecure_primary_key['clementine_reservation_horaire-id'], $insecure_values['clementine_reservation_horaire-time_creneaux']) || (!($this->in_array_date_deb($insecure_values['clementine_reservation_horaire-start_hour'], $horr_start)) || !($this->in_array_date_deb($insecure_values['clementine_reservation_horaire-start_hour'], $horr_end))))) {
                //     $my_errors['end_date'] = 'Il y a déjà une horaire créé durant ce temps là';
                // }

            } else {
                $horaire_mdl = $this->getModel('horaire');
                $id_horaire = $horaire_mdl->getMaxIdHoraire();
                // if ($insecure_values['clementine_reservation_horaire-to_add'] >= 1 && $ressource_mdl->ressourcehasHoraire($insecure_values['clementine_reservation_ressource-libelle'] + 1) && ($ressource_mdl->ressourcehasHoraireInTimeById($insecure_values['clementine_reservation_ressource-libelle'] + 1, $insecure_values['clementine_reservation_horaire-start_date'], $insecure_values['clementine_reservation_horaire-end_date'], $insecure_values['clementine_reservation_horaire-start_hour'], $insecure_values['clementine_reservation_horaire-end_hour'], $id_horaire + 1, $insecure_values['clementine_reservation_horaire-time_creneaux']) && (!($this->in_array_date_deb($insecure_values['clementine_reservation_horaire-start_hour'], $horr_start)) || !($this->in_array_date_deb($insecure_values['clementine_reservation_horaire-start_hour'], $horr_end))))) {
                //     $my_errors['end_date'] = 'Il y a déjà une horaire créé durant ce temps là';
                // }

            }
            $db = $this->getModel('db');
            $horaire_mdl = $this->getModel('horaire');

            $max_id = $horaire_mdl->getMaxIdHoraire();
            $max_id = $max_id + 1;
            $fullcalendar_mdl = $this->getModel('fullcalendarresa');
            $db->query('START TRANSACTION');
            $horaire_tab_crea = array();
            $fullcalendar_helper = $this->getHelper('fullcalendarresa');
            $val_till = $_POST['till'];
            $continue = false;
            $tab_horaire_actu_deb = array();
            $tab_horaire_actu_fin = array();
            $tab_horaire_crea_deb = array();
            $tab_horaire_crea_fin = array();
            if ($this->data['formtype'] == "update") {
                $max_id = $insecure_primary_key['clementine_reservation_horaire-id'];
                $sql = "DELETE FROM clementine_reservation_horaire_has_option WHERE id_horaire = $max_id";
                $db->query($sql);
            }
            $tab_horaire_crea_deb = array();
            $tab_horaire_crea_fin = array();
            for ($i = 1; $i < 8; ++$i) {
                if (isset($_POST['select_week' . $i])) {
                    $val_post = $_POST['select_week' . $i];
                    $res = array();
                    $res2 = array();
                    $res2['till'] = $val_till;
                    $res2['repeat_all'] = 'semaine';
                    $res2['week'] = $val_post;
                    $res2['repeat'] = $_POST['nb_fois'];
                    $res['start_date'] = $insecure_values['clementine_reservation_horaire-start_date'];
                    $res['start_hour'] = $insecure_values['clementine_reservation_horaire-start_hour'] . ':00';
                    $res['end_date'] = $insecure_values['clementine_reservation_horaire-end_date'];
                    $res['end_hour'] = $insecure_values['clementine_reservation_horaire-end_hour'] . ':00';
                    $res['comment'] = $insecure_values['clementine_reservation_horaire-comment'];
                    $res['to_add'] = $insecure_values['clementine_reservation_horaire-to_add'];
                    $res['id'] = $max_id;
                    $start_date_load = $insecure_values['clementine_reservation_horaire-start_date'];
                    $end_date_load = $val_till;
                    $request = $this->getRequest();
                    if (isset($_GET['clementine_reservation_ressource-id'])) {
                        $id_ressource = $_GET['clementine_reservation_ressource-id'];
                    } else {
                        $id_ressource = $_GET['id_ressource'];
                    }
                    $sql = <<<SQL
                SELECT time_creneaux, maximum_number_place, maximum_number_place_by_reservation
                FROM clementine_reservation_ressource
                WHERE id = $id_ressource
SQL;

                    $stmt = $db->query($sql);
                    $res3 = $db->fetch_assoc($stmt);
                    $times_creneaux = $res3['time_creneaux'];
                    $maximum_number_place = $res3['maximum_number_place'];
                    $maximum_number_place_by_reservation = $res3['maximum_number_place_by_reservation'];
                    $time_creneaux = $fullcalendar_helper->timeToSecond($times_creneaux);
                    if (!empty($insecure_values['clementine_reservation_horaire-time_creneaux']) && $insecure_values['clementine_reservation_horaire-time_creneaux'] != "00:00:00") {
                        $times_creneaux = $insecure_values['clementine_reservation_horaire-time_creneaux'];
                        $time_creneaux = $fullcalendar_helper->timeToSecond($times_creneaux);
                    }
                    if (!empty($insecure_values['clementine_reservation_horaire-maximum_number_place'])) {
                        $maximum_number_place = $insecure_values['clementine_reservation_horaire-maximum_number_place'];
                    }
                    if (!empty($insecure_values['clementine_reservation_horaire-maximum_number_place_by_reservation'])) {
                        $maximum_number_place_by_reservation = $insecure_values['clementine_reservation_horaire-maximum_number_place_by_reservation'];
                    }
                    $sec_start = strtotime($res['start_date']);
                    $sec_end = strtotime($res['end_date']);
                    $diff_datedeb_datefin = $sec_end - $sec_start;

                    $horaire_tab_crea = $fullcalendar_mdl->repeat_all_week($res, $res2, $start_date_load, $end_date_load, $diff_datedeb_datefin, $request, $times_creneaux, $horaire_tab_crea, true, false, $maximum_number_place, $maximum_number_place_by_reservation);
                    foreach ($horaire_tab_crea as $tab_crea) {
                        array_push($tab_horaire_crea_deb, $tab_crea->start);
                        array_push($tab_horaire_crea_fin, $tab_crea->end);
                    }

                    $horraire_tab_actu = $fullcalendar_mdl->getTotalHorraireResa($id_ressource, false, false, $start_date_load, $end_date_load);

                    foreach ($horraire_tab_actu as $tab_actu) {
                        array_push($tab_horaire_actu_deb, $tab_actu->start);
                        array_push($tab_horaire_actu_fin, $tab_actu->end);
                    }
                    $ind = 0;
                    $existe = 0;
                    $val_post = $_POST['select_week' . $i];
                    $repeat = $_POST['nb_fois'];
                    $sql = "INSERT INTO `clementine_reservation_horaire_has_option` (`repeat_all`,`month`,`week`,`till`,`id_horaire`,`repeat`) VALUES ('semaine','null','" . $val_post . "','" . $val_till . "','" . $max_id . "','" . $repeat . "') ";
                    $db->query($sql);
                    $continue = true;
                }
            }
            if (!$continue) {
                for ($i = 0; $i < 6; ++$i) {
                    if (isset($_POST['select_mois' . $i])) {
                        $val_post = $_POST['select_mois' . $i];
                        $res = array();
                        $res2 = array();
                        $res2['till'] = $val_till;
                        $res2['repeat_all'] = 'mois';
                        $res2['month'] = $val_post;
                        $res2['repeat'] = 'null';
                        $res['start_date'] = $insecure_values['clementine_reservation_horaire-start_date'];
                        $res['start_hour'] = $insecure_values['clementine_reservation_horaire-start_hour'] . ':00';
                        $res['end_date'] = $insecure_values['clementine_reservation_horaire-end_date'];
                        $res['end_hour'] = $insecure_values['clementine_reservation_horaire-end_hour'] . ':00';
                        $res['comment'] = $insecure_values['clementine_reservation_horaire-comment'];
                        $res['to_add'] = $insecure_values['clementine_reservation_horaire-to_add'];
                        $res['id'] = $max_id;
                        $start_date_load = $insecure_values['clementine_reservation_horaire-start_date'];
                        $end_date_load = $val_till;
                        $request = $this->getRequest();
                        $id_ressource = $_GET['clementine_reservation_ressource-id'];

                        $sql = <<<SQL
                    SELECT time_creneaux, maximum_number_place, maximum_number_place_by_reservation
                    FROM clementine_reservation_ressource
                    WHERE id = $id_ressource
SQL;
                        $stmt = $db->query($sql);
                        $res3 = $db->fetch_assoc($stmt);
                        $times_creneaux = $res3['time_creneaux'];
                        $maximum_number_place = $res3['maximum_number_place'];
                        $maximum_number_place_by_reservation = $res3['maximum_number_place_by_reservation'];

                        $time_creneaux = $fullcalendar_helper->timeToSecond($times_creneaux);
                        if (!empty($insecure_values['clementine_reservation_horaire-time_creneaux']) && $insecure_values['clementine_reservation_horaire-time_creneaux'] != "00:00:00") {
                            $times_creneaux = $insecure_values['clementine_reservation_horaire-time_creneaux'];
                            $time_creneaux = $fullcalendar_helper->timeToSecond($times_creneaux);
                        }
                        if (!empty($insecure_values['clementine_reservation_horaire-maximum_number_place'])) {
                            $maximum_number_place = $insecure_values['clementine_reservation_horaire-maximum_number_place'];
                        }
                        if (!empty($insecure_values['clementine_reservation_horaire-maximum_number_place_by_reservation'])) {
                            $maximum_number_place_by_reservation = $insecure_values['clementine_reservation_horaire-maximum_number_place_by_reservation'];
                        }
                        $sec_start = strtotime($res['start_date']);
                        $sec_end = strtotime($res['end_date']);
                        $diff_datedeb_datefin = $sec_end - $sec_start;

                        $horaire_tab_crea = $fullcalendar_mdl->repeat_all_month($res, $res2, $start_date_load, $end_date_load, $diff_datedeb_datefin, $request, $times_creneaux, $horaire_tab_crea, true, false, $maximum_number_place, $maximum_number_place_by_reservation);

                        foreach ($horaire_tab_crea as $tab_crea) {
                            array_push($tab_horaire_crea_deb, $tab_crea->start);
                            array_push($tab_horaire_crea_fin, $tab_crea->end);
                        }

                        $horraire_tab_actu = $fullcalendar_mdl->getTotalHorraireResa($id_ressource, false, false, $start_date_load, $end_date_load);

                        foreach ($horraire_tab_actu as $tab_actu) {
                            array_push($tab_horaire_actu_deb, $tab_actu->start);
                            array_push($tab_horaire_actu_fin, $tab_actu->end);
                        }
                        $ind = 0;
                        $existe = 0;

                        $val_mois = $_POST['select_mois' . $i];
                        $sql = "INSERT INTO `clementine_reservation_horaire_has_option` (`repeat_all`,`month`,`week`,`till`,`id_horaire`,`repeat`) VALUES ('mois','" . $val_mois . "','null','" . $val_till . "','" . $max_id . "','null') ";
                        $db->query($sql);
                        $continue = true;
                    }
                }
            }
            if (!$continue) {
                if (isset($_POST['select_value'])) {
                    $val = $_POST['select_value'];
                    if ($_POST['select_value'] != 'mois' && $_POST['select_value'] != 'semaine' && $_POST['select_value'] == 1) {
                        $res = array();
                        $res2 = array();
                        $res2['till'] = $val_till;
                        $res2['repeat_all'] = 'jour';
                        $res2['repeat'] = 'null';
                        $res['start_date'] = $insecure_values['clementine_reservation_horaire-start_date'];
                        $res['start_hour'] = $insecure_values['clementine_reservation_horaire-start_hour'] . ':00';
                        $res['end_date'] = $insecure_values['clementine_reservation_horaire-end_date'];
                        $res['end_hour'] = $insecure_values['clementine_reservation_horaire-end_hour'] . ':00';
                        $res['comment'] = $insecure_values['clementine_reservation_horaire-comment'];
                        $res['to_add'] = $insecure_values['clementine_reservation_horaire-to_add'];
                        $res['id'] = $max_id;
                        $start_date_load = $insecure_values['clementine_reservation_horaire-start_date'];
                        $end_date_load = $val_till;
                        $request = $this->getRequest();
                        $id_ressource = $_GET['clementine_reservation_ressource-id'];

                        $sql = <<<SQL
                    SELECT time_creneaux, maximum_number_place, maximum_number_place_by_reservation
                    FROM clementine_reservation_ressource
                    WHERE id = $id_ressource
SQL;
                        $stmt = $db->query($sql);
                        $res3 = $db->fetch_assoc($stmt);
                        $times_creneaux = $res3['time_creneaux'];
                        $maximum_number_place = $res3['maximum_number_place'];
                        $maximum_number_place_by_reservation = $res3['maximum_number_place_by_reservation'];

                        $time_creneaux = $fullcalendar_helper->timeToSecond($times_creneaux);
                        if (!empty($insecure_values['clementine_reservation_horaire-time_creneaux']) && $insecure_values['clementine_reservation_horaire-time_creneaux'] != "00:00:00") {
                            $times_creneaux = $insecure_values['clementine_reservation_horaire-time_creneaux'];
                            $time_creneaux = $fullcalendar_helper->timeToSecond($times_creneaux);
                        }
                        if (!empty($insecure_values['clementine_reservation_horaire-maximum_number_place'])) {
                            $maximum_number_place = $insecure_values['clementine_reservation_horaire-maximum_number_place'];
                        }
                        if (!empty($insecure_values['clementine_reservation_horaire-maximum_number_place_by_reservation'])) {
                            $maximum_number_place_by_reservation = $insecure_values['clementine_reservation_horaire-maximum_number_place_by_reservation'];
                        }
                        $sec_start = strtotime($res['start_date']);
                        $sec_end = strtotime($res['end_date']);
                        $diff_datedeb_datefin = $sec_end - $sec_start;

                        $horaire_tab_crea = $fullcalendar_mdl->repeat_all_day($start_date_load, $end_date_load, $res2, $request, $times_creneaux, $horaire_tab_crea, true, false, $maximum_number_place, $maximum_number_place_by_reservation, $res, $diff_datedeb_datefin);

                        foreach ($horaire_tab_crea as $tab_crea) {
                            array_push($tab_horaire_crea_deb, $tab_crea->start);
                            array_push($tab_horaire_crea_fin, $tab_crea->end);
                        }

                        $horraire_tab_actu = $fullcalendar_mdl->getTotalHorraireResa($id_ressource, false, false, $start_date_load, $end_date_load);

                        foreach ($horraire_tab_actu as $tab_actu) {
                            array_push($tab_horaire_actu_deb, $tab_actu->start);
                            array_push($tab_horaire_actu_fin, $tab_actu->end);
                        }
                        $ind = 0;
                        $existe = 0;

                        $sql = "INSERT INTO `clementine_reservation_horaire_has_option` (`repeat_all`,`month`,`week`,`till`,`id_horaire`,`repeat`) VALUES ('jour','null','null','" . $val_till . "','" . $max_id . "','null') ";
                        $db->query($sql);
                        $continue = true;
                    } else {
                        $res = array();
                        $res2 = array();
                        $res2['till'] = $val_till;
                        $res2['repeat_all'] = $val;
                        $res['start_date'] = $insecure_values['clementine_reservation_horaire-start_date'];
                        $res['start_hour'] = $insecure_values['clementine_reservation_horaire-start_hour'] . ':00';
                        $res['end_date'] = $insecure_values['clementine_reservation_horaire-end_date'];
                        $res['end_hour'] = $insecure_values['clementine_reservation_horaire-end_hour'] . ':00';
                        $res['comment'] = $insecure_values['clementine_reservation_horaire-comment'];
                        $res['to_add'] = $insecure_values['clementine_reservation_horaire-to_add'];
                        $res['id'] = $max_id;
                        $start_date_load = $insecure_values['clementine_reservation_horaire-start_date'];
                        $end_date_load = $val_till;
                        $request = $this->getRequest();
                        $id_ressource = $_GET['clementine_reservation_ressource-id'];

                        $sql = <<<SQL
                    SELECT time_creneaux, maximum_number_place, maximum_number_place_by_reservation
                    FROM clementine_reservation_ressource
                    WHERE id = $id_ressource
SQL;
                        $stmt = $db->query($sql);
                        $res3 = $db->fetch_assoc($stmt);
                        $times_creneaux = $res3['time_creneaux'];
                        $maximum_number_place = $res3['maximum_number_place'];
                        $maximum_number_place_by_reservation = $res3['maximum_number_place_by_reservation'];

                        $time_creneaux = $fullcalendar_helper->timeToSecond($times_creneaux);
                        if (!empty($insecure_values['clementine_reservation_horaire-time_creneaux']) && $insecure_values['clementine_reservation_horaire-time_creneaux'] != "00:00:00") {
                            $times_creneaux = $insecure_values['clementine_reservation_horaire-time_creneaux'];
                            $time_creneaux = $fullcalendar_helper->timeToSecond($times_creneaux);
                        }
                        if (!empty($insecure_values['clementine_reservation_horaire-maximum_number_place'])) {
                            $maximum_number_place = $insecure_values['clementine_reservation_horaire-maximum_number_place'];
                        }
                        if (!empty($insecure_values['clementine_reservation_horaire-maximum_number_place_by_reservation'])) {
                            $maximum_number_place_by_reservation = $insecure_values['clementine_reservation_horaire-maximum_number_place_by_reservation'];
                        }
                        $sec_start = strtotime($res['start_date']);
                        $sec_end = strtotime($res['end_date']);
                        $diff_datedeb_datefin = $sec_end - $sec_start;
                        $horaire_tab_crea = $fullcalendar_mdl->repeat_all_spec_day($res, $res2, $start_date_load, $end_date_load, $diff_datedeb_datefin, $request, $times_creneaux, $horaire_tab_crea, true, false, $maximum_number_place, $maximum_number_place_by_reservation);

                        foreach ($horaire_tab_crea as $tab_crea) {
                            array_push($tab_horaire_crea_deb, $tab_crea->start);
                            array_push($tab_horaire_crea_fin, $tab_crea->end);
                        }

                        $horraire_tab_actu = $fullcalendar_mdl->getTotalHorraireResa($id_ressource, false, false, $start_date_load, $end_date_load);

                        foreach ($horraire_tab_actu as $tab_actu) {
                            array_push($tab_horaire_actu_deb, $tab_actu->start);
                            array_push($tab_horaire_actu_fin, $tab_actu->end);
                        }
                        $ind = 0;
                        $existe = 0;

                        $sql = "INSERT INTO `clementine_reservation_horaire_has_option` (`repeat_all`,`month`,`week`,`till`,`id_horaire`,`repeat`) VALUES ('" . $val . "','null','null','" . $val_till . "','" . $max_id . "','null') ";
                        $db->query($sql);
                        $continue = true;
                    }
                }
            }
            $existe = 0;
            foreach ($tab_horaire_actu_deb as $tab_deb_actu) {
                $date_deb_actu = $tab_deb_actu;
                $date_fin_actu = $tab_horaire_actu_fin[$ind];
                $ind_crea = 0;

                foreach ($tab_horaire_crea_deb as $tab_deb_crea) {

                    $date_deb_crea = $tab_deb_crea;
                    $date_fin_crea = $tab_horaire_crea_fin[$ind_crea];

                    if ($date_fin_actu >= $date_deb_crea && $date_deb_crea >= $date_deb_actu || $date_fin_actu >= $date_fin_crea && $date_fin_crea >= $date_deb_actu) {
                        $existe = 1;
                    }
                    ++$ind_crea;

                }
                ++$ind;

            }
            if ($existe == 1) {
                $my_errors['option'] = 'Il y a déjà un horaire crée sur cette durée.';
                $db->query('ROLLBACK');
            } else {
                $db->query('COMMIT');
            }
        }
        return $my_errors;
    }
    /**
     *  getTotalHoraireExcep : Renvoie les horaires exceptions qui sont stocké dans tab_horaire qui contient toute les horaires
     *
     *  @access public
     *  @return void
     *
     */
    public function getTotalHoraireExcep($tab_horaire)
    {
        $tab_horaire_except = array();
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');
        foreach ($tab_horaire as $elem) {
            if (isset($elem->color)) {
                if ($elem->color == "red") {
                    array_push($tab_horaire_except, $elem);
                }
            }
        }
        return $tab_horaire_except;
    }
    /**
     *  in_array_date_deb : Equivaut à un in_array mais uniquement pour les horaires
     *
     *  @access public
     *  @return void
     *
     */
    public function in_array_date_deb($valeur, $tab)
    {
        $valeur.= ':00';
        foreach ($tab as $key => $value) {
            list($horaire_start, $horaire_end) = explode("-", $value);
            if ($valeur == trim($horaire_start)) {
                return true;
            }
        }
        return false;
    }

    public function override_url($request, $params = null)
    {
        $this->overrideUrlButton('create', __WWW__ . '/horaire/create?clementine_reservation_ressource-id=' . $request->get('int', 'clementine_reservation_ressource-id'));
        $this->overrideUrlButton('back', __WWW__ . '/horaire?clementine_reservation_ressource-id=' . $request->get('int', 'clementine_reservation_ressource-id'));
    }
}
