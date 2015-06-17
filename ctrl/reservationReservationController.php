<?php
class reservationReservationController extends reservationReservationController_Parent
{
    /**
     *  tryAccess : tryAccess permet d'empêcher l'accès à une page dont il n'a pas les droits.
     *  Cette accès sera stopé et il y aura une erreur de type 403
     *
     *  @param $privileges de la forme array('visualiser_stats' => true); pour un seul privileges sinon ce referer à la fonction
     *          needPrivilege du fichier usersUsersModel.php ligne 227
     *  @access public
     *  @return void
     *
     */
    public function tryAccess($privileges)
    {
        $usr_mdl = $this->getModel('users');
        $module_name = $this->getCurrentModule();
        $err = $this->getHelper('errors');
        if (!$usr_mdl->hasPrivilege($privileges)) {
            $err->register_err('failed_privileges', 'login_error_privileges', Clementine::$config['module_site']['login_error_privileges'], $module_name);
        }
        $auth_errors = $err->get($module_name, 'failed_privileges');
        if ($auth_errors) {
            $this->data['errors'] = $auth_errors;
            $this->data['message'] = implode('<br />', $auth_errors);
            header('HTTP/1.0 403 Unauthorized');
            die();
        }
    }
    /**
     *  profilAction : ProfilAction s'occupe de la page profil pour les personnes connecté
     *
     *  @access public
     *  @return void
     *
     */
    public function profilAction($request, $params = null)
    {
        $user = $this->getModel('users');
        $auth = $user->getAuth();
        if ($auth) {
            $reservation_mdl = $this->getModel('reservation');
            $id_user = $user->getUserByLogin($auth['login']);
            $id_user = $id_user['id'];
            $id_user = $reservation_mdl->getIdUser($id_user);
            $params['where'] = 'clementine_reservation.user_id = ' . $id_user;
            return parent::indexAction($request, $params);
        } else {
            header('HTTP/1.0 403 Unauthorized');
            die();
        }
    }
    public function choixplanningAction($request, $params = null)
    {
        $ressource_mdl = $this->getModel('ressource');
        $this->data['list_total_ressource'] = $ressource_mdl->getListRessource();
    }
    /**
     *  rename_fields : Renomme la totalité des champs
     *
     *  @access public
     *  @return void
     *
     */
    public function rename_fields($request, $params = null)
    {
        $ret = parent::rename_fields($request, $params);
        $lang = clementine::$config['module_fullcalendar']['lang'];
        if ($lang == 'fr') {
            $this->mapFieldName('clementine_reservation_ressource_has_reservation.ressource_id', 'Ressource');
            $this->mapFieldName('clementine_reservation_users.name', 'Nom');
            $this->mapFieldName('clementine_reservation_users.firstname', 'Prenom');
            $this->mapFieldName('clementine_reservation.comment', 'Commentaire');
            $this->mapFieldName('clementine_reservation.number_people', 'Nombre de personne');
            $this->mapFieldName('clementine_reservation.start_date', 'Date de début de réservation');
            $this->mapFieldName('clementine_reservation.end_date', 'Date de fin de réservation');
            $this->mapFieldName('clementine_reservation_ressource.libelle', 'Ressource');
            if (Clementine::$config['mail']['send'] >= 0 && $this->data['formtype'] == 'update') {
                $this->mapFieldName('raison', 'Raison de la modification');
            }
        } else {
            $this->mapFieldName('clementine_reservation_ressource_has_reservation.ressource_id', 'Ressource');
            $this->mapFieldName('clementine_reservation_users.name', 'Last name');
            $this->mapFieldName('clementine_reservation_users.firstname', 'First name');
            $this->mapFieldName('clementine_reservation.comment', 'Comment');
            $this->mapFieldName('clementine_reservation.number_people', 'Number of people');
            $this->mapFieldName('clementine_reservation.start_date', 'Date de début de réservation');
            $this->mapFieldName('clementine_reservation.end_date', 'Date de fin de réservation');
            $this->mapFieldName('clementine_reservation_ressource.libelle', 'Ressource');
            $this->mapFieldName('creneaux', 'Battlements');
            if (Clementine::$config['mail']['send'] >= 0 && $this->data['formtype'] == 'update') {
                $this->mapFieldName('raison', 'Reason of Change');
            }
        }
        return $ret;
    }
    /**
     *  deleteAction : Personnalise la suppression des reservation. On met seulement un champ à 1 pour par la suite
     *                 les traiter dans les statistiques
     *
     *  @access public
     *  @return void
     *
     */
    public function deleteAction($request, $params = null)
    {
        $id_reservation = $request->get('int', 'clementine_reservation-id');
        if ($request->POST) {
            if (isset($request->GET['send']) && $request->GET['send'] == "true") {
                $this->data['send'] = true;
            }
            if (clementine::$config['mail']['send'] >= 0) {
                $this->notification($id_reservation, $request->POST['raison']);
            }
            $db = $this->getModel('db');
            $sql = "UPDATE clementine_reservation SET cancel = 1 WHERE id = $id_reservation";
            $db->query($sql);
            header('Location: ' . __WWW__ . '/reservation/calendar');
        }
    }
    /**
     *  notification : Controle le faites que l'on puisse bien envoyé un mail
     *
     *  @access public
     *  @return void
     *
     */
    public function notification($request, $params = null, $id_reservation, $raison = null)
    {
        if (Clementine::$config['mail']['send'] == 0 || (isset($this->data['send']) && $this->data['send'] == true)) {
            $this->mail_modif($id_reservation, $raison);
        }
    }
    /**
     *  mail_modif : Prépare la totalité du mail et l'envoie
     *
     *  @access public
     *  @return void
     *
     */
    public function mail_modif($id_reservation, $raison = null)
    {
        $from = Clementine::$config['mail']['email_exp'];
        $all_info_resa = $this->getModel('reservation')->getAllInfoReservationById($id_reservation);
        $to = $all_info_resa['mail'];
        $mail = 'Votre réservation commençant le' . $all_info_resa['start_date'] . ' finissant le ' . $all_info_resa['end_date'] . ' a été modifié ou supprimé du à la raison suivante : ' . $raison;
        $this->getHelper('mail')->send(array(
            'from' => $from,
            'to' => $to,
            'title' => 'Attention réservation',
            'message_html' => $mail,
        ));
    }
    /**
     *  allAction : allAction s'occupe de la page profil pour les personnes connecté
     *
     *  @access public
     *  @return void
     *
     */
    public function allAction($request, $params = null)
    {
        if ($request->AJAX) {
            $this->data['start_date_load'] = $request->get('string', 'start');
            $this->data['end_date_load'] = $request->get('string', 'end');
        } else {
            $start_date_load = mktime(0, 0, 0, date("m") , date("d") , date("Y"));
            $this->data['start_date_load'] = date('Y-m-d', $start_date_load);
            $end_date_load = mktime(0, 0, 0, date("m") , date("d") + 1, date("Y"));
            $this->data['end_date_load'] = date('Y-m-d', $end_date_load);
        }
        $this->data['id_ressource'] = - 1;
        $ressource_mdl = $this->getModel('ressource');
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');
        $fullcalendar_ctrl = $this->getController('fullcalendarresa');

        $horaire_mdl = $this->getModel('horaire');
        $nb_max = $horaire_mdl->getNumberPlaceMaxHoraireBetweenDate($this->data['start_date_load'], $this->data['end_date_load'], $this->data['id_ressource']);
        $number_max_reserv = $ressource_mdl->getMaximumNumberPlace($this->data['id_ressource']);
        if (!empty($nb_max) && $number_max_reserv != $nb_max) {
            $this->data['nb_max'] = $nb_max;
        } else {
            $this->data['nb_max'] = $number_max_reserv;
        }
        if (isset($_GET['nb_recherche'])) {
            $this->data['nb_recherche'] = $_GET['nb_recherche'];
        } else {
            $this->data['nb_recherche'] = 1;
        }

        $this->data['h_active'] = false;
        $this->data['list_total_id'] = $ressource_mdl->returnAllIdRessource();
        $this->data['all_libelle'] = $ressource_mdl->getToutLibelle();

        $size_tab_ressource = count($this->data['list_total_id']);
        for ($i = 0; $i < $size_tab_ressource; ++$i) {
            $matrice_valeur[$i][0] = $this->data['list_total_id'][$i];
            $matrice_valeur[$i][1] = $this->data['all_libelle'][$i];
            $matrice_valeur[$i][2] = $fullcalendar_mdl->getTotalHorraireResa($this->data['list_total_id'][$i], true, $this->data['h_active'], $this->data['start_date_load'], $this->data['end_date_load']);
            $this->data['plage_horraire_horraire'] = $fullcalendar_mdl->getListCreneauxPossible($this->data['list_total_id'][$i], $this->data['start_date_load'], $this->data['end_date_load'], $matrice_valeur[$i][2]);
            $this->data['plage_horraire'] = $fullcalendar_mdl->getListCreneauxSansResa($this->data['list_total_id'][$i], $this->data['plage_horraire_horraire'], $this->data['start_date_load'], $this->data['end_date_load']);
            $matrice_valeur[$i][3] = $this->data['plage_horraire'];
            $matrice_valeur[$i][5] = $fullcalendar_mdl->getTotalHorraireResa($this->data['list_total_id'][$i], false, $this->data['h_active'], $this->data['start_date_load'], $this->data['end_date_load']);
            $this->data['plage_horraire_util'] = $fullcalendar_ctrl->createCalendarUtilisateur($this->data['plage_horraire'], $this->data['list_total_id'][$i], null, $this->data['start_date_load'], $this->data['end_date_load'], $this->data['plage_horraire_horraire'], $matrice_valeur[$i][5], $this->data['nb_recherche']);

            $matrice_valeur[$i][4] = $this->data['plage_horraire_util'];
        }
        $this->data['list_total_ressource'] = $ressource_mdl->getListRessource();
        $this->data['choix_ress'] = - 1;
        $this->data['matrice_valeur'] = $matrice_valeur;
        $timeline = $fullcalendar_ctrl->createTimeline($this->data['matrice_valeur']);
        $this->data['timeline_ressource'] = $timeline['ressource'];
        $this->data['timeline_resa'] = $timeline['resa'];

    }
    /**
     *  calendarCreation : Initialise la totalité de la page avec le libelle, le nombre de personne etc.
     *
     *  @access public
     *  @return void
     *
     */
    public function calendarCreation($request, $params = null)
    {
        $reservation_mdl = $this->getModel('reservation');
        $horaire_mdl = $this->getModel('horaire');
        $ressource_mdl = $this->getModel('ressource');
        $ressource_existe = $reservation_mdl->getFirstIdRessource();
        if ($ressource_existe == null) {
            $this->data['id_ressource'] = 0;
        } else {
            if ($request->AJAX) {
                $this->data['start_date_load'] = $request->get('string', 'start');
                $this->data['end_date_load'] = $request->get('string', 'end');
                $this->data['h_active'] = $request->get('string', 'h_active');
                $this->data['id_ressource'] = $request->get('int', 'id_ressource');
            } else {
                $start_date_load = mktime(0, 0, 0, date("m") , date("d") , date("Y"));
                $this->data['start_date_load'] = date('Y-m-d', $start_date_load);
                $end_date_load = mktime(0, 0, 0, date("m") , date("d") + 1, date("Y"));
                //mktime(0, 0, 0, date("m")  , date("d")+1, date("Y"));
                $this->data['end_date_load'] = date('Y-m-d', $end_date_load);
                if (isset($_GET['h_active'])) {
                    $this->data['h_active'] = $_GET['h_active'];
                } else {
                    $this->data['h_active'] = false;
                }
                $this->data['id_ressource'] = $reservation_mdl->getFirstIdRessource();
            }
            $horaire_mdl = $this->getModel('horaire');

            $nb_max = $horaire_mdl->getNumberPlaceMaxHoraireBetweenDate($this->data['start_date_load'], $this->data['end_date_load'], $this->data['id_ressource']);
            $number_max_reserv = $ressource_mdl->getMaximumNumberPlace($this->data['id_ressource']);
            if (!empty($nb_max) && $number_max_reserv != $nb_max) {
                $this->data['nb_max'] = $nb_max;
            } else {
                $this->data['nb_max'] = $number_max_reserv;
            }
            if (isset($_GET['nb_recherche'])) {
                $this->data['nb_recherche'] = $_GET['nb_recherche'];
            } else {
                $this->data['nb_recherche'] = 1;
            }
            $users = $this->getModel('users');
            $auth = $users->getAuth();
            $user = $users->getUserByLogin($auth['login']);
            /* Création d'une variable initialisé à 0 qui permettera de savoir si l'utilisateur souhaite l'affichage de toutes ses ressources*/
            $this->data['choix_ress'] = 0;
            /*  Différents tests pour savoir ce que l'utilisateur
                à choisit comme ressources.
                S'il choisit qu'une seul ressources seul $this->data['id_ressource'] prend pour valeur l'id_ressource séléctionné.
                S'il choisit toutes les ressources $this->data['choixRess'] prend pour valeur -1 et il y a donc création $matriceValeur qui prendra tout les parametres ( id_ressource , Libelle et horraires ) de toutes les ressources.
                Sinon prend par defaut la première ressources.

            */
            $fullcalendar_mdl = $this->getModel('fullcalendarresa');
            $fullcalendar_ctrl = $this->getController('fullcalendarresa');
            $ressource_mdl = $this->getModel('ressource');
            if ($request->get('int', 'clementine_reservation_ressource-id') > 0) {
                $this->data['id_ressource'] = $request->GET['clementine_reservation_ressource-id'];
            } else if ($request->get('int', 'clementine_reservation_ressource-id') == - 1) {
                $this->data['id_ressource'] = $request->GET['clementine_reservation_ressource-id'];
                $this->data['list_total_id'] = $ressource_mdl->returnAllIdRessource();
                $this->data['all_libelle'] = $ressource_mdl->getToutLibelle();
                $size_tab_ressource = count($this->data['list_total_id']);
                for ($i = 0; $i < $size_tab_ressource; ++$i) {
                    $matrice_valeur[$i][0] = $this->data['list_total_id'][$i];
                    $matrice_valeur[$i][1] = $this->data['all_libelle'][$i];
                    $matrice_valeur[$i][2] = $fullcalendar_mdl->getTotalHorraireResa($this->data['list_total_id'][$i], true, $this->data['h_active'], $this->data['start_date_load'], $this->data['end_date_load']);
                    $this->data['plage_horraire_horraire'] = $fullcalendar_mdl->getListCreneauxPossible($this->data['list_total_id'][$i], $this->data['start_date_load'], $this->data['end_date_load']);
                    $this->data['plage_horraire'] = $fullcalendar_mdl->getListCreneauxSansResa($this->data['list_total_id'][$i], $this->data['plage_horraire_horraire'], $this->data['start_date_load'], $this->data['end_date_load']);
                    $matrice_valeur[$i][3] = $this->data['plage_horraire'];
                    $matrice_valeur[$i][5] = $fullcalendar_mdl->getTotalHorraireResa($this->data['list_total_id'][$i], false, $this->data['h_active'], $this->data['start_date_load'], $this->data['end_date_load']);
                    $this->data['plage_horraire_util'] = $fullcalendar_ctrl->createCalendarUtilisateur($this->data['plage_horraire'], $this->data['list_total_id'][$i], null, $this->data['start_date_load'], $this->data['end_date_load'], $this->data['plage_horraire_horraire'], $matrice_valeur[$i][5], $this->data['nb_recherche']);
                    $matrice_valeur[$i][4] = $this->data['plage_horraire_util'];
                }
                $this->data['choix_ress'] = - 1;
                $this->data['matrice_valeur'] = $matrice_valeur;
            }
            $this->data['list_horraire_util'] = $fullcalendar_mdl->getTotalHorraireResa($this->data['id_ressource'], false, $this->data['h_active'], $this->data['start_date_load'], $this->data['end_date_load']);
            $this->data['total_horaire_resa'] = $this->data['list_horraire_util'];
            $this->data['plage_horraire_horraire'] = $fullcalendar_mdl->getListCreneauxPossible($this->data['id_ressource'], $this->data['start_date_load'], $this->data['end_date_load'], $this->data['list_horraire_util']);
            $this->data['plage_horraire'] = $fullcalendar_mdl->getListCreneauxSansResa($this->data['id_ressource'], $this->data['plage_horraire_horraire'], $this->data['start_date_load'], $this->data['end_date_load']);
            $this->data['plage_horraire_util'] = $fullcalendar_ctrl->createCalendarUtilisateur($this->data['plage_horraire'], $this->data['id_ressource'], null, $this->data['start_date_load'], $this->data['end_date_load'], $this->data['plage_horraire_horraire'], $this->data['list_horraire_util'], $this->data['nb_recherche']);
            $this->data['list_total_ressource'] = $ressource_mdl->getListRessource();
            $this->data['libelle'] = $ressource_mdl->getLibelle($this->data['id_ressource']);
        }
    }
    /**
     *  calendarAction : Charge la page calendar, Rajoute le css, et bien sur crée le calendrier
     *
     *  @access public
     *  @return void
     */
    public function calendarAction($request, $params = null)
    {
        if ($request->POST) {
            $this->data['id_ressource'] = $request->POST['ressource'];
            header('Location: ' . __WWW__ . '/reservation/calendar?clementine_reservation_ressource-id=' . $request->POST['ressource']);
        } else if ($request->get('int', 'clementine_reservation_ressource-id') > 0) {
            $this->data['id_ressource'] = $request->GET['clementine_reservation_ressource-id'];
        } else if ($request->get('int', 'id_ressource') > 0) {
            $this->data['id_ressource'] = $request->GET['id_ressource'];
        }
        $this->calendarCreation($request, $params);
        return parent::calendarAction($request, $params);
    }
    /**
     *  indexAction : S'occupe de la liste des réservation
     *
     *  @access public
     *  @return void
     */
    public function indexAction($request, $params = null)
    {
        $params['where'] = 'clementine_reservation.cancel = 0';
        if (isset($request->GET['start_date']) && isset($request->GET['end_date'])) {
            $params['where'].= ' AND clementine_reservation.start_date = "' . $request->GET['start_date'] . '" AND clementine_reservation.end_date="' . $request->GET['end_date'] . '"';
        }
        $privileges = array(
            'clementine_reservation_list_reservation' => true
        );
        $this->tryAccess($privileges);
        return parent::indexAction($request, $params);
    }
    /**
     * ressourceInBD : vérifie dans le tableau $$ressource_client si
     * la ressource accedé fais bien partis d'une ressource du client.
     *
     * @param int id_ressource, $ressource_client : array(0 => array('champ_dans_bd' => valeurAssocié))
     * @access public
     * @return bool
     */
    public function ressourceInBD($id_ressource, $ressource_client, $request, $params = null)
    {
        for ($i = 0; $i < count($$ressource_client); ++$i) {
            if (in_array($id_ressource, $$ressource_client[$i])) {
                return true;
            }
        }
        return false;
    }
    /**
     * hide_fields_index : Cache les champs dans la vue index
     *
     * @access public
     * @return bool
     */
    public function hide_fields_index($request, $params = null)
    {
        $ret = parent::hide_fields_index($request, $params);
        $this->hideField('clementine_reservation.cancel');
        $this->hideField('clementine_reservation.information_id');
        $this->hideField('clementine_reservation.user_id');
        $this->hideField('clementine_reservation_users.clementine_users_id');
        if ($this->data['formtype'] != 'update') {
            $this->unhideField('clementine_reservation_users.name');
            $this->unhideField('clementine_reservation_users.firstname');
            $this->unhideField('clementine_reservation_users.mail');
        }
        $this->unhideField('clementine_reservation.start_date');
        $this->unhideField('clementine_reservation.end_date');
        $this->unhideField('clementine_reservation_ressource.libelle');
        return $ret;
    }
    /**
     * override_url : Surcharge l'url de différents buttons
     *
     * @access public
     * @return bool
     */
    public function override_url($request, $params = null)
    {
        $ret = parent::override_url($request, $params);
        $this->overrideUrlButton('updatebutton', __WWW__ . '/reservation/update?');
        $this->overrideUrlButton('back', __WWW__ . '/reservation/calendar');
        $this->overrideUrlButton('create', __WWW__ . '/reservation/calendar');
        $this->overrideUrlRow(__WWW__ . '/reservation/update?');
        if (isset($request->GET['is_iframe']) && $request->ACT == 'update') {
            $this->overrideUrlButton('del', __WWW__ . '/reservation/delete?clementine_reservation_ressource-id=' . $request->GET['clementine_reservation_ressource-id'] . '&clementine_reservation-id=' . $request->GET['clementine_reservation-id'] . '&is_iframe=1');
        }
        return $ret;
    }
    /**
     * createAction : S'occupe de toute la partie création de réservation
     *
     * @access public
     * @return bool
     */
    public function createAction($request, $params = null)
    {
        $this->data['id_ressource_create'] = $request->get('int', 'clementine_reservation_ressource-id');
        $fullcalendar_ctrl = $this->getController('fullcalendarresa');
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');
        $id_ressource = $request->GET['clementine_reservation_ressource-id'];
        list($day, $hour) = explode('_', $request->GET['start_date']);
        list($year, $month, $days) = explode('-', $day);
        $next_day = date("Y-m-d", mktime(0, 0, 0, $month, $days + 1, $year));
        $this->data['list_creneaux'] = $fullcalendar_ctrl->getListCreneauxParJour($day, $fullcalendar_mdl->getListCreneauxPossible($id_ressource, $day, $next_day, $fullcalendar_mdl->getTotalHorraireResa($id_ressource, false, false, $day, $next_day)));
        return parent::createAction($request, $params);
    }
    /**
     * mailAction : Une page appelé par une requete AJAX, pour savoir si l'on doit envoyé un mail ou non
     *
     * @access public
     * @return bool
     */
    public function mailAction($request, $params = null)
    {
        $this->data['send'] = false;
        if ($request->POST['ok'] == true) {
            $this->data['send'] = true;
        }
    }
    /**
     * updateAction : S'occupe de la vue update, pour plus d'explication se reporter aux docblock de crud
     *
     * @access public
     * @return bool
     */
    public function updateAction($request, $params = null)
    {
        $params['force_default_value'] = 1;
        $params['url_retour'] = __WWW__ . '/reservation/calendar?clementine_reservation_ressource-id=' . $request->get('int', 'clementine_reservation_ressource-id');

        if (isset($request->GET['start_date'])) {
            $this->data['id_ressource_create'] = $request->get('int', 'clementine_reservation_ressource-id');
            $this->data['id_reservation'] = $request->get('int', 'clementine_reservation-id');
            $start_date = $request->get('string', 'start_date');
            list($annee, $mois, $jour) = explode('-', $start_date);
            list($jour, $heur) = explode('_', $jour);
            $jours = date("Y-m-d", mktime(0, 0, 0, $mois, $jour, $annee));
            $end_date = date("Y-m-d", mktime(0, 0, 0, $mois, $jour + 1, $annee));
            list($date, $heure) = explode('_', $start_date);
            $fullcalendar_mdl = $this->getModel('fullcalendarresa');
            $fullcalendar_ctrl = $this->getController('fullcalendarresa');
            $horr = $fullcalendar_ctrl->getListCreneauxParJour($date, $fullcalendar_mdl->getListCreneauxPossible($this->data['id_ressource_create'], $jours, $end_date, $fullcalendar_mdl->getTotalHorraireResa($this->data['id_ressource_create'], false, false, $jours, $end_date)));
        } else {
            $this->data['id_ressource_create'] = $request->get('int', 'clementine_reservation_ressource-id');
            $this->data['id_reservation'] = $request->get('int', 'clementine_reservation_ressource_has_reservation-reservation_id');
        }
        return parent::updateAction($request, $params);
    }
    /**
     * hide_fields : cache les champs désirés
     *
     * @access public
     * @return bool
     */
    public function hide_fields($request, $params = null)
    {
        $ret = parent::hide_fields($request, $params);
        $this->hideField('clementine_reservation_ressource_has_reservation.primary');
        $this->unhideField('clementine_reservation.user_id');
        $this->hideField('clementine_reservation.start_date');
        $this->hideField('clementine_reservation.end_date');
        $this->hideField('clementine_reservation.information_id');
        return $ret;
    }
    /**
     * move_fields : Met les champs à leur places voulus
     *
     * @access public
     * @return bool
     */
    public function move_fields($request, $params = null)
    {
        $ret = parent::move_fields($request, $params);
        $this->moveField('clementine_reservation_users.name', 'clementine_reservation.comment');
        $this->moveField('clementine_reservation_users.firstname', 'clementine_reservation.comment');
        $this->moveField('clementine_reservation_users.mail', 'clementine_reservation.comment');
        return $ret;
    }
    /**
     * alter_values_create_or_update : Change différentes valeur à l'affichage, pour plus d'explication ce reportée
     *                                 aux docs block de crud
     *
     * @access public
     * @return bool
     */
    public function alter_values_create_or_update($request, $params = null)
    {
        $ret = parent::alter_values_create_or_update($request, $params);
        if ($this->data['formtype'] == 'create') {
            $nb_recherche = $request->get('int', 'nb_recherche');
            $this->setDefaultValue('clementine_reservation.number_people', $nb_recherche);
        }
        return $ret;
    }
    /**
     * add_fields_create_or_update : Ajoute différents champs dans les vues create or update de reservation
     *
     * @access public
     * @return bool
     */
    public function add_fields_create_or_update($request, $params = null)
    {
        $this->data['other_value'] = $this->_crud->get($params['get']);
        $ret = parent::add_fields_create_or_update($request, $params);
        $fullcalendar_ctrl = $this->getController('fullcalendarresa');
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');
        $ressource_mdl = $this->getModel('ressource');
        $horaire_mdl = $this->getModel('horaire');
        $id_ressource = 0;
        if (isset($request->GET['start_date'])) {
            $start_date = $request->get('string', 'start_date');
            $id_ressource = $request->get('int', 'ressource_id_ressource');
        } elseif (isset($request->GET['clementine_reservation-id'])) {
            $id_reservation = $request->GET['clementine_reservation-id'];
            foreach ($this->data['other_value'] as $key => $value) {
                list($ressource_id, $ressource_has_reservation_reservation_id, $id_ressource, $id_reservation2, $user_id) = explode('&', $key);
                list($name, $id_reservation2) = explode('=', $id_reservation2);
                if ($id_reservation2 == $id_reservation) {
                    $start_date = $value['clementine_reservation.start_date'];
                    list($day, $hour) = explode(' ', $start_date);
                    $start_date = $day . '_' . $hour;
                    list($name, $id_ressource) = explode('=', $ressource_id);
                    $this->data['id_ressource_create'] = $id_ressource;
                }
            }
            $this->data['id_reservation'] = $id_reservation;
        }
        if (empty($id_ressource)) {
            $id_ressource = $request->get('int', 'clementine_reservation_ressource-id');
        }
        $nb_recherche = $request->get('int', 'nb_recherche');
        $fullcalendar_helper = $this->getHelper('fullcalendarresa');
        list($day, $hour) = explode('_', $start_date);
        list($year, $month, $days) = explode('-', $day);
        $next_day = date("Y-m-d", mktime(0, 0, 0, $month, $days + 1, $year));
        $horr = $fullcalendar_mdl->getListCreneauxPossible($this->data['id_ressource_create'], $day, $next_day, $fullcalendar_mdl->getTotalHorraireResa($this->data['id_ressource_create'], false, false, $day, $next_day));
        $tab = $fullcalendar_mdl->getListCreneauxSansResa($this->data['id_ressource_create'], $horr, $day, $next_day);
        $tab_hor = $fullcalendar_ctrl->getListCreneauxParJour($day, $tab);
        $creneaux = $ressource_mdl->getCreneaux($this->data['id_ressource_create']);
        $end_date = $fullcalendar_helper->secondToTime($fullcalendar_helper->timeToSecond($hour) + $fullcalendar_helper->timeToSecond($creneaux));
        $total = $fullcalendar_ctrl->getListCreneauxParJour($day, $fullcalendar_mdl->getListCreneauxPossible($this->data['id_ressource_create'], $day, $next_day, $fullcalendar_mdl->getTotalHorraireResa($this->data['id_ressource_create'], false, false, $day, $next_day)));
        $user = $this->getModel('users');
        $privileges = array(
            'clementine_reservation_gerer_reservation' => true
        );
        $admin = $user->hasPrivilege($privileges);
        if ($admin) {
            $tabHor = $fullcalendar_ctrl->getListCreneauxParJour($day, $fullcalendar_mdl->getListCreneauxPossible($this->data['id_ressource_create'], $day, $next_day, $fullcalendar_mdl->getTotalHorraireResa($this->data['id_ressource_create'], false, false, $day, $next_day)));
        }
        $tab_crea = array();
        $ind = 0;
        $selected = 0;
        $total = array_unique($total, SORT_REGULAR);
        $reservation_mdl = $this->getModel('reservation');
        $nbPlaceMax = $ressource_mdl->getMaximumNumberPlace($this->data['id_ressource_create']);

        $nb_place_max_horaire = $horaire_mdl->getAllNumberPlaceHoraireBetweenDate($day, $next_day, $id_ressource);
        if ($this->data['formtype'] == 'update') {
            $total = array_values(array_filter($total));
            $incr = 0;
            foreach ($total as $tab_total) {

                if (strpos($tab_total, '-') == false) {
                    $total[$incr] = '';
                }
                ++$incr;

            }
            sort($total, SORT_REGULAR);
            $total = array_values(array_filter($total));
            $tab_crea = $total;
            foreach ($tab_crea as $t) {
                list($start_hour, $end_hour) = explode('-', $t);
                if ($fullcalendar_helper->timeToSecond($start_hour) == $fullcalendar_helper->timeToSecond($hour)) {
                    $selected = $ind;
                }
                ++$ind;
            }
        } else {
            $total = array_values(array_filter($total));
            $incr = 0;
            foreach ($total as $tab_total) {

                if (strpos($tab_total, '-') == false) {
                    $total[$incr] = '';
                }
                ++$incr;

            }
            sort($total, SORT_REGULAR);
            $total = array_values(array_filter($total));
            foreach ($total as $t) {
                $ok = false;
                list($start_hour, $end_hour) = explode('-', $t);
                if (!empty($nb_place_max_horaire)) {
                    foreach ($nb_place_max_horaire as $key => $value) {
                        if ($start_hour >= $value['start_hour'] || $start_hour <= $value['end_hour']) {
                            if (!empty($value["maximum_number_place"]) && $value["maximum_number_place"] != $nbPlaceMax) {
                                $nbPlaceMax = $value["maximum_number_place"];
                            }
                        }
                    }
                }
                if (($nbPlaceMax - $reservation_mdl->getNbPlacePrise($day . ' ' . $start_hour, $day . ' ' . $end_hour, $this->data['id_ressource_create'])) < $nb_recherche) {
                    $tab_crea[$ind]['text'] = $t . '  (non dispo)';
                    $tab_crea[$ind]['disabled'] = 'true';
                    ++$ind;
                } else {
                    foreach ($tab_hor as $th) {
                        if ($th == $t) {
                            $tab_crea[$ind]['text'] = $t;
                            $ok = true;
                        }
                    }
                    if ($ok == false) {
                        $tab_crea[$ind]['text'] = $t . '  (non dispo)';
                        $tab_crea[$ind]['disabled'] = 'true';
                    }
                    if ($fullcalendar_helper->timeToSecond($start_hour) == $fullcalendar_helper->timeToSecond($hour)) {
                        $selected = $ind;
                    }
                    ++$ind;
                }
            }
        }
        $this->addField('creneaux', null, null, array(
            'type' => 'select',
            'fieldvalues' => $tab_crea,
            'default_value' => $selected
        ));
        if ($this->data['formtype'] == 'update' && Clementine::$config['mail']['send'] >= 0) {
            $this->addField('raison');
        }
        return $ret;
    }
    /**
     * override_fields_create_or_update : Surcharge les champs dans les vues create or update,
     *                                    Plus d'explication dans le docBlock de crud
     *
     * @access public
     * @return bool
     */
    public function override_fields_create_or_update($request, $params = null)
    {
        $ret = parent::override_fields_create_or_update($request, $params);
        $this->overrideField('clementine_reservation_ressource_has_reservation.ressource_id', array(
            'type' => 'hidden'
        ));
        $this->overrideField('clementine_reservation.cancel', array(
            'type' => 'hidden'
        ));
        $this->overrideField('clementine_reservation.user_id', array(
            'type' => 'hidden'
        ));
        $this->overrideField('clementine_reservation_users.clementine_users_id', array(
            'type' => 'hidden'
        ));
        $this->overrideField('clementine_reservation.start_date', array(
            'type' => 'hidden',
            'readonly' => 'true',
        ));
        $this->overrideField('clementine_reservation.end_date', array(
            'type' => 'hidden',
            'readonly' => 'true',
        ));
        /*
                Création d'un champ crénaux pour simplifier le changement d'horaire
                pour l'utilisateur.
        */
        $this->overrideField('creneaux', array(
            'type' => 'select',
        ));
        if ($this->data['formtype'] == 'update' && Clementine::$config['mail']['send'] >= 0) {
            $this->overrideField('raison', array(
                'type' => 'textarea',
            ));
        }

        $this->setMandatoryField('clementine_reservation.number_people');
        /*
                Vérifie si l'utilisateur est connécté ou non.
                Et vérifie si il à rentré au moins une fois sont ( name, prenom..)
                Si oui cache name , préname , mail.
        */
        $this->overrideField('clementine_reservation_users.mail', array(
            'type' => 'email'
        ));
        $user = $this->getModel('users');
        $auth = $user->getAuth();
        $creation = $this->getModel('reservation');
        $privileges = array(
            'clementine_reservation_gerer_reservation' => true
        );
        $admin = $user->hasPrivilege($privileges);
        if ($auth != false && !$admin) {
            if ($creation->testUser($auth['id'])) {
                $this->overrideField('clementine_reservation_users.name', array(
                    'type' => 'hidden'
                ));
                $this->overrideField('clementine_reservation_users.firstname', array(
                    'type' => 'hidden'
                ));
                $this->overrideField('clementine_reservation_users.mail', array(
                    'type' => 'hidden'
                ));
            }
        } else {
            $this->setMandatoryField('clementine_reservation_users.name');
            $this->setMandatoryField('clementine_reservation_users.firstname');
            $this->setMandatoryField('clementine_reservation_users.mail');
        }
        return $ret;
    }
    /**
     *  alter_post : fonction permettant de convertir le créneaux séléctioné
     *               en start_date et end_date attendu pour l'insertion dans la base de données.
     *
     *  @access public
     *  @return void
     */
    public function alter_post($insecure_array, $params = null)
    {
        $insecure_array = parent::alter_post($insecure_array, $params);
        $request = $this->getRequest();
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');
        $fullcalendar_helper = $this->getHelper('fullcalendarresa');
        $fullcalendar_ctrl = $this->getController('fullcalendarresa');
        $start_date;
        $id_ressource;
        if (isset($request->GET['clementine_reservation-id'])) {
            $id_reservation = $request->get('int', 'clementine_reservation-id');
            foreach ($this->data['other_value'] as $key => $value) {
                list($ressource_id, $ressource_has_reservation_reservation_id, $id_ressource, $id_reservation2, $user_id) = explode('&', $key);
                list($name, $id_reservation2) = explode('=', $id_reservation2);
                if ($id_reservation2 == $id_reservation) {
                    $start_date = $value['clementine_reservation.start_date'];
                    list($day, $hour) = explode(' ', $start_date);
                    $start_date = $day . '_' . $hour;
                    list($name, $id_ressource) = explode('=', $ressource_id);
                    $this->data['id_ressource_create'] = $id_ressource;
                }
            }
            $this->data['id_reservation'] = $id_reservation;
        }
        if (!isset($start_date)) {
            $start_date = $request->GET['start_date'];
            $this->data['id_reservation'] = $request->GET['clementine_reservation_ressource-id'];
        }
        list($day, $hour) = explode('_', $start_date);
        list($year, $month, $days) = explode('-', $day);
        $next_day = date("Y-m-d", mktime(0, 0, 0, $month, $days + 1, $year));
        $horr = $fullcalendar_mdl->getListCreneauxPossible($this->data['id_ressource_create'], $day, $next_day, $fullcalendar_mdl->getTotalHorraireResa($this->data['id_ressource_create'], false, false, $day, $next_day));
        $tab = $fullcalendar_mdl->getListCreneauxSansResa($this->data['id_ressource_create'], $horr, $day, $next_day);
        $result = $request->POST['creneaux'];
        $tab_hor = $fullcalendar_ctrl->getListCreneauxParJour($day, $horr);
        $tab_hor = array_values(array_filter($tab_hor));
        $incr = 0;
        foreach ($tab_hor as $tab_total) {
            if (strpos($tab_total, '-') == false) {
                $tab_hor[$incr] = '';
            }
            ++$incr;
        }
        sort($tab_hor, SORT_REGULAR);
        $tab_hor = array_values(array_filter($tab_hor));
        $user = $this->getModel('users');
        $privileges = array(
            'clementine_reservation_gerer_reservation' => true
        );
        $ressource_mdl = $this->getModel('ressource');
        $creneaux = $ressource_mdl->getCreneaux($this->data['id_ressource_create']);
        $end_date = $fullcalendar_helper->secondToTime($fullcalendar_helper->timeToSecond($hour) + $fullcalendar_helper->timeToSecond($creneaux));
        $admin = $user->hasPrivilege($privileges);

        list($start_hour, $end_hour) = explode('-', $tab_hor[$result]);
        $start_date = $day . ' ' . trim($start_hour);
        $end_date = $day . ' ' . $end_hour;
        $insecure_array['clementine_reservation-start_date'] = $start_date;
        $insecure_array['clementine_reservation-end_date'] = $end_date;
        $insecure_array['clementine_reservation-cancel'] = 0;
        $insecure_array['clementine_reservation_ressource_has_reservation-ressource_id'] = $this->data['id_reservation'];
        $user = $this->getModel('users');
        $auth = $user->getAuth();
        $creation = $this->getModel('reservation');
        $privileges = array(
            'clementine_reservation_gerer_reservation' => true
        );
        $admin = $user->hasPrivilege($privileges);
        if ($auth != false && !$admin) {
            if ($creation->testUser($auth['id'])) {
                $insecure_array['clementine_reservation-user_id'] = $creation->getIdUser($auth['id']);
            } else {
                $insecure_array['clementine_reservation_users-clementine_users_id'] = $auth['id'];
            }
        }

        if ($this->data['formtype'] == 'update' && Clementine::$config['mail']['send'] >= 0) {
            $this->notification($this->data['id_reservation'], $insecure_array['raison']);
        }
        return $insecure_array;
    }
    /**
     *  validate :  Fonction qui vérifie si le créneaux séléctioné est bien disponible
     *              et si le nombre NbPersonne n'est pas supérieur au nombre de places réstantes
     *              sur le crénaux séléctioné.
     *
     *  @access public
     *  @return void
     */
    public function validate($insecure_values, $insecure_primary_key = null, $params = null)
    {
        $my_errors = parent::validate($insecure_values, $insecure_primary_key, $params);
        list($year, $month, $days) = explode('-', $insecure_values['clementine_reservation-start_date']);
        list($days, $other) = explode(' ', $days);
        $day = date("Y-m-d", mktime(0, 0, 0, $month, $days, $year));
        $next_day = date("Y-m-d", mktime(0, 0, 0, $month, $days + 1, $year));
        $fullcalendar_mdl = $this->getModel('fullcalendarresa');
        $fullcalendar_ctrl = $this->getController('fullcalendarresa');
        $reservation_mdl = $this->getModel('reservation');
        $ressource_mdl = $this->getModel('ressource');
        $horaire_mdl = $this->getModel('horaire');
        $id_ressource = $this->data['id_ressource_create'];
        $request = $this->getRequest();
        $number_place_max_reserv = $ressource_mdl->getNbPlaceMax($id_ressource);
        $number_place_max_horaire = $horaire_mdl->getNumberPlaceMaxHoraire($day, $other);
        if (!empty($number_place_max_horaire) && $number_place_max_horaire != $number_place_max_reserv) {
            $number_place_max_reserv = $number_place_max_horaire;
        }

        $list_horaire_util = $fullcalendar_mdl->getTotalHorraireResa($id_ressource, false, false, $day, $next_day);
        $plage_horaire_horaire = $fullcalendar_mdl->getListCreneauxPossible($id_ressource, $day, $next_day, $list_horaire_util);
        $plage_horraire = $fullcalendar_mdl->getListCreneauxSansResa($id_ressource, $plage_horaire_horaire, $day, $next_day);
        $request = $this->getRequest();
        $nb_recherche = $request->get('int', 'nb_recherche');
        $plage_horraire_util = $fullcalendar_ctrl->createCalendarUtilisateur($plage_horraire, $id_ressource, null, $day, $next_day, $plage_horaire_horaire, $list_horaire_util, $nb_recherche);
        $number_place_take = $reservation_mdl->getNbPlaceprise($insecure_values['clementine_reservation-start_date'], $insecure_values['clementine_reservation-end_date'], $id_ressource);
        $verif_possible_creneaux = $fullcalendar_ctrl->verifDatePossible($insecure_values['clementine_reservation-start_date'], $insecure_values['clementine_reservation-end_date'], $plage_horraire_util);

        $number_place_max = $ressource_mdl->getMaximumNumberPlace($id_ressource);
        $number_place_remainder = $number_place_max - $number_place_take;
        $number_place_take_by_reservation = $reservation_mdl->getNbPlaceByIdReservation($request->get('int', 'clementine_reservation-id'));
        if ($insecure_values['clementine_reservation-number_people'] < 0) {
            $my_errors['number_people'] = 'Impossible d\'avoir un nombre de personne inférieur à 0';
        } else {
            if ($number_place_take == $number_place_take_by_reservation) {
                if ($insecure_values['clementine_reservation-number_people'] > $number_place_max_reserv) {
                    $my_errors['number_people'] = 'Vous ne pouvez réservez que ' . $number_place_max_reserv . ' places pour ce créneaux';
                } else if ($insecure_values['clementine_reservation-number_people'] > $number_place_max) {
                    $my_errors['number_people'] = 'Il ne reste plus que ' . $number_place_max . ' places pour ce créneaux';
                }
            } else {
                if ($insecure_values['clementine_reservation-number_people'] > $number_place_max_reserv) {
                    $my_errors['number_people'] = 'Vous ne pouvez réservez que ' . $number_place_max_reserv . ' places pour ce créneaux';
                } else if ($insecure_values['clementine_reservation-number_people'] > ($number_place_remainder + $number_place_take_by_reservation)) {
                    $my_errors['number_people'] = 'Il ne reste plus que ' . $number_place_remainder . ' places pour ce créneaux';
                }
            }
        }
        if ($verif_possible_creneaux == false) {
            $my_errors['start_date'] = 'Le créneaux séléctioné n\'est pas disponible';
        }
        return $my_errors;
    }
    /**
     * blockAction : controlleur permettant de bloquer une réservation
     *
     * @access public
     * @return bool
     */
    public function blockAction($request, $params = null)
    {
        if ($request->POST) {
            $clementine_reservation_ressource_id = $request->GET['clementine_reservation_ressource-id'];
            $start_date = $request->GET['start_date'];
            $commentaire = $request->POST['commentaire'];
            $horaire_mdl = $this->getModel('horaire');
            $horaire_mdl->createHoraireSuppr($clementine_reservation_ressource_id, $start_date, $commentaire);
            header('Location: ' . __WWW__ . '/reservation/calendar');
        }
    }
    /**
     * numberpeopleAction : Est appelé par une requête AJAX et traite les données envoyé par l'input du nombre de personne
     *
     * @access public
     * @return bool
     */
    public function numberpeopleAction($request, $params = null)
    {
        if ($request->POST) {
            $start_date = $request->POST['start_date'];
            $test_date = new DateTime($request->POST['end_date']);
            $end_date = date('Y-m-d', date_timestamp_get($test_date));
            $id_ressource = $request->POST['id_ressource'];

            $horaire_mdl = $this->getModel('horaire');
            $ressource_mdl = $this->getModel('ressource');
            $fullcalendar_mdl = $this->getModel('fullcalendarresa');
            $number_max = 0;
            $number_max = $horaire_mdl->getNumberPlaceMaxHoraireBetweenDate($start_date, $end_date, $id_ressource);
            $number_max_reserv = $ressource_mdl->getNbPlaceMax($id_ressource);

            if (!empty($number_max) && $number_max != $number_max_reserv) {
                $number_max_reserv = $number_max;
            }
            $this->data['number_max'] = $number_max_reserv;
        }
    }
    /**
     * choixAction : Controlleur permettant de choisir entre une réservation connecté et une non connecté
     *               N'est appelé uniquement si clementine::$config['module_reservation']['force'] == 0
     *
     * @access public
     * @return bool
     */
    public function choixAction($request, $params = null)
    {

    }
    public function calendar_cssAction($request, $params = null)
    {

    }
}
