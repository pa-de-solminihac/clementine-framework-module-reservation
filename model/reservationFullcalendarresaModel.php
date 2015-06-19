<?php
class reservationFullcalendarresaModel extends reservationFullcalendarresaModel_Parent
{
    /**  
     *   getTotalHorraireResa : La fonction getTotalHorraireResa() créer un tableau contenant tout les
     *                          horraires et toutes les résérvation sous forme d'objet fullCalendar.
     *                          Ce tableau correspond au valeur spécifique d'une ressources ( préciser par sont id_ressource )
     *
     *   @access public
     *   @return horraire_tab tableau de tous les horaires
     */
    public function getTotalHorraireResa($id_ressource, $resa = true, $horaire = false, $start_date_load, $end_date_load)
    {
        $request = $this->getRequest();
        $db = $this->getModel('db');

        $sql = <<<SQL
    SELECT time_creneaux, maximum_number_place, maximum_number_place_by_reservation
    FROM clementine_reservation_ressource
    WHERE id = $id_ressource
SQL;
        $stmt = $db->query($sql);
        $res = $db->fetch_assoc($stmt);
        $times_creneaux = $res['time_creneaux'];
        $maximum_number_place = $res['maximum_number_place'];
        $maximum_number_place_by_reservation = $res['maximum_number_place_by_reservation'];
        
        list($an_date_laod_deb, $mois_date_load_deb, $jour_date_load_deb) = explode('-', $start_date_load);
        list($an_date_laod_end, $mois_date_load_end, $jour_date_load_end) = explode('-', $end_date_load);

        if ($an_date_laod_deb != $an_date_laod_end) {
            $mois_date_load_end = 13;
        }
        if ($mois_date_load_deb == 12 && $mois_date_load_deb != $mois_date_load_end) {
            $mois_date_load_deb = 0;
            $an_date_laod_deb = $an_date_laod_deb + 1;
        }
        if ($mois_date_load_end - $mois_date_load_deb > 1) {
            $start_date_load = date("Y-m-d", mktime(0, 0, 0, $mois_date_load_deb + 1, 1, $an_date_laod_deb));
            list($useless1, $useless2, $max_day) = explode('-', date("Y-m-t", strtotime($start_date_load)));
            $end_date_load = date("Y-m-d", mktime(0, 0, 0, $mois_date_load_deb + 1, $max_day + 1, $an_date_laod_deb));
            list($useless1, $useless2, $max_day) = explode('-', date("Y-m-t", strtotime($end_date_load)));
            $end_date_load = date("Y-m-d", mktime(0, 0, 0, $mois_date_load_deb + 1, $max_day + 1, $an_date_laod_deb)); 
        } else if ($mois_date_load_end - $mois_date_load_deb == 1) {
            if ($jour_date_load_deb == "01") {
                list($useless1, $useless2, $max_day) = explode('-', date("Y-m-t", strtotime($start_date_load)));
                $end_date_load = date("Y-m-d", mktime(0, 0, 0, $mois_date_load_deb + 1, $max_day + 1, $an_date_laod_deb));
                list($useless1, $useless2, $max_day) = explode('-', date("Y-m-t", strtotime($end_date_load)));
                $end_date_load = date("Y-m-d", mktime(0, 0, 0, $mois_date_load_deb + 1, $max_day + 1, $an_date_laod_deb));
            }
        }
        
        /* Mise en place des horraires */
        /* Recupère les horraire pour la ressource séléctionné */
        $sql = <<<SQL
    SELECT clementine_reservation_horaire.id, start_date, start_hour, end_hour, end_date, to_add, comment, clementine_reservation_horaire.option , time_creneaux, maximum_number_place, maximum_number_place_by_reservation
    FROM clementine_reservation_horaire, clementine_reservation_ressource_has_horaire
    WHERE clementine_reservation_horaire.id = clementine_reservation_ressource_has_horaire.horaire_id
      AND clementine_reservation_ressource_has_horaire.ressource_id = $id_ressource

SQL;
        $stmt = $db->query($sql);
        $horraire_tab = array();
        $fullcalendar_helper = $this->getHelper('fullcalendarresa');
        $user = $this->getModel('users');
        $privileges = array(
            'clementine_reservation_gerer_reservation' => true
        );
        $admin = $user->hasPrivilege($privileges);
        while ($res = $db->fetch_assoc($stmt)) {

            $time_creneaux = $fullcalendar_helper->timeToSecond($times_creneaux);
            if (!empty($res["time_creneaux"]) && $res["time_creneaux"] != "00:00:00") {
                $times_creneaux = $res["time_creneaux"];
                $time_creneaux = $fullcalendar_helper->timeToSecond($times_creneaux);
            }
            if (!empty($res['maximum_number_place'])) {
                $maximum_number_place = $res['maximum_number_place'];
            }
            if (!empty($res['maximum_number_place_by_reservation'])) {
                $maximum_number_place_by_reservation = $res['maximum_number_place_by_reservation'];
            }
            $id_horaire_actu = $res['id'];
            list($ans_deb_res, $mois_deb, $jour_deb) = explode('-', $res['start_date']);
            $sec_start = strtotime($res['start_date']);
            $sec_end = strtotime($res['end_date']);
            $diff_datedeb_datefin = $sec_end - $sec_start;

           
            if ($res['option'] != 1) {

                $horraire_tab = $this->createHoraire($res, $start_date_load, $end_date_load, null, null, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
            } else {
                $sql2 = <<<SQL
    SELECT *
    FROM clementine_reservation_horaire_has_option
    WHERE clementine_reservation_horaire_has_option.id_horaire = $id_horaire_actu
SQL;
                $stmt2 = $db->query($sql2);
                while ($res2 = $db->fetch_assoc($stmt2)) {
                    if ($res2['repeat_all'] != 'NULL') {
                        if ($res2['repeat_all'] == 'jour') {
                            $horraire_tab = $this->repeat_all_day($start_date_load, $end_date_load, $res2, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation, $res, $diff_datedeb_datefin);
                        } else if ($res2['repeat_all'] == 'mois') {
                            $horraire_tab = $this->repeat_all_month($res, $res2, $start_date_load, $end_date_load, $diff_datedeb_datefin, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        } else if ($res2['repeat_all'] == 'semaine') {
                            $horraire_tab = $this->repeat_all_week($res, $res2, $start_date_load, $end_date_load, $diff_datedeb_datefin, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        } else {
                            $horraire_tab = $this->repeat_all_spec_day($res, $res2, $start_date_load, $end_date_load, $diff_datedeb_datefin, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        }

                    }
                }
            }
        }
        return $horraire_tab;
    }
    /**
     *  getListCreneauxPossible : Donne la totalité des créneaux possible entre deux date donnée pour une ressource
     *
     *  @access public
     *  @return void
     */
    public function getListCreneauxPossible($id_ressource, $start_date_load, $end_date_load, $tab_horaire_resa = null)
    {
        list($an_date_laod_deb, $mois_date_load_deb, $jour_date_load_deb) = explode('-', $start_date_load);
        list($an_date_laod_end, $mois_date_load_end, $jour_date_load_end) = explode('-', $end_date_load);

        if ($mois_date_load_end - $mois_date_load_deb == 2) {
            $start_date_load = date("Y-m-d", mktime(0, 0, 0, $mois_date_load_deb + 1, 1, $an_date_laod_deb));
            list($useless1, $useless2, $max_day) = explode('-', date("Y-m-t", strtotime($start_date_load)));
            $end_date_load = date("Y-m-d", mktime(0, 0, 0, $mois_date_load_deb + 1, $max_day + 1, $an_date_laod_deb));
        } else if ($mois_date_load_end - $mois_date_load_deb == 1) {
            if ($jour_date_load_deb == "01") {
                list($useless1, $useless2, $max_day) = explode('-', date("Y-m-t", strtotime($start_date_load)));
                $end_date_load = date("Y-m-d", mktime(0, 0, 0, $mois_date_load_deb + 1, $max_day + 1, $an_date_laod_deb));
            }
        }
        if (!(isset($tab_horaire_resa))) {
            $tab_horaire_resa = $this->getTotalHorraireResa($id_ressource, false, false, $start_date_load, $end_date_load);
        }
        $tab_horaire_except = array();
        foreach ($tab_horaire_resa as $horaire_creneaux) {
            $color = 'green';
            if (isset($horaire_creneaux->color)) {
                $color = $horaire_creneaux->color;
            }
            if ($color == 'red') {
                $tab_creneaux_except = array();
                $actually_day = $horaire_creneaux->start;
                list($actually_day, $start_hour) = explode('T', $actually_day);
                $actually_day_end = $horaire_creneaux->end;
                list($actually_day_end, $end_hour) = explode('T', $actually_day_end);
                array_push($tab_creneaux_except, $actually_day, $start_hour, $end_hour);
                list($annee, $mois, $jour) = explode('-', $actually_day);
                array_push($tab_horaire_except, $tab_creneaux_except);
            }
        }

        $size_except = count($tab_horaire_except);
        $tab_horraire = array();
        foreach ($tab_horaire_resa as $horaire_creneaux) {
            $color = 'green';
            if (isset($horaire_creneaux->color)) {
                $color = $horaire_creneaux->color;
            }
            if ($color != 'red') {
                $times_creneaux = $horaire_creneaux->time_creneaux;
                $fullcalendar_helper = $this->getHelper('fullcalendarresa');
                $time_creneaux = $fullcalendar_helper->timeToSecond($times_creneaux);

                $actually = $horaire_creneaux->start;
                list($actually_day, $actually_hour) = explode('T', $actually);

                $actually_end = $horaire_creneaux->end;
                list($actually_day_end, $actually_hour_end) = explode('T', $actually_end);

                $tab_creneaux_deb = array();
                $tab_creneaux_fin = array();

                $start_second = $fullcalendar_helper->timeToSecond($actually_hour);
                $end_second = $fullcalendar_helper->timeToSecond($actually_hour_end);

                while ($start_second <= $end_second) {
                    $start_hour = gmdate("H:i:s", $start_second);
                    $end_hour_cren = $start_second + $time_creneaux;
                    $end_time_cren = gmdate("H:i:s", $end_hour_cren);
                    array_push($tab_creneaux_deb, $start_hour);
                    array_push($tab_creneaux_fin, $end_time_cren);
                    $start_second = $end_hour_cren;
                }
                $taille_creneaux_tab = count($tab_creneaux_deb);
                list($annee, $mois, $jour) = explode('-', $actually_day);
                $tab_creneaux = array();
                $start_second = $fullcalendar_helper->timeToSecond($actually_hour);
                $end_second = $fullcalendar_helper->timeToSecond($actually_hour_end);
                $incr = 0;
                for ($j = 200; $j; --$j) {
                    $end_hour_creneaux = $start_second + $time_creneaux;
                    if ($end_hour_creneaux > $end_second) {
                        break;
                    }
                    $start_hour = $tab_creneaux_deb[$incr];
                    $end_time_creneaux = $tab_creneaux_fin[$incr];
                    $start_second = $end_hour_creneaux;
                    for ($k = 0; $k < $size_except; ++$k) {
                        if ($tab_horaire_except[$k][0] == $actually_day) {
                            if (($start_hour >= $tab_horaire_except[$k][1]) && ($end_time_creneaux <= $tab_horaire_except[$k][2])) {
                                $start_timestamp = $fullcalendar_helper->timeToSecond($tab_horaire_except[$k][1]);
                                $end_timestamp = $fullcalendar_helper->timeToSecond($tab_horaire_except[$k][2]);
                                $except_duration = $end_timestamp - $start_timestamp;
                                $number_exception = ceil($except_duration / $time_creneaux);
                                for ($l = 0; $l < $number_exception; ++$l) {
                                    $end_hour_creneaux = $start_second + $time_creneaux;
                                    if ($end_hour_creneaux > $end_second) {
                                        break;
                                    }
                                    ++$incr;
                                    $start_hour = $tab_creneaux_deb[$incr];
                                    $end_time_creneaux = $tab_creneaux_fin[$incr];
                                    $start_second = $end_hour_creneaux;

                                }
                            }
                        }
                    }
                    if ($end_hour_creneaux > $end_second) {
                        break;
                    }
                    $var_creneaux = " " . $start_hour . "-" . $end_time_creneaux;
                    array_push($tab_creneaux, $var_creneaux);
                    if ($incr < $taille_creneaux_tab) {
                        ++$incr;
                    }

                }
                array_push($tab_creneaux, $horaire_creneaux->time_creneaux);
                array_push($tab_horraire, $actually_day, $tab_creneaux);
            }
        }
        return $tab_horraire;
    }
    /**
     *  getListCreneauxSansResa : Donne la totalité des créneaux possible entre deux date donnée pour une ressource
     *
     *  @access public
     *  @return void
     */
    public function getListCreneauxSansResa($id_ressource, $plage_horraire, $start_date_load, $end_date_load)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT time_creneaux, maximum_number_place
    FROM clementine_reservation_ressource
    WHERE id = $id_ressource
SQL;
        $stmt = $db->query($sql);
        $res = $db->fetch_assoc($stmt);
        $horaire_mdl = $this->getModel('horaire');
        $time_creneaux = $res['time_creneaux'];
        $maximum_number_place = $res['maximum_number_place'];
        $number_people = 0;
        $tab_already_done = array();
        $start_date_load = $start_date_load . ' ' . '00:00:00';
        $end_date_load = $end_date_load . ' ' . '00:00:00';
        $sql = <<<SQL
    SELECT clementine_reservation.start_date, clementine_reservation.end_date, clementine_reservation.number_people
    FROM clementine_reservation_ressource_has_reservation, clementine_reservation
    WHERE clementine_reservation.id = clementine_reservation_ressource_has_reservation.reservation_id
      AND clementine_reservation_ressource_has_reservation.ressource_id = $id_ressource
      AND clementine_reservation.cancel = 0
      AND clementine_reservation.start_date >= "$start_date_load"
      AND clementine_reservation.end_date <= "$end_date_load"
SQL;
        $stmt = $db->query($sql);
        $horaire_max = $horaire_mdl->getAllNumberPlaceHoraireBetweenDate($start_date_load, $end_date_load, $id_ressource);

        $fullcalendar_helper = $this->getHelper('fullcalendarresa');
        $reservation_mdl = $this->getModel('reservation');
        while ($res = $db->fetch_assoc($stmt)) {
            $number_people = $res['number_people'];
            $start_d = date_parse_from_format('Y-m-d h:i:s', $res['start_date']);
            $start_timestamp = mktime($start_d['hour'], $start_d['minute'], $start_d['second'], $start_d['month'], $start_d['day'], $start_d['year']);
            $end_d = date_parse_from_format('Y-m-d H:i:s', $res['end_date']);
            $end_timestamp = mktime($end_d['hour'], $end_d['minute'], $end_d['second'], $end_d['month'], $end_d['day'], $end_d['year']);
            $reservation_duration = $end_timestamp - $start_timestamp;
            $time_creneaux = $fullcalendar_helper->timeToSecond($time_creneaux);
            $number_reservation = ceil($reservation_duration / $time_creneaux);
            list($date, $start_hour) = explode(' ', $res['start_date']);
            $start_hour_time = strtotime($start_hour);
            list($end_date, $end_hour) = explode(' ', $res['end_date']);
            $taille_plage_horaire = count($plage_horraire);
            $number_place_max_tmp = $maximum_number_place;
            if (!empty($horaire_max)) {
                foreach ($horaire_max as $key => $value) {
                    if (($value["start_date"] < $date && $date < $value["end_date"]) || ($value["start_date"] == $date && $value["start_hour"] < $start_hour) || ($value["end_date"] == $end_date && $value["end_hour"] > $end_hour)) {
                        if (!empty($value["maximum_number_place"]) && $value["maximum_number_place"] != $maximum_number_place) {
                            $maximum_number_place = $value["maximum_number_place"];
                        }
                    }
                }
            }
            for ($i = 0; $i < $taille_plage_horaire; $i = $i + 2) {
                if ($plage_horraire[$i] == $date) {
                    $taille_plage_horaire_i = count($plage_horraire[$i + 1]);
                    for ($j = 0; $j < $taille_plage_horaire_i - 1; ++$j) {
                        $number_reservation = ceil($reservation_duration / strtotime($plage_horraire[$i + 1][$taille_plage_horaire_i - 1]));
                        list($hd, $hf) = explode('-', $plage_horraire[$i + 1][$j]);
                        $elem_list_tab = strtotime($hd);
                        if ($elem_list_tab == $start_hour_time) {
                            array_push($tab_already_done, $res['start_date']);
                            $number_place_already_took = $reservation_mdl->getNbPlacePrise($res['start_date'], $res['end_date'], $id_ressource);
                            if ($number_place_already_took == $maximum_number_place) {
                                for ($k = 0; $k < $number_reservation; ++$k) {
                                    unset($plage_horraire[$i + 1][$j + $k]);
                                }
                                $plage_horraire[$i + 1] = array_values($plage_horraire[$i + 1]);
                                break;
                            }
                        } else if ($elem_list_tab > $start_hour_time) {
                            $number_place_already_took = $reservation_mdl->getNbPlacePrise($res['start_date'], $res['end_date'], $id_ressource); // Prend beaucoup de place pour des cas exceptionnels.
                            if (!(in_array($res['start_date'], $tab_already_done))) {
                                array_push($tab_already_done, $res['start_date']);
                                if ($number_place_already_took == $maximum_number_place) {
                                    if ($end_hour > $hf) {
                                        ++$number_reservation;
                                    }
                                    for ($k = 0; $k < $number_reservation; ++$k) {
                                        if ($j > 0) {
                                            unset($plage_horraire[$i + 1][$j + $k - 1]);
                                        }
                                    }
                                    $plage_horraire[$i + 1] = array_values($plage_horraire[$i + 1]);
                                    break;
                                }
                            }
                        }
                    }
                }
            }
            $maximum_number_place = $number_place_max_tmp;
        }
        return $plage_horraire;
    }
    /**
     * getTabId : renvoie tous les id à une date donnée et une ressource donnée, est utilisé pour drag and drop une liste
     *
     * @access public
     * @return void
     */
    public function getTabId($date, $id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT id
    FROM clementine_reservation, clementine_reservation_ressource_has_reservation
    WHERE start_date = "$date"
      AND clementine_reservation.id = clementine_reservation_ressource_has_reservation.reservation_id
      AND ressource_id = $id_ressource
SQL;
        $stmt = $db->query($sql);
        return $db->fetch_all($stmt, MYSQL_ASSOC);
    }
    /**
     *  createHoraire : Créer les horaires avec un format d'événements de type Fullcalendar.
     *
     *  @access public
     *  @return horraire_tab
     */
    public function createHoraire($res, $start_date_load, $end_date_load, $date_semaine_actu = null, $date_fin_actu = null, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation)
    {
        if ($res['to_add'] == - 1) {
            $color = 'red';
        } else {
            $color = 'grey';
        }
        $actual_day = $res['start_date'];
        $end_date = $res['end_date'];
        if ($actual_day < $start_date_load) {
            $actual_day = $start_date_load;
        }
        if (isset($date_semaine_actu) && $date_semaine_actu != null) {
            $actual_day = $date_semaine_actu;
            $end_date = $date_fin_actu;
        }
        if (!($request->AJAX)) {
            while ($actual_day <= $end_date && $actual_day < $end_date_load) {
                list($annee, $mois, $jour) = explode('-', $actual_day);
                $full_week_horaire = (object)array(
                    'start' => $actual_day . 'T' . $res['start_hour'],
                    'end' => $actual_day . 'T' . $res['end_hour'],
                    'rendering' => 'background',
                    'color' => $color,
                    'title' => $res['comment'],
                    'time_creneaux' => $times_creneaux,
                    'maximum_number_place' => $maximum_number_place,
                    'maximum_number_place_by_reservation' => $maximum_number_place_by_reservation
                );
                if ($admin && $horaire == "true") {
                    $affichage = (object)array(
                        'start' => $actual_day . 'T' . $res['start_hour'],
                        'end' => $actual_day . 'T' . $res['end_hour'],
                        'backgroundColor' => 'transparent',
                        'title' => $res['comment'],
                        'url' => __WWW__ . '/horaire/update?clementine_reservation_horaire-id=' . $res['id'],
                        'time_creneaux' => $times_creneaux,
                        'maximum_number_place' => $maximum_number_place,
                        'maximum_number_place_by_reservation' => $maximum_number_place_by_reservation
                    );
                    array_push($horraire_tab, $affichage);
                } else {
                    if (!empty($res['comment']) || $res['to_add'] == - 1) {
                        if ($admin) {
                            $affichage = (object)array(
                                'start' => $actual_day . 'T' . $res['start_hour'],
                                'end' => $actual_day . 'T' . $res['end_hour'],
                                'backgroundColor' => 'transparent',
                                'title' => $res['comment'],
                                'url' => __WWW__ . '/horaire/update?clementine_reservation_horaire-id=' . $res['id'],
                                'time_creneaux' => $times_creneaux,
                                'maximum_number_place' => $maximum_number_place,
                                'maximum_number_place_by_reservation' => $maximum_number_place_by_reservation
                            );
                        } else {
                            $affichage = (object)array(
                                'start' => $actual_day . 'T' . $res['start_hour'],
                                'end' => $actual_day . 'T' . $res['end_hour'],
                                'backgroundColor' => 'transparent',
                                'title' => $res['comment'],
                                'time_creneaux' => $times_creneaux,
                                'maximum_number_place' => $maximum_number_place,
                                'maximum_number_place_by_reservation' => $maximum_number_place_by_reservation
                            );
                        }
                        array_push($horraire_tab, $affichage);
                    }
                }
                array_push($horraire_tab, $full_week_horaire);
                $actual_day = date("Y-m-d", mktime(0, 0, 0, $mois, $jour + 1, $annee));
            }
        } else {
            while ($actual_day < $start_date_load) {
                list($annee, $mois, $jour) = explode('-', $actual_day);
                $actual_day = date("Y-m-d", mktime(0, 0, 0, $mois, $jour + 1, $annee));
            }

            while ($actual_day >= $start_date_load && $actual_day <= $end_date && $actual_day < $end_date_load) {
                list($annee, $mois, $jour) = explode('-', $actual_day);
                $full_week_horaire = (object)array(
                    'start' => $actual_day . 'T' . $res['start_hour'],
                    'end' => $actual_day . 'T' . $res['end_hour'],
                    'rendering' => 'background',
                    'color' => $color,
                    'title' => $res['comment'],
                    'time_creneaux' => $times_creneaux,
                    'maximum_number_place' => $maximum_number_place,
                    'maximum_number_place_by_reservation' => $maximum_number_place_by_reservation
                );
                if ($admin && $horaire == "true") {
                    $affichage = (object)array(
                        'start' => $actual_day . 'T' . $res['start_hour'],
                        'end' => $actual_day . 'T' . $res['end_hour'],
                        'backgroundColor' => 'transparent',
                        'title' => $res['comment'],
                        'url' => __WWW__ . '/horaire/update?clementine_reservation_horaire-id=' . $res['id'],
                        'time_creneaux' => $times_creneaux,
                        'maximum_number_place' => $maximum_number_place,
                        'maximum_number_place_by_reservation' => $maximum_number_place_by_reservation
                    );
                    array_push($horraire_tab, $affichage);
                } else {
                    if (!empty($res['comment']) || $res['to_add'] == - 1) {
                        if ($admin) {
                            $affichage = (object)array(
                                'start' => $actual_day . 'T' . $res['start_hour'],
                                'end' => $actual_day . 'T' . $res['end_hour'],
                                'backgroundColor' => 'transparent',
                                'title' => $res['comment'],
                                'url' => __WWW__ . '/horaire/update?clementine_reservation_horaire-id=' . $res['id'],
                                'time_creneaux' => $times_creneaux,
                                'maximum_number_place' => $maximum_number_place,
                                'maximum_number_place_by_reservation' => $maximum_number_place_by_reservation
                            );
                        } else {
                            $affichage = (object)array(
                                'start' => $actual_day . 'T' . $res['start_hour'],
                                'end' => $actual_day . 'T' . $res['end_hour'],
                                'backgroundColor' => 'transparent',
                                'title' => $res['comment'],
                                'time_creneaux' => $times_creneaux,
                                'maximum_number_place' => $maximum_number_place,
                                'maximum_number_place_by_reservation' => $maximum_number_place_by_reservation
                            );
                        }
                        array_push($horraire_tab, $affichage);
                    }
                }
                array_push($horraire_tab, $full_week_horaire);
                $actual_day = date("Y-m-d", mktime(0, 0, 0, $mois, $jour + 1, $annee));
            }
        }
        return $horraire_tab;

    }
    /**
     *  repeat_all_day : Créer en boucle les horaires répétés pour des horaires répété tous les jours.
     *
     *  @access public
     *  @return horraire_tab
     */
    public function repeat_all_day($start_date_load, $end_date_load, $res2, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation, $res, $diff_datedeb_datefin)
    {
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');
        $lastday = date('t', strtotime($start_date_load));
        $start_date_load_s = strtotime($start_date_load);
        $date_time_deb = new DateTime();
        $date_time_deb->setTimestamp($start_date_load_s);
        $date_time_deb->format('Y-m-d');
        $test_date = new DateTime();
        $test_date = $date_time_deb;
        date_sub($test_date, date_interval_create_from_date_string('1 days'));
        for ($k = 0; $k < $lastday; ++$k) {
            $test_date->modify('+1 day');
            $test_dates = date('Y-m-d', date_timestamp_get($test_date));
            list($y, $m, $d) = explode('-', $test_dates);
            if ($test_dates <= $res2['till'] && $test_dates >= $res['start_date']) {
                $date_semaine_actu = $test_dates;
                list($ans_actu, $mois_actu, $j_actu) = explode('-', $date_semaine_actu);
                $sec_actu = mktime(0, 0, 0, $mois_actu, $j_actu, $ans_actu);
                $date_fin_actu = $sec_actu + $diff_datedeb_datefin;
                $date_fin_actu = date('Y-m-d', $date_fin_actu);
                $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, $date_semaine_actu, $date_fin_actu, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
            }
        }
        return $horraire_tab;
    }
    /**
     *  repeat_all_month : Créer en boucle les horaires répétés pour des horaires répété tous les mois.
     *
     *  @access public
     *  @return horraire_tab
     */
    public function repeat_all_month($res, $res2, $start_date_load, $end_date_load, $diff_datedeb_datefin, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation)
    {
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');
        if ($res2['month'] == '0') {
            $lastday = date('t', strtotime($start_date_load));
            $deb_last_week = $lastday - 6;
            $date_to_repeat = $res['start_date'];
            list($ans, $mois, $j) = explode('-', $start_date_load);
            $day = date('l', strtotime($date_to_repeat));
            $debut_de_mois = date('Y-m-d', mktime(0, 0, 0, $mois, 1, $ans));
            $start_date_load_s = strtotime($debut_de_mois);

            $date_time_deb = new DateTime();
            $date_time_deb->setTimestamp($start_date_load_s);
            $date_time_deb->format('Y-m-d');
            $test_date = new DateTime();
            $test_date = $date_time_deb;

            for ($ij = 1; $ij < $deb_last_week - 1; ++$ij) {
                $test_date->modify('+1 day');
            }

            for ($k = $deb_last_week - 1; $k <= $lastday - 1; ++$k) {
                $test_date->modify('+1 day');
                $test_date_day = date('l', date_timestamp_get($test_date));
                $test_dates = date('Y-m-d', date_timestamp_get($test_date));
                list($y, $m, $d) = explode('-', $test_dates);
                if ($d >= $deb_last_week && $d <= $lastday) {
                    if ($day == $test_date_day && $test_dates < $res2['till'] && $test_dates >= $res['start_date']) {
                        $date_semaine_actu = $test_dates;
                        list($ans_actu, $mois_actu, $j_actu) = explode('-', $date_semaine_actu);
                        $sec_actu = mktime(0, 0, 0, $mois_actu, $j_actu, $ans_actu);
                        $date_fin_actu = $sec_actu + $diff_datedeb_datefin;
                        $date_fin_actu = date('Y-m-d', $date_fin_actu);
                        if (isset($date_semaine_actu)) {
                            $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, $date_semaine_actu, $date_fin_actu, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        } else {
                            $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, null, null, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        }
                    }
                }
            }

        } else if ($res2['month'] == 1) {
            $date_to_repeat = $res['start_date'];
            list($ans, $mois, $j) = explode('-', $start_date_load);
            $day = date('l', strtotime($date_to_repeat));
            $debut_de_mois = date('Y-m-d', mktime(0, 0, 0, $mois, 1, $ans));
            $start_date_load_s = strtotime($start_date_load);
            $debut_de_mois_s = strtotime($debut_de_mois);
            if ($start_date_load_s < $debut_de_mois_s) {
                $debut_de_mois = date('Y-m-d', mktime(0, 0, 0, $mois + 1, 1, $ans));
                $debut_de_mois_s = strtotime($debut_de_mois);
                $start_date_load_s = $debut_de_mois_s;
            }

            $date_time_deb = new DateTime();
            $date_time_deb->setTimestamp($start_date_load_s);
            $date_time_deb->format('Y-m-d');
            $test_date = new DateTime();
            $test_date = $date_time_deb;
            date_sub($test_date, date_interval_create_from_date_string('1 days'));
            for ($k = 0; $k < 7; ++$k) {
                $test_date->modify('+1 day');
                $test_date_day = date('l', date_timestamp_get($test_date));
                $test_dates = date('Y-m-d', date_timestamp_get($test_date));
                list($y, $m, $d) = explode('-', $test_dates);
                if ($d < 8) {
                    if ($day == $test_date_day && $test_dates < $res2['till'] && $test_dates >= $res['start_date']) {

                        $date_semaine_actu = $test_dates;
                        list($ans_actu, $mois_actu, $j_actu) = explode('-', $date_semaine_actu);
                        $sec_actu = mktime(0, 0, 0, $mois_actu, $j_actu, $ans_actu);
                        $date_fin_actu = $sec_actu + $diff_datedeb_datefin;
                        $date_fin_actu = date('Y-m-d', $date_fin_actu);
                        if (isset($date_semaine_actu)) {
                            $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, $date_semaine_actu, $date_fin_actu, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        } else {
                            $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, null, null, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        }
                    }
                }
            }

        } else if ($res2['month'] == 2) {
            $date_to_repeat = $res['start_date'];
            list($ans, $mois, $j) = explode('-', $start_date_load);
            $day = date('l', strtotime($date_to_repeat));
            $debut_de_mois = date('Y-m-d', mktime(0, 0, 0, $mois, 1, $ans));
            $start_date_load_s = strtotime($start_date_load);
            $debut_de_mois_s = strtotime($debut_de_mois);
            if ($start_date_load_s > $debut_de_mois_s) {
                $start_date_load_s = $debut_de_mois_s;
            }
            $date_time_deb = new DateTime();
            $date_time_deb->setTimestamp($start_date_load_s);
            $date_time_deb->format('Y-m-d');
            $test_date = new DateTime();
            $test_date = $date_time_deb;
            $test_date->modify('+6 day');
            for ($k = 7; $k < 14; ++$k) {
                $test_date->modify('+1 day');
                $test_date_day = date('l', date_timestamp_get($test_date));
                $test_dates = date('Y-m-d', date_timestamp_get($test_date));
                list($y, $m, $d) = explode('-', $test_dates);
                if ($d >= 8 && $d < 15) {
                    if ($day == $test_date_day && $test_dates < $res2['till'] && $test_dates >= $res['start_date']) {
                        $date_semaine_actu = $test_dates;
                        list($ans_actu, $mois_actu, $j_actu) = explode('-', $date_semaine_actu);
                        $sec_actu = mktime(0, 0, 0, $mois_actu, $j_actu, $ans_actu);
                        $date_fin_actu = $sec_actu + $diff_datedeb_datefin;
                        $date_fin_actu = date('Y-m-d', $date_fin_actu);
                        if (isset($date_semaine_actu)) {
                            $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, $date_semaine_actu, $date_fin_actu, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        } else {
                            $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, null, null, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        }
                    }
                }
            }
        } else if ($res2['month'] == 3) {
            $date_to_repeat = $res['start_date'];
            list($ans, $mois, $j) = explode('-', $start_date_load);
            $day = date('l', strtotime($date_to_repeat));
            $debut_de_mois = date('Y-m-d', mktime(0, 0, 0, $mois, 1, $ans));
            $start_date_load_s = strtotime($start_date_load);
            $debut_de_mois_s = strtotime($debut_de_mois);
            if ($start_date_load_s > $debut_de_mois_s) {
                $start_date_load_s = $debut_de_mois_s;
            }
            $date_time_deb = new DateTime();
            $date_time_deb->setTimestamp($start_date_load_s);
            $date_time_deb->format('Y-m-d');
            $test_date = new DateTime();
            $test_date = $date_time_deb;
            $test_date->modify('+13 day');
            for ($k = 14; $k < 21; ++$k) {
                $test_date->modify('+1 day');
                $test_date_day = date('l', date_timestamp_get($test_date));
                $test_dates = date('Y-m-d', date_timestamp_get($test_date));
                list($y, $m, $d) = explode('-', $test_dates);
                if ($d >= 15 && $d < 22) {
                    if ($day == $test_date_day && $test_dates < $res2['till'] && $test_dates >= $res['start_date']) {
                        $date_semaine_actu = $test_dates;
                        list($ans_actu, $mois_actu, $j_actu) = explode('-', $date_semaine_actu);
                        $sec_actu = mktime(0, 0, 0, $mois_actu, $j_actu, $ans_actu);
                        $date_fin_actu = $sec_actu + $diff_datedeb_datefin;
                        $date_fin_actu = date('Y-m-d', $date_fin_actu);
                        if (isset($date_semaine_actu)) {
                            $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, $date_semaine_actu, $date_fin_actu, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        } else {
                            $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, null, null, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        }
                    }
                }
            }

        } else if ($res2['month'] == 4) {
            $date_to_repeat = $res['start_date'];
            list($ans, $mois, $j) = explode('-', $start_date_load);
            $day = date('l', strtotime($date_to_repeat));
            $debut_de_mois = date('Y-m-d', mktime(0, 0, 0, $mois, 1, $ans));
            $start_date_load_s = strtotime($start_date_load);
            $debut_de_mois_s = strtotime($debut_de_mois);
            if ($start_date_load_s > $debut_de_mois_s) {
                $start_date_load_s = $debut_de_mois_s;
            }
            $date_time_deb = new DateTime();
            $date_time_deb->setTimestamp($start_date_load_s);
            $date_time_deb->format('Y-m-d');
            $test_date = new DateTime();
            $test_date = $date_time_deb;
            $test_date->modify('+20 day');
            for ($k = 21; $k < 28; ++$k) {
                $test_date->modify('+1 day');
                $test_date_day = date('l', date_timestamp_get($test_date));
                $test_dates = date('Y-m-d', date_timestamp_get($test_date));
                list($y, $m, $d) = explode('-', $test_dates);
                if ($d >= 22 && $d < 29) {
                    if ($day == $test_date_day && $test_dates < $res2['till'] && $test_dates >= $res['start_date']) {
                        $date_semaine_actu = $test_dates;
                        list($ans_actu, $mois_actu, $j_actu) = explode('-', $date_semaine_actu);
                        $sec_actu = mktime(0, 0, 0, $mois_actu, $j_actu, $ans_actu);
                        $date_fin_actu = $sec_actu + $diff_datedeb_datefin;
                        $date_fin_actu = date('Y-m-d', $date_fin_actu);
                        if (isset($date_semaine_actu)) {
                            $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, $date_semaine_actu, $date_fin_actu, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        } else {
                            $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, null, null, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        }
                    }
                }
            }

        } else {
            $date_to_repeat = substr($res2['month'], 1);
            list($ans, $mois, $j) = explode('-', $start_date_load);
            $debut_de_mois = date('Y-m-d', mktime(0, 0, 0, $mois, 1, $ans));

            $date_semaine_actu = date('Y-m-d', mktime(0, 0, 0, $mois, $date_to_repeat, $ans));
            list($ans_fin, $mois_fin, $j_fin) = explode('-', $res['end_date']);
            $date_fin_actu = date('Y-m-d', mktime(0, 0, 0, $mois, $j_fin, $ans_fin));

            if (isset($date_semaine_actu) && $date_fin_actu <= $res2['till']) {
                $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, $date_semaine_actu, $date_fin_actu, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
            } else if ($date_fin_actu <= $res2['till']) {
                $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, null, null, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
            }

        }
        return $horraire_tab;
    }
    /**
     *  repeat_all_week : Créer en boucle les horaires répétés pour des horaires répété toutes les semaines.
     *
     *  @access public
     *  @return horraire_tab
     */
    public function repeat_all_week($res, $res2, $start_date_load, $end_date_load, $diff_datedeb_datefin, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation)
    {
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');

        if ($res2['repeat'] > 0) {
            $date_to_repeat = $res['start_date'];
            $jour_to_repeat = $res2['week'];
            $repeat_all_x = $res2['repeat'];
            $decal = ('+' . ($repeat_all_x * 7) . ' day');
            $tab_jour = array(
                "Sunday" => 7,
                "Monday" => 1,
                "Tuesday" => 2,
                "Wednesday" => 3,
                "Thursday" => 4,
                "Friday" => 5,
                "Saturday" => 6,
            );
            $jour_date_to_repeat = date('l', strtotime($date_to_repeat));
            $ind_date_jour_to_repeat = $tab_jour[$jour_date_to_repeat];
            $ind_jour_to_repeat = $tab_jour[$jour_to_repeat];
            list($ans, $mois, $j) = explode('-', $date_to_repeat);
            if ($ind_jour_to_repeat < $ind_date_jour_to_repeat) {
                $decallement = $ind_date_jour_to_repeat - $ind_jour_to_repeat;
                $jour_recherche = new DateTime();
                $jour_recherche->setTimestamp(mktime(0, 0, 0, $mois, $j, $ans));
                date_sub($jour_recherche, date_interval_create_from_date_string($decallement . ' days'));
                $jour_recherche->modify($decal);
                $start_date_load_sec = strtotime($start_date_load);
                if (date_timestamp_get($jour_recherche) <= $start_date_load_sec) {
                    for ($k = 0; $k < 1000; ++$k) {
                        if ($start_date_load_sec <= date_timestamp_get($jour_recherche)) {
                            $date_semaine_actu = date('Y-m-d', date_timestamp_get($jour_recherche));
                            break;
                        }
                        $jour_recherche->modify($decal);
                    }
                } else {
                    $date_semaine_actu = date('Y-m-d', date_timestamp_get($jour_recherche));
                }
            } else {
                $decallement = $ind_jour_to_repeat - $ind_date_jour_to_repeat;
                $jour_recherche = new DateTime();
                $jour_recherche->setTimestamp(mktime(0, 0, 0, $mois, $j, $ans));
                $jour_recherche->modify('+' . $decallement . ' day');
                $start_date_load_sec = strtotime($start_date_load);
                if (date_timestamp_get($jour_recherche) <= $start_date_load_sec) {
                    for ($k = 0; $k < 1000; ++$k) {
                        if ($start_date_load_sec <= date_timestamp_get($jour_recherche)) {
                            $date_semaine_actu = date('Y-m-d', date_timestamp_get($jour_recherche));
                            break;
                        }
                        $jour_recherche->modify($decal);
                    }
                } else {
                    $date_semaine_actu = date('Y-m-d', date_timestamp_get($jour_recherche));
                }
            }
            list($ans_actu, $mois_actu, $j_actu) = explode('-', $date_semaine_actu);
            $sec_actu = mktime(0, 0, 0, $mois_actu, $j_actu, $ans_actu);
            $date_fin_actu = $sec_actu + $diff_datedeb_datefin;
            $date_fin_actu = date('Y-m-d', $date_fin_actu);

            if (isset($date_semaine_actu) && $date_semaine_actu <= $res2['till'] && $res['start_date'] <= $date_semaine_actu) {
                $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, $date_semaine_actu, $date_fin_actu, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
            } else if ($date_semaine_actu <= $res2['till'] && $res['start_date'] <= $date_semaine_actu) {
                $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, null, null, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
            }

            $jour_recherche->modify($decal);
            $end_date_load_sec = strtotime($end_date_load);
            if (date_timestamp_get($jour_recherche) < $end_date_load_sec) {

                for ($m = 0; $m < 10; ++$m) {
                    if (date_timestamp_get($jour_recherche) >= $end_date_load_sec) {
                        break;
                    } else {
                        $date_semaine_actu = date('Y-m-d', date_timestamp_get($jour_recherche));
                        list($ans_actu, $mois_actu, $j_actu) = explode('-', $date_semaine_actu);
                        $sec_actu = mktime(0, 0, 0, $mois_actu, $j_actu, $ans_actu);
                        $date_fin_actu = $sec_actu + $diff_datedeb_datefin;
                        $date_fin_actu = date('Y-m-d', $date_fin_actu);

                        if (isset($date_semaine_actu) && $date_semaine_actu <= $res2['till'] && $res['start_date'] <= $date_semaine_actu) {
                            $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, $date_semaine_actu, $date_fin_actu, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        } else if ($date_semaine_actu <= $res2['till'] && $res['start_date'] <= $date_semaine_actu) {
                            $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, null, null, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                        }
                    }
                    $jour_recherche->modify($decal);
                }

            }

        }
        return $horraire_tab;
    }
    /**
     *  repeat_all_week : Créer en boucle les horaires répétés pour des horaires répété tous les jours spécifiés ( Ex : répété tout les lundi ).
        Fonctionnement : Créer des dates comprises entre $start_date_load et $end_date_load , vérifie que la date créée est inferieur à la date de répétition
                         maximal et vérifié si cette date correspond créer un horaire pour celle-ci. 
     *
     *  @access public
     *  @return horraire_tab
     */
    public function repeat_all_spec_day($res, $res2, $start_date_load, $end_date_load, $diff_datedeb_datefin, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation)
    {
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');
        
        $repeat_day = $fullcalendar_mdl->getRepeatDay($res2);
        
        $start_date_load_s = strtotime($start_date_load);

        $date_time_deb = new DateTime();
        $date_time_deb->setTimestamp($start_date_load_s);
        $date_time_deb->format('Y-m-d');
        $test_date = new DateTime();
        $test_date = $date_time_deb;
        date_sub($test_date, date_interval_create_from_date_string('1 days'));
        for ($k = 0; $k < 32; ++$k) {
            $test_date->modify('+1 day');
            $test_date_day = date('l', date_timestamp_get($test_date));
            $test_dates = date('Y-m-d', date_timestamp_get($test_date));
            if ($test_dates >= $start_date_load && $test_dates <= $end_date_load && $test_dates <= $res2['till'] && $test_dates >= $res['start_date']) {
                if ($repeat_day == $test_date_day) {
                    $date_semaine_actu = $test_dates;
                    list($ans_actu, $mois_actu, $j_actu) = explode('-', $date_semaine_actu);
                    $sec_actu = mktime(0, 0, 0, $mois_actu, $j_actu, $ans_actu);
                    $date_fin_actu = $sec_actu + $diff_datedeb_datefin;
                    $date_fin_actu = date('Y-m-d', $date_fin_actu);
                    if (isset($date_semaine_actu)) {
                        $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, $date_semaine_actu, $date_fin_actu, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                    } else {
                        $horraire_tab = $fullcalendar_mdl->createHoraire($res, $start_date_load, $end_date_load, null, null, $request, $times_creneaux, $horraire_tab, $admin, $horaire, $maximum_number_place, $maximum_number_place_by_reservation);
                    }

                }
            }
        }

        return $horraire_tab;
    }
    /**
     *  getRepeatDay : renvoie le jour à répéter en anglais.  
     *
     *  @access public
     *  @return repeat_day
     */
    public function getRepeatDay($res2) {
        $tab_jour = array(
            "Sunday" => 0,
            "Monday" => 1,
            "Tuesday" => 2,
            "Wednesday" => 3,
            "Thursday" => 4,
            "Friday" => 5,
            "Saturday" => 6,
        );
        $tab_jour_fr = array(
            "dimanche" => 0,
            "lundi" => 1,
            "mardi" => 2,
            "mercredi" => 3,
            "jeudi" => 4,
            "vendredi" => 5,
            "samedi" => 6,
        );
        $repeat_day = $res2['repeat_all'];
        foreach ($tab_jour_fr as $jour => $nb) {
            if ($jour == $repeat_day) {
                $ind = $nb;
            }
        }
        foreach ($tab_jour as $day => $nb_eng) {
            if ($nb_eng == $ind) {
                $repeat_day = $day;
            }
        }
        return $repeat_day;
    }

}
