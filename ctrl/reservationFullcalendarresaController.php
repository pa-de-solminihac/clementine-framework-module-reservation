<?php
class reservationFullcalendarresaController extends reservationFullcalendarresaController_Parent
{
    /**
     * createCalendarUtilisateur : Fait partis des 3 fonctions créant le calendrier du côté utilisateur et du côté client,
     *                             Cette fonction s'occupe principalement de déclarer les variables,
     *                             A la base la fonction faisait 400 lignes, étant ilisibles, nous avons du la découpé en 3 plus petites fonctions
     *
     * @access public
     * @return void
     */
    public function createCalendarUtilisateur($horraire_dispo, $id_ressource, $params = null, $start_date_load, $end_date_load, $list_creneaux, $tab_horaire, $nb_recherche)
    {
        $lang = Clementine::$config['module_reservation']['lang'];
        $dispo = Clementine::$config['module_reservation']['dispo'];
        $incomplet = Clementine::$config['module_reservation']['incomplet'];
        $full = Clementine::$config['module_reservation']['complet'];
        if (isset($params['color']['dispo'])) {
            $dispo = $params['color']['dispo'];
        } elseif (isset($params['color']['incomplet'])) {
            $incomplet = $params['color']['incomplet'];
        } elseif (isset($params['color']['full'])) {
            $full = $params['color']['full'];
        }
        if (isset($params['number_max_print_col'])) {
            $number_max_print_col = $params['number_max_print_col'];
        } else {
            $number_max_print_col = Clementine::$config['module_reservation']['number_max_print_col'];
        }
        $request = $this->getRequest();
        $reservation_mdl = $this->getModel('reservation');
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');
        $user = $this->getModel('users');
        $ressource_mdl = $this->getModel('ressource');
        $horaire_ctrl = $this->getController('horaire');
        $fullcalendar_helper = $this->getHelper('fullcalendarresa');
        $horaire_mdl = $this->getModel('horaire');
        $tab_url_resa = $reservation_mdl->getUrlResa($id_ressource, $start_date_load, $end_date_load);
        $orange_tab = $reservation_mdl->getTabNbPlaceSup1($id_ressource, $start_date_load, $end_date_load);
        $creneaux = $ressource_mdl->getCreneaux($id_ressource);
        $nbPlaceMax = $ressource_mdl->getMaximumNumberPlace($id_ressource);
        $nb_place_max_reservation = $ressource_mdl->getNbPlaceMax($id_ressource);
        $nb_place_max_horaire_tmp = $fullcalendar_mdl->getTotalHorraireResa($id_ressource, false, false, $start_date_load, $end_date_load);
        $nb_place_max_horaire = array();
        $i = 0;
        foreach ($nb_place_max_horaire_tmp as $key => $value) {
            list($start_date, $start_hour) = explode('T', $value->start);
            list($end_date, $end_hour) = explode('T', $value->end);
            $nb_place_max_horaire[$i]['start_date'] = $start_date;
            $nb_place_max_horaire[$i]['end_date'] = $end_date;
            $nb_place_max_horaire[$i]['start_hour'] = $start_hour;
            $nb_place_max_horaire[$i]['end_hour'] = $end_hour;
            $nb_place_max_horaire[$i]['maximum_number_place_by_reservation'] = $value->maximum_number_place_by_reservation;
            $nb_place_max_horaire[$i]['maximum_number_place'] = $value->maximum_number_place;
            $i++;
        }
        $creneaux_sec = $fullcalendar_helper->timeToSecond($creneaux);
        $tab_horaire_except = $horaire_ctrl->getTotalHoraireExcep($tab_horaire);
        $privileges = array(
            'clementine_reservation_gerer_reservation' => true
        );
        $privileges_client = array(
            'clementine_reservation_regarder_reservation' => true
        );
        $admin = $user->hasPrivilege($privileges);
        $client = $user->hasPrivilege($privileges_client);
        $edit = false;
        $name = false;
        $connecte = false;
        if ($admin) {
            $edit = true;
        } else if ($client) {
            $auth = $user->getAuth();
            $name = $auth[Clementine::$config['module_reservation']['getuser_lastname']] . ' ' . $auth[Clementine::$config['module_reservation']['getuser_firstname']];
        }
        if ($name) {
            $connecte = true;
        }
        $complete_tab = array();
        $under_file = trim($request->ACT);
        if ($under_file == "updateajax") {
            $under_file = 'calendar';
        }
        if ($lang == 'fr') {
            $title_dispo = 'Dispo';
            $title_incomplet = 'Incomplet';
            $title_non_dispo = 'Non dispo';
            $title_non_autoriser = 'Non autorisé';
        } else {
            $title_dispo = 'Available';
            $title_incomplet = 'Incomplete';
            $title_non_dispo = 'Not available';
            $title_non_autoriser = 'Unauthorized';
        }
        return $this->boucleCreationCalendar($list_creneaux, $horraire_dispo, $reservation_mdl, $id_ressource, $admin, $under_file, $orange_tab, $full, $edit, $number_max_print_col, $lang, $start_date_load, $end_date_load, $title_dispo, $title_incomplet, $title_non_dispo, $title_non_autoriser, $nb_recherche, $dispo, $incomplet, $full, $nbPlaceMax, $nb_place_max_horaire, $nb_place_max_reservation, $connecte, $name, $tab_url_resa);
    }

    /**
     * boucleCreationCalendar : Fais partis des 3 fonctions de la création du calendrier.
     *                          Cette fonction s'occupe de tous la fonctionnalité de base du calendrier,
     *                          Les disponibilités, les places authorisés, mais également les créneaux occupé etc.
     *
     * @access public
     * @return void
     */
    public function boucleCreationCalendar($list_creneaux, $horraire_dispo, $reservation_mdl, $id_ressource, $admin, $under_file, $orange_tab, $full, $edit, $number_max_print_col, $lang, $start_date_load, $end_date_load, $title_dispo, $title_incomplet, $title_non_dispo, $title_non_autoriser, $nb_recherche, $dispo, $incomplet, $full, $nbPlaceMax, $nb_place_max_horaire, $nb_place_max_reservation, $connecte, $name, $tab_url_resa)
    {
        $tab_disponibilite = array();
        $taille_horaire_dispo = count($horraire_dispo);
        $nb_place_max_tmp = $nbPlaceMax;
        $url = "";
        $user = $this->getModel('users');
        $auth = $user->getAuth();
        $plein = true;
        $url_pass = false;
        if (Clementine::$config['module_reservation']['force'] == 0) {
            if (!$auth) {
                $url = __WWW__ . '/reservation/choix';
            } else {
                $plein = false;
            }
        } else if (Clementine::$config['module_reservation']['force'] == 1) {
            if (!$auth) {
                if (!empty(Clementine::$config['module_reservation']['url_connect'])) {
                    $url = __WWW__ . Clementine::$config['module_reservation']['url_connect'];
                } else if (!empty(Clementine::$config['module_reservation']['url_register'])) {
                    $url = __WWW__ . Clementine::$config['module_reservation']['url_register'];
                } else {
                    $plein = false;
                }
            } else {
                $plein = false;
            }
        } else {
            $plein = false;
        }
        for ($i = 0; $i < $taille_horaire_dispo; $i = $i + 2) {
            if ($horraire_dispo[$i] >= $start_date_load && $horraire_dispo[$i] < $end_date_load) {
                $taille_horaire_dispo_i = count($horraire_dispo[$i + 1]) - 1;
                $time_creneaux = $horraire_dispo[$i + 1][count($horraire_dispo[$i + 1]) - 1];
                for ($j = 0; $j < $taille_horaire_dispo_i; ++$j) {
                    list($start, $end) = explode('-', $horraire_dispo[$i + 1][$j]);
                    $start = trim($start);
                    if (!$plein) {
                        $url = __WWW__ . '/reservation/create?start_date=' . $horraire_dispo[$i] . '_' . $start . '&clementine_reservation_ressource-id=' . $id_ressource;
                    } else if (!$url_pass) {
                        $url.= '?start_date=' . $horraire_dispo[$i] . '_' . $start . '&clementine_reservation_ressource-id=' . $id_ressource;
                        $url_pass = true;
                    }
                    $continue = false;
                    if (isset($orange_tab[$horraire_dispo[$i] . ' ' . $start . '-' . $horraire_dispo[$i] . ' ' . $end])) {
                        if (($admin && ($under_file == 'calendar' || $under_file == 'all')) || ($connecte == 1 && ($under_file == 'calendar' || $under_file == 'all'))) {
                            if ($lang == 'fr') {
                                $title = 'Incomplet';
                            } else {
                                $title = 'Incomplete';
                            }
                            $url_update = $reservation_mdl->getUrlUpdateByHour($horraire_dispo[$i] . ' ' . $start, $horraire_dispo[$i] . ' ' . $end, $id_ressource);
                            if ($orange_tab[$horraire_dispo[$i] . ' ' . $start . '-' . $horraire_dispo[$i] . ' ' . $end] >= 1) {
                                if ($orange_tab[$horraire_dispo[$i] . ' ' . $start . '-' . $horraire_dispo[$i] . ' ' . $end] <= $number_max_print_col) {
                                    $number_create = $orange_tab[$horraire_dispo[$i] . ' ' . $start . '-' . $horraire_dispo[$i] . ' ' . $end];
                                    for ($l = 0; $l < $number_create; ++$l) {
                                        $tab_disponibilite = $this->createNumberCreateOrange($l, $url_update, $reservation_mdl, $connecte, $name, $horraire_dispo, $i, $start, $end, $time_creneaux, $tab_disponibilite);
                                    }
                                    if (!empty($nb_place_max_horaire)) {
                                        $res_non_autorise = $this->createNonAutorise($nb_place_max_horaire, $horraire_dispo, $i, $start, $end, $nb_recherche, $title_non_autoriser, $time_creneaux, $continue, $nbPlaceMax, $nb_place_max_reservation, $tab_disponibilite);
                                        $tab_disponibilite = $res_non_autorise['tab_disponibilite'];
                                        $continue = $res_non_autorise['continue'];
                                        $nbPlaceMax = $res_non_autorise['nbPlaceMax'];
                                    }
                                    if ($continue) {
                                        continue;
                                    } else if (($nbPlaceMax - $reservation_mdl->getNbPlacePrise($horraire_dispo[$i] . ' ' . $start, $horraire_dispo[$i] . ' ' . $end, $id_ressource)) < $nb_recherche) {
                                        $disponibilite = (object)array(
                                            'start' => $horraire_dispo[$i] . 'T' . $start,
                                            'end' => $horraire_dispo[$i] . 'T' . $end,
                                            'title' => $title_non_dispo,
                                            'color' => 'grey',
                                            'time_creneaux' => $time_creneaux,
                                        );
                                        array_push($tab_disponibilite, $disponibilite);
                                    } else {
                                        $disponibilite = (object)array(
                                            'start' => $horraire_dispo[$i] . 'T' . $start,
                                            'end' => $horraire_dispo[$i] . 'T' . $end,
                                            'title' => $title_dispo,
                                            'editable' => false,
                                            'url' => __WWW__ . '/reservation/create?start_date=' . $horraire_dispo[$i] . '_' . $start . '&clementine_reservation_ressource-id=' . $id_ressource . '&nb_recherche=' . $nb_recherche,
                                            'className' => 'dispo',
                                            'time_creneaux' => $time_creneaux,
                                        );
                                        array_push($tab_disponibilite, $disponibilite);
                                    }
                                } else if ($admin) {
                                    $res_non_autorise_admin = $this->createIfAdmin($nb_place_max_horaire, $horraire_dispo, $i, $start, $end, $nb_recherche, $title_non_autoriser, $time_creneaux, $continue, $nbPlaceMax, $nb_place_max_reservation, $tab_disponibilite, $reservation_mdl, $id_ressource, $url_update, $title_dispo, $nb_place_max_tmp);
                                    $tab_disponibilite = $res_non_autorise_admin['tab_disponibilite'];
                                    $continue = $res_non_autorise_admin['continue'];
                                    $nbPlaceMax = $res_non_autorise_admin['nbPlaceMax'];
                                } else {
                                    $title = $reservation_mdl->getNameByIdResa($url_update['id_reservation0']);
                                    $disponibilite = (object)array(
                                        'start' => $horraire_dispo[$i] . 'T' . $start,
                                        'end' => $horraire_dispo[$i] . 'T' . $end,
                                        'title' => $title_dispo,
                                        'editable' => false,
                                        'url' => __WWW__ . '/reservation/create?start_date=' . $horraire_dispo[$i] . '_' . $start . '&clementine_reservation_ressource-id=' . $id_ressource . '&nb_recherche=' . $nb_recherche,
                                        'className' => 'dispo',
                                        'time_creneaux' => $time_creneaux,
                                    );
                                    array_push($tab_disponibilite, $disponibilite);
                                }
                            } else {
                                $res_if_orange_inf_1 = $this->ifOrangeTabInf1($nb_place_max_horaire, $horraire_dispo, $i, $start, $end, $nb_recherche, $title_non_autoriser, $time_creneaux, $continue, $nbPlaceMax, $nb_place_max_reservation, $tab_disponibilite, $reservation_mdl, $id_ressource, $url_update, $title_dispo, $nb_place_max_tmp, $l, $connecte, $name);
                                $tab_disponibilite = $res_if_orange_inf_1['tab_disponibilite'];
                                $continue = $res_if_orange_inf_1['continue'];
                                $nbPlaceMax = $res_if_orange_inf_1['nbPlaceMax'];
                            }
                        } else {
                            $res_non_connecte = $this->ifNonConnecte($nb_place_max_horaire, $horraire_dispo, $i, $start, $end, $nb_recherche, $title_non_autoriser, $time_creneaux, $continue, $nbPlaceMax, $nb_place_max_reservation, $tab_disponibilite, $reservation_mdl, $id_ressource, $title_dispo, $url, $nb_place_max_tmp);
                            $tab_disponibilite = $res_non_connecte['tab_disponibilite'];
                            $continue = $res_non_connecte['continue'];
                            $nbPlaceMax = $res_non_connecte['nbPlaceMax'];
                        }
                    } else {
                        $res_non_tab_orange = array();
                        $continue = false;
                        $res_non_tab_orange = $this->ifNonTabOrange($nb_place_max_horaire, $horraire_dispo, $i, $start, $end, $nb_recherche, $title_non_autoriser, $time_creneaux, $continue, $nbPlaceMax, $nb_place_max_reservation, $tab_disponibilite, $title_dispo, $nb_place_max_tmp, $url);
                        $tab_disponibilite = $res_non_tab_orange['tab_disponibilite'];
                        $continue = $res_non_tab_orange['continue'];
                        $nbPlaceMax = $res_non_tab_orange['nbPlaceMax'];
                    }
                }
            }
        }
        return $this->calendarPlusResa($list_creneaux, $horraire_dispo, $reservation_mdl, $id_ressource, $admin, $under_file, $orange_tab, $tab_disponibilite, $full, $edit, $number_max_print_col, $lang, $connecte, $name, $tab_url_resa);
    }

    /**
     * calendarPlusResa : Fais partis des 3 fonctions de la création du calendrier.
     *                    Cette fonction s'occupe d'ajouter les reservations aux calendrier,
     *                    Les disponibilités, les places authorisés, mais également les créneaux occupé etc.
     *
     * @access public
     * @return void
     */
    public function calendarPlusResa($list_creneaux, $horraire_dispo, $reservation_mdl, $id_ressource, $admin, $under_file, $orange_tab, $tab_disponibilite, $full, $edit, $number_max_print_col, $lang, $connecte, $name, $tab_url_resa)
    {
        $excep = array();
        $url = false;
        $user = $this->getModel('users');
        $taille_list_creneaux = count($list_creneaux);
        for ($e = 0; $e < $taille_list_creneaux - 1; $e = $e + 2) {
            $complette = array_diff($list_creneaux[$e + 1], $horraire_dispo[$e + 1]);
            $time_creneaux = $list_creneaux[$e + 1][count($list_creneaux[$e + 1]) - 1];
            if (isset($complette)) {
                foreach ($complette as $element) {
                    list($start, $end) = explode('-', $element);
                    $url_update = $reservation_mdl->getUrlUpdateByHour($list_creneaux[$e] . ' ' . trim($start) , $list_creneaux[$e] . ' ' . $end, $id_ressource);
                    if (($admin && ($under_file == 'calendar' || $under_file == 'all')) || ($connecte == 1 && ($under_file == 'calendar' || $under_file == 'all'))) {
                        if (isset($orange_tab[$list_creneaux[$e] . ' ' . trim($start) . '-' . $list_creneaux[$e] . ' ' . $end])) {
                            $info = $list_creneaux[$e] . ' ' . trim($start) . '-' . $list_creneaux[$e] . ' ' . $end;
                            if (isset($tab_url_resa[$info])) {
                                $url = $tab_url_resa[$info];
                            }
                            if ($orange_tab[$list_creneaux[$e] . ' ' . trim($start) . '-' . $list_creneaux[$e] . ' ' . $end] >= 1 && $orange_tab[$list_creneaux[$e] . ' ' . trim($start) . '-' . $list_creneaux[$e] . ' ' . $end] <= $number_max_print_col) {
                                for ($l = 0; $l < $orange_tab[$list_creneaux[$e] . ' ' . trim($start) . '-' . $list_creneaux[$e] . ' ' . $end]; ++$l) {
                                    $title = $reservation_mdl->getNameByIdResa($url_update['id_reservation' . $l]);
                                    if (empty($title) || $title == " ") {
                                        $usr = $user->getUser($reservation_mdl->getIdClemByIdResa($url_update['id_reservation' . $l]));
                                        $title = $usr[Clementine::$config['module_reservation']['getuser_lastname']] . ' ' . $usr[Clementine::$config['module_reservation']['getuser_firstname']];
                                    }
                                    if ($connecte) {
                                        if ($title == $name) {
                                            $complet = (object)array(
                                                'start' => $list_creneaux[$e] . 'T' . trim($start) ,
                                                'end' => $list_creneaux[$e] . 'T' . $end,
                                                'title' => $title,
                                                'color' => $full,
                                                'editable' => $edit,
                                                'url' => $url_update[$l],
                                                'time_creneaux' => $time_creneaux,
                                            );
                                            array_push($excep, $complet);
                                        } else {
                                            $complet = (object)array(
                                                'start' => $list_creneaux[$e] . 'T' . trim($start) ,
                                                'end' => $list_creneaux[$e] . 'T' . $end,
                                                'title' => "Complet",
                                                'color' => $full,
                                                'editable' => $edit,
                                                'time_creneaux' => $time_creneaux,
                                            );
                                            array_push($excep, $complet);
                                            array_unique($excep, SORT_REGULAR);
                                        }
                                    } else {
                                        $complet = (object)array(
                                            'start' => $list_creneaux[$e] . 'T' . trim($start) ,
                                            'end' => $list_creneaux[$e] . 'T' . $end,
                                            'title' => $title,
                                            'color' => $full,
                                            'editable' => $edit,
                                            'url' => $url_update[$l],
                                            'time_creneaux' => $time_creneaux,
                                        );
                                        array_push($excep, $complet);
                                    }
                                }
                            } else if ($admin) {
                                $complet = (object)array(
                                    'start' => $list_creneaux[$e] . 'T' . trim($start) ,
                                    'end' => $list_creneaux[$e] . 'T' . $end,
                                    'title' => '...',
                                    'color' => $full,
                                    'editable' => $edit,
                                    'url' => __WWW__ . '/reservation?start_date=' . $list_creneaux[$e] . ' ' . trim($start) . '&end_date=' . $list_creneaux[$e] . ' ' . trim($end) . '',
                                    'className' => 'listeRed',
                                    'time_creneaux' => $time_creneaux,
                                );
                                array_push($excep, $complet);
                            }
                        } else {
                            $title = $reservation_mdl->getNameByIdResa($url_update['id_reservation0']);
                            if (empty($title) || $title == " ") {
                                $usr = $user->getUser($reservation_mdl->getIdClemByIdResa($url_update['id_reservation' . $l]));
                                $title = $usr[Clementine::$config['module_reservation']['getuser_lastname']] . ' ' . $usr[Clementine::$config['module_reservation']['getuser_firstname']];
                            }
                            if ($connecte) {
                                if ($title == $name) {
                                    $complet = (object)array(
                                        'start' => $list_creneaux[$e] . 'T' . trim($start) ,
                                        'end' => $list_creneaux[$e] . 'T' . $end,
                                        'title' => $title,
                                        'color' => $full,
                                        'editable' => $edit,
                                        'url' => $url_update[0],
                                        'time_creneaux' => $time_creneaux,
                                    );
                                    array_push($excep, $complet);
                                }
                            } else {
                                $complet = (object)array(
                                    'start' => $list_creneaux[$e] . 'T' . trim($start) ,
                                    'end' => $list_creneaux[$e] . 'T' . $end,
                                    'title' => $title,
                                    'color' => $full,
                                    'editable' => $edit,
                                    'url' => $url_update[0],
                                    'time_creneaux' => $time_creneaux,
                                );
                                array_push($excep, $complet);
                            }
                        }
                    } else {
                        if ($lang == 'fr') {
                            $title = 'Complet';
                        } else {
                            $title = 'Full';
                        }
                        $info = $list_creneaux[$e] . ' ' . trim($start) . '-' . $list_creneaux[$e] . ' ' . $end;
                        if (isset($tab_url_resa[$info])) {
                            $url = $tab_url_resa[$info];
                        }
                        $complet = (object)array(
                            'start' => $list_creneaux[$e] . 'T' . trim($start) ,
                            'end' => $list_creneaux[$e] . 'T' . $end,
                            'title' => $title,
                            // 'color' => $full,
                            'url' => $url,
                            'className' => 'full',
                            'time_creneaux' => $time_creneaux,
                        );
                        array_push($excep, $complet);
                    }
                }
            } else {
                if ($lang == 'fr') {
                    $title = 'Complet';
                } else {
                    $title = 'Full';
                }
                $complet = (object)array(
                    'start' => $list_creneaux[$e] . 'T' . trim($start) ,
                    'end' => $list_creneaux[$e] . 'T' . $end,
                    'title' => $title,
                    // 'color' => $full,
                    'editable' => 'false',
                    'url' => $url,
                    'className' => 'full',
                    'time_creneaux' => $time_creneaux,
                );
                array_push($excep, $complet);
            }
        }
        $tab_disponibilite = array_merge($tab_disponibilite, $excep);
        return $tab_disponibilite;
    }

    /**
     * verifDatePossible : Et l'équivalent d'un in_array mais pour les dates et/ou les créneaux
     *
     * @access public
     * @return boolean
     */
    public function verifDatePossible($start_date, $end_date, $plage_horraire)
    {
        $plage_size = count($plage_horraire);
        list($start_day, $start_hour) = explode(' ', $start_date);
        list($end_day, $end_hour) = explode(' ', $end_date);
        $valide = false;
        for ($i = 0; $i < $plage_size; ++$i) {
            list($dateDebPossible, $heureDebPossible) = explode('T', $plage_horraire[$i]->start);
            list($dateFinPossible, $heureFinPossible) = explode('T', $plage_horraire[$i]->end);
            if ($dateDebPossible == $start_day && $heureDebPossible == $start_hour && $heureFinPossible == $end_hour) {
                $valide = true;
            }
        }
        return $valide;
    }

    /**
     * getListCreneauxParJour : retourne la liste des créneaux sur une heure donné
     *
     * @access public
     * @return void
     */
    public function getListCreneauxParJour($jour, $plage_horraire)
    {
        $size = count($plage_horraire);
        $return_tab = array();
        for ($i = 0; $i < $size; $i = $i + 2) {
            if ($plage_horraire[$i] == $jour) {
                $return_tab = array_merge($return_tab, $plage_horraire[$i + 1]);
            }
        }
        return $return_tab;
    }

    /**
     * createTimeline : A partir de la matrice de création créé la timeline des ressources pour la vue tous
     *
     * @access public
     * @return void
     */
    public function createTimeline($matrice)
    {
        $taille_matrice = count($matrice);
        $tab_resultat = array();
        $tab_ressource = array();
        $tab_resa = array();
        for ($i = 0; $i < $taille_matrice; ++$i) {
            $id_ressource = $matrice[$i][0];
            $title_ressource = $matrice[$i][1];
            $ressource_object = (object)array(
                'id' => $id_ressource,
                'title' => $title_ressource,
            );
            array_push($tab_ressource, $ressource_object);
            $taille_evt = count($matrice[$i][4]);
            for ($j = 0; $j < $taille_evt; ++$j) {
                $array_res = array();
                $resourceId = 'resourceId';
                $val_actu = $matrice[$i][4][$j];
                $result = (object)array();
                foreach ($val_actu as $k => $v) {
                    $result->$k = $v;
                }
                $result->$resourceId = $id_ressource;
                array_push($tab_resa, $result);
            }
        }
        $tab_resultat['ressource'] = $tab_ressource;
        $tab_resultat['resa'] = $tab_resa;
        return $tab_resultat;
    }

    public function createNumberCreateOrange($l, $url_update, $reservation_mdl, $connecte, $name, $horraire_dispo, $i, $start, $end, $time_creneaux, &$tab_disponibilite)
    {
        $title = $reservation_mdl->getNameByIdResa($url_update['id_reservation' . $l]);
        if (empty($title) || $title == " ") {
            $usr = $user->getUser($reservation_mdl->getIdClemByIdResa($url_update['id_reservation' . $l]));
            $title = $usr[Clementine::$config['module_reservation']['getuser_lastname']] . ' ' . $usr[Clementine::$config['module_reservation']['getuser_firstname']];
        }
        if ($connecte) {
            if ($title == $name) {
                $disponibilite = (object)array(
                    'start' => $horraire_dispo[$i] . 'T' . $start,
                    'end' => $horraire_dispo[$i] . 'T' . $end,
                    'title' => $name,
                    'url' => $url_update[$l],
                    'editable' => true,
                    'className' => 'occupe',
                    'time_creneaux' => $time_creneaux,
                );
                array_push($tab_disponibilite, $disponibilite);
            }
        } else {
            $disponibilite = (object)array(
                'start' => $horraire_dispo[$i] . 'T' . $start,
                'end' => $horraire_dispo[$i] . 'T' . $end,
                'title' => $title,
                'url' => $url_update[$l],
                'editable' => true,
                'className' => 'occupe',
                'time_creneaux' => $time_creneaux,
            );
            array_push($tab_disponibilite, $disponibilite);
        }
        return $tab_disponibilite;
    }

    public function createNonAutorise($nb_place_max_horaire, $horraire_dispo, $i, $start, $end, $nb_recherche, $title_non_autoriser, $time_creneaux, $continue, $nbPlaceMax, $nb_place_max_reservation, &$tab_disponibilite)
    {
        foreach ($nb_place_max_horaire as $key => $value) {
            if (($horraire_dispo[$i] > $value['start_date'] && $horraire_dispo[$i] < $value['end_date']) || ($horraire_dispo[$i] == $value['start_date'] && $start >= $value['start_hour']) || ($horraire_dispo[$i] == $value['end_date'] && $end <= $value['end_hour'])) {
                if (is_numeric($nb_recherche) && !empty($value["maximum_number_place_by_reservation"]) && $nb_recherche > $value['maximum_number_place_by_reservation']) {
                    $disponibilite = (object)array(
                        'start' => $horraire_dispo[$i] . 'T' . $start,
                        'end' => $horraire_dispo[$i] . 'T' . $end,
                        'title' => $title_non_autoriser,
                        'color' => 'grey',
                        'time_creneaux' => $time_creneaux,
                    );
                    array_push($tab_disponibilite, $disponibilite);
                    $continue = true;
                    break;
                } else if (is_numeric($nb_recherche) && empty($value["maximum_number_place_by_reservation"]) && $nb_recherche > $nb_place_max_reservation) {
                    $disponibilite = (object)array(
                        'start' => $horraire_dispo[$i] . 'T' . $start,
                        'end' => $horraire_dispo[$i] . 'T' . $end,
                        'title' => $title_non_autoriser,
                        'color' => 'grey',
                        'time_creneaux' => $time_creneaux,
                    );
                    array_push($tab_disponibilite, $disponibilite);
                    $continue = true;
                    break;
                }
                if (!empty($value["maximum_number_place"]) && $value["maximum_number_place"] != $nbPlaceMax) {
                    $nbPlaceMax = $value["maximum_number_place"];
                }
            }
        }
        $resultat = array();
        $resultat['tab_disponibilite'] = $tab_disponibilite;
        $resultat['continue'] = $continue;
        $resultat['nbPlaceMax'] = $nbPlaceMax;
        return $resultat;
    }

    public function createIfAdmin($nb_place_max_horaire, $horraire_dispo, $i, $start, $end, $nb_recherche, $title_non_autoriser, $time_creneaux, $continue, $nbPlaceMax, $nb_place_max_reservation, &$tab_disponibilite, $reservation_mdl, $id_ressource, $url_update, $title_dispo, $nb_place_max_tmp)
    {
        $disponibilite = (object)array(
            'start' => $horraire_dispo[$i] . 'T' . $start,
            'end' => $horraire_dispo[$i] . 'T' . $end,
            'title' => '...',
            'url' => __WWW__ . '/reservation?start_date=' . $horraire_dispo[$i] . ' ' . trim($start) . '&end_date=' . $horraire_dispo[$i] . ' ' . trim($end) . '',
            'editable' => true,
            'className' => 'listeOrange',
            'time_creneaux' => $time_creneaux,
        );
        array_push($tab_disponibilite, $disponibilite);
        $continue = false;
        if (!empty($nb_place_max_horaire)) {
            foreach ($nb_place_max_horaire as $key => $value) {
                if (($horraire_dispo[$i] > $value['start_date'] && $horraire_dispo[$i] < $value['end_date']) || ($horraire_dispo[$i] == $value['start_date'] && $start >= $value['start_hour']) || ($horraire_dispo[$i] == $value['end_date'] && $end <= $value['end_hour'])) {
                    if (is_numeric($nb_recherche) && !empty($value["maximum_number_place_by_reservation"]) && $nb_recherche > $value['maximum_number_place_by_reservation']) {
                        $disponibilite = (object)array(
                            'start' => $horraire_dispo[$i] . 'T' . $start,
                            'end' => $horraire_dispo[$i] . 'T' . $end,
                            'title' => $title_non_autoriser,
                            'color' => 'grey',
                            'time_creneaux' => $time_creneaux,
                        );
                        array_push($tab_disponibilite, $disponibilite);
                        $continue = true;
                        break;
                    } else if (is_numeric($nb_recherche) && empty($value["maximum_number_place_by_reservation"]) && $nb_recherche > $nb_place_max_reservation) {
                        $disponibilite = (object)array(
                            'start' => $horraire_dispo[$i] . 'T' . $start,
                            'end' => $horraire_dispo[$i] . 'T' . $end,
                            'title' => $title_non_autoriser,
                            'color' => 'grey',
                            'time_creneaux' => $time_creneaux,
                        );
                        array_push($tab_disponibilite, $disponibilite);
                        $continue = true;
                        break;
                    }
                    if (!empty($value["maximum_number_place"]) && $value["maximum_number_place"] != $nbPlaceMax) {
                        $nbPlaceMax = $value["maximum_number_place"];
                    }
                }
            }
        }
        if ($continue) {
            continue;
        } else if (($nbPlaceMax - $reservation_mdl->getNbPlacePrise($horraire_dispo[$i] . ' ' . $start, $horraire_dispo[$i] . ' ' . $end, $id_ressource)) < $nb_recherche) {
            $disponibilite = (object)array(
                'start' => $horraire_dispo[$i] . 'T' . $start,
                'end' => $horraire_dispo[$i] . 'T' . $end,
                'title' => $title_non_dispo,
                'color' => 'grey',
                'time_creneaux' => $time_creneaux,
            );
            array_push($tab_disponibilite, $disponibilite);
        } else {
            $title = $reservation_mdl->getNameByIdResa($url_update['id_reservation0']);
            $disponibilite = (object)array(
                'start' => $horraire_dispo[$i] . 'T' . $start,
                'end' => $horraire_dispo[$i] . 'T' . $end,
                'title' => $title_dispo,
                'editable' => false,
                'url' => __WWW__ . '/reservation/create?start_date=' . $horraire_dispo[$i] . '_' . $start . '&clementine_reservation_ressource-id=' . $id_ressource . '&nb_recherche=' . $nb_recherche,
                'className' => 'dispo',
                'time_creneaux' => $time_creneaux,
            );
            array_push($tab_disponibilite, $disponibilite);
        }
        $nbPlaceMax = $nb_place_max_tmp;
        $resultat = array();
        $resultat['tab_disponibilite'] = $tab_disponibilite;
        $resultat['continue'] = $continue;
        $resultat['nbPlaceMax'] = $nbPlaceMax;
        return $resultat;
    }

    public function ifOrangeTabInf1($nb_place_max_horaire, $horraire_dispo, $i, $start, $end, $nb_recherche, $title_non_autoriser, $time_creneaux, $continue, $nbPlaceMax, $nb_place_max_reservation, &$tab_disponibilite, $reservation_mdl, $id_ressource, $url_update, $title_dispo, $nb_place_max_tmp, $l, $connecte, $name)
    {
        $title = $reservation_mdl->getNameByIdResa($url_update['id_reservation0']);
        if (empty($title) || $title == " ") {
            $usr = $user->getUser($reservation_mdl->getIdClemByIdResa($url_update['id_reservation' . $l]));
            $title = $usr[Clementine::$config['module_reservation']['getuser_lastname']] . ' ' . $usr[Clementine::$config['module_reservation']['getuser_firstname']];
        }
        if ($connecte) {
            if ($title == $name) {
                $disponibilite = (object)array(
                    'start' => $horraire_dispo[$i] . 'T' . $start,
                    'end' => $horraire_dispo[$i] . 'T' . $end,
                    'title' => $title,
                    'editable' => true,
                    'url' => $url_update[0],
                    'className' => 'occupe',
                    'time_creneaux' => $time_creneaux,
                );
                array_push($tab_disponibilite, $disponibilite);
            }
        } else {
            $disponibilite = (object)array(
                'start' => $horraire_dispo[$i] . 'T' . $start,
                'end' => $horraire_dispo[$i] . 'T' . $end,
                'title' => $title,
                'editable' => true,
                'url' => $url_update[0],
                'className' => 'occupe',
                'time_creneaux' => $time_creneaux,
            );
            array_push($tab_disponibilite, $disponibilite);
        }
        $continue = false;
        if (!empty($nb_place_max_horaire)) {
            foreach ($nb_place_max_horaire as $key => $value) {
                if (($horraire_dispo[$i] > $value['start_date'] && $horraire_dispo[$i] < $value['end_date']) || ($horraire_dispo[$i] == $value['start_date'] && $start >= $value['start_hour']) || ($horraire_dispo[$i] == $value['end_date'] && $end <= $value['end_hour'])) {
                    if (is_numeric($nb_recherche) && !empty($value["maximum_number_place_by_reservation"]) && $nb_recherche > $value['maximum_number_place_by_reservation']) {
                        $disponibilite = (object)array(
                            'start' => $horraire_dispo[$i] . 'T' . $start,
                            'end' => $horraire_dispo[$i] . 'T' . $end,
                            'title' => $title_non_autoriser,
                            'color' => 'grey',
                            'time_creneaux' => $time_creneaux,
                        );
                        array_push($tab_disponibilite, $disponibilite);
                        $continue = true;
                        break;
                    } else if (is_numeric($nb_recherche) && empty($value["maximum_number_place_by_reservation"]) && $nb_recherche > $nb_place_max_reservation) {
                        $disponibilite = (object)array(
                            'start' => $horraire_dispo[$i] . 'T' . $start,
                            'end' => $horraire_dispo[$i] . 'T' . $end,
                            'title' => $title_non_autoriser,
                            'color' => 'grey',
                            'time_creneaux' => $time_creneaux,
                        );
                        array_push($tab_disponibilite, $disponibilite);
                        $continue = true;
                        break;
                    }
                    if (!empty($value["maximum_number_place"]) && $value["maximum_number_place"] != $nbPlaceMax) {
                        $nbPlaceMax = $value["maximum_number_place"];
                    }
                }
            }
        }
        if ($continue) {
            continue;
        } else if (($nbPlaceMax - $reservation_mdl->getNbPlacePrise($horraire_dispo[$i] . ' ' . $start, $horraire_dispo[$i] . ' ' . $end, $id_ressource)) < $nb_recherche) {
            $disponibilite = (object)array(
                'start' => $horraire_dispo[$i] . 'T' . $start,
                'end' => $horraire_dispo[$i] . 'T' . $end,
                'title' => $title_non_dispo,
                'color' => 'grey',
                'time_creneaux' => $time_creneaux,
            );
            array_push($tab_disponibilite, $disponibilite);
        } else {
            $title = $reservation_mdl->getNameByIdResa($url_update['id_reservation0']);
            $disponibilite = (object)array(
                'start' => $horraire_dispo[$i] . 'T' . $start,
                'end' => $horraire_dispo[$i] . 'T' . $end,
                'title' => $title_dispo,
                'editable' => false,
                'url' => __WWW__ . '/reservation/create?start_date=' . $horraire_dispo[$i] . '_' . $start . '&clementine_reservation_ressource-id=' . $id_ressource,
                'className' => 'dispo',
                'time_creneaux' => $time_creneaux,
            );
            array_push($tab_disponibilite, $disponibilite);
        }
        $nbPlaceMax = $nb_place_max_tmp;
        $resultat = array();
        $resultat['tab_disponibilite'] = $tab_disponibilite;
        $resultat['continue'] = $continue;
        $resultat['nbPlaceMax'] = $nbPlaceMax;
        return $resultat;
    }

    public function ifNonConnecte($nb_place_max_horaire, $horraire_dispo, $i, $start, $end, $nb_recherche, $title_non_autoriser, $time_creneaux, $continue, $nbPlaceMax, $nb_place_max_reservation, &$tab_disponibilite, $reservation_mdl, $id_ressource, $title_dispo, $url, $nb_place_max_tmp)
    {
        $continue = false;
        if (!empty($nb_place_max_horaire)) {
            foreach ($nb_place_max_horaire as $key => $value) {
                if (($horraire_dispo[$i] > $value['start_date'] && $horraire_dispo[$i] < $value['end_date']) || ($horraire_dispo[$i] == $value['start_date'] && $start >= $value['start_hour']) || ($horraire_dispo[$i] == $value['end_date'] && $end <= $value['end_hour'])) {
                    if (is_numeric($nb_recherche) && !empty($value["maximum_number_place_by_reservation"]) && $nb_recherche > $value['maximum_number_place_by_reservation']) {
                        $disponibilite = (object)array(
                            'start' => $horraire_dispo[$i] . 'T' . $start,
                            'end' => $horraire_dispo[$i] . 'T' . $end,
                            'title' => $title_non_autoriser,
                            'color' => 'grey',
                            'time_creneaux' => $time_creneaux,
                        );
                        array_push($tab_disponibilite, $disponibilite);
                        $continue = true;
                        break;
                    } else if (isset($_SESSION['resa_unco'])) {
                        $full = false;
                        foreach ($_SESSION['resa_unco'] as $id_resa => $date) {
                            if ($date == ($horraire_dispo[$i] . ' ' . $start)) {
                                $title = $reservation_mdl->getNameByIdResa($id_resa);
                                $nb_place = $reservation_mdl->getNbPlaceByIdReservation($id_resa);
                                if (isset($value['maximum_number_place']) && $value['maximum_number_place'] == $nb_place) {
                                    $disponibilite = (object)array(
                                        'start' => $horraire_dispo[$i] . 'T' . $start,
                                        'end' => $horraire_dispo[$i] . 'T' . $end,
                                        'title' => $title,
                                        'url' => $url . '&nb_recherche=' . $nb_recherche,
                                        'className' => 'full',
                                        'time_creneaux' => $time_creneaux,
                                    );
                                    array_push($tab_disponibilite, $disponibilite);
                                    $full = true;
                                    break;
                                } else {
                                    $disponibilite = (object)array(
                                        'start' => $horraire_dispo[$i] . 'T' . $start,
                                        'end' => $horraire_dispo[$i] . 'T' . $end,
                                        'title' => $title,
                                        'url' => $url . '&nb_recherche=' . $nb_recherche,
                                        'className' => 'occupe',
                                        'time_creneaux' => $time_creneaux,
                                    );
                                    array_push($tab_disponibilite, $disponibilite);
                                    break;
                                }
                            }
                        }
                        if (!$full) {
                            $disponibilite = (object)array(
                                'start' => $horraire_dispo[$i] . 'T' . $start,
                                'end' => $horraire_dispo[$i] . 'T' . $end,
                                'title' => $title_dispo,
                                'url' => $url . '&nb_recherche=' . $nb_recherche,
                                'className' => 'dispo',
                                'time_creneaux' => $time_creneaux,
                            );
                            array_push($tab_disponibilite, $disponibilite);
                        }
                        $continue = true;
                        break;
                    } else if (is_numeric($nb_recherche) && empty($value["maximum_number_place_by_reservation"]) && $nb_recherche > $nb_place_max_reservation) {
                        $disponibilite = (object)array(
                            'start' => $horraire_dispo[$i] . 'T' . $start,
                            'end' => $horraire_dispo[$i] . 'T' . $end,
                            'title' => $title_non_autoriser,
                            'color' => 'grey',
                            'time_creneaux' => $time_creneaux,
                        );
                        array_push($tab_disponibilite, $disponibilite);
                        $continue = true;
                        break;
                    }
                    if (!empty($value["maximum_number_place"]) && $value["maximum_number_place"] != $nbPlaceMax) {
                        $nbPlaceMax = $value["maximum_number_place"];
                    }
                }
            }
        }
        if ($continue) {
        } else if (($nbPlaceMax - $reservation_mdl->getNbPlacePrise($horraire_dispo[$i] . ' ' . $start, $horraire_dispo[$i] . ' ' . $end, $id_ressource)) < $nb_recherche) {
            $disponibilite = (object)array(
                'start' => $horraire_dispo[$i] . 'T' . $start,
                'end' => $horraire_dispo[$i] . 'T' . $end,
                'title' => $title_non_dispo,
                'color' => 'grey',
                'time_creneaux' => $time_creneaux,
            );
            array_push($tab_disponibilite, $disponibilite);
        } else if (isset($_SESSION['resa_unco'])) {
            $full = false;
            foreach ($_SESSION['resa_unco'] as $id_resa => $date) {
                if ($date == ($horraire_dispo[$i] . ' ' . $start)) {
                    $title = $reservation_mdl->getNameByIdResa($id_resa);
                    $nb_place = $reservation_mdl->getNbPlaceByIdReservation($id_resa);
                    if (isset($value['maximum_number_place']) && $value['maximum_number_place'] == $nb_place) {
                        $disponibilite = (object)array(
                            'start' => $horraire_dispo[$i] . 'T' . $start,
                            'end' => $horraire_dispo[$i] . 'T' . $end,
                            'title' => $title,
                            'url' => $url . '&nb_recherche=' . $nb_recherche,
                            'className' => 'full',
                            'time_creneaux' => $time_creneaux,
                        );
                        array_push($tab_disponibilite, $disponibilite);
                        $full = true;
                        break;
                    } else {
                        $disponibilite = (object)array(
                            'start' => $horraire_dispo[$i] . 'T' . $start,
                            'end' => $horraire_dispo[$i] . 'T' . $end,
                            'title' => $title,
                            'url' => $url . '&nb_recherche=' . $nb_recherche,
                            'className' => 'occupe',
                            'time_creneaux' => $time_creneaux,
                        );
                        array_push($tab_disponibilite, $disponibilite);
                        break;
                    }
                }
            }
            if (!$full) {
                $disponibilite = (object)array(
                    'start' => $horraire_dispo[$i] . 'T' . $start,
                    'end' => $horraire_dispo[$i] . 'T' . $end,
                    'title' => $title_dispo,
                    'url' => $url . '&nb_recherche=' . $nb_recherche,
                    'className' => 'dispo',
                    'time_creneaux' => $time_creneaux,
                );
                array_push($tab_disponibilite, $disponibilite);
            }
            break;
        } else {
            $disponibilite = (object)array(
                'start' => $horraire_dispo[$i] . 'T' . $start,
                'end' => $horraire_dispo[$i] . 'T' . $end,
                'title' => $title_dispo,
                'url' => $url . '&nb_recherche=' . $nb_recherche,
                'className' => 'dispo',
                'time_creneaux' => $time_creneaux,
            );
            array_push($tab_disponibilite, $disponibilite);
        }
        $nbPlaceMax = $nb_place_max_tmp;
        $resultat = array();
        $resultat['tab_disponibilite'] = $tab_disponibilite;
        $resultat['continue'] = $continue;
        $resultat['nbPlaceMax'] = $nbPlaceMax;
        return $resultat;
    }

    public function ifNonTabOrange($nb_place_max_horaire, $horraire_dispo, $i, $start, $end, $nb_recherche, $title_non_autoriser, $time_creneaux, $continue, $nbPlaceMax, $nb_place_max_reservation, &$tab_disponibilite, $title_dispo, $nb_place_max_tmp, $url)
    {
        $continue = false;
        if (!empty($nb_place_max_horaire)) {
            foreach ($nb_place_max_horaire as $key => $value) {
                if (($horraire_dispo[$i] > $value['start_date'] && $horraire_dispo[$i] < $value['end_date']) || ($horraire_dispo[$i] == $value['start_date'] && $start >= $value['start_hour']) || ($horraire_dispo[$i] == $value['end_date'] && $end <= $value['end_hour'])) {
                    if (is_numeric($nb_recherche) && !empty($value["maximum_number_place_by_reservation"]) && $nb_recherche > $value['maximum_number_place_by_reservation']) {
                        $disponibilite = (object)array(
                            'start' => $horraire_dispo[$i] . 'T' . $start,
                            'end' => $horraire_dispo[$i] . 'T' . $end,
                            'title' => $title_non_autoriser,
                            'color' => 'grey',
                            'time_creneaux' => $time_creneaux,
                        );
                        array_push($tab_disponibilite, $disponibilite);
                        $continue = true;
                        break;
                    } else if (is_numeric($nb_recherche) && empty($value["maximum_number_place_by_reservation"]) && $nb_recherche > $nb_place_max_reservation) {
                        $disponibilite = (object)array(
                            'start' => $horraire_dispo[$i] . 'T' . $start,
                            'end' => $horraire_dispo[$i] . 'T' . $end,
                            'title' => $title_non_autoriser,
                            'color' => 'grey',
                            'time_creneaux' => $time_creneaux,
                        );
                        array_push($tab_disponibilite, $disponibilite);
                        $continue = true;
                        break;
                    }
                    if (!empty($value["maximum_number_place"]) && $value["maximum_number_place"] != $nbPlaceMax) {
                        $nbPlaceMax = $value["maximum_number_place"];
                    }
                }
            }
        }
        if ($continue) {
            continue;
        } else {
            $disponibilite = (object)array(
                'start' => $horraire_dispo[$i] . 'T' . $start,
                'end' => $horraire_dispo[$i] . 'T' . $end,
                'title' => $title_dispo,
                'url' => $url . '&nb_recherche=' . $nb_recherche,
                'className' => 'dispo',
                'time_creneaux' => $time_creneaux,
            );
            array_push($tab_disponibilite, $disponibilite);
        }
        $nbPlaceMax = $nb_place_max_tmp;
        $resultat = array();
        $resultat['tab_disponibilite'] = $tab_disponibilite;
        $resultat['continue'] = $continue;
        $resultat['nbPlaceMax'] = $nbPlaceMax;
        return $resultat;
    }

}
