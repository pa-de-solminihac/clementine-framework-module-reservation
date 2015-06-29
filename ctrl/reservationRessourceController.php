<?php
class reservationRessourceController extends reservationRessourceController_Parent
{
    /**
     *  createAction : Controlleur s'occupant de la création des ressources,
     *                 Pour plus d'explication ce reporté aux docblock de
     *
     *  @access public
     *  @return void
     */
    public function createAction($request, $params = null)
    {
        if ($id_ressource = $request->post('int', 'clementine_reservation_ressource-id')) {
            $params['url_retour'] = __WWW__ . '/reservation/calendar?clementine_reservation_ressource-id=' . $id_ressource;
        }
        $this->getModel('users')->needPrivilege(array(
            'clementine_reservation_gerer_reservation' => true,
        ));
        return parent::createAction($request, $params);
    }

    /**
     *  updateAction : Controlleur s'occupant de la modification des ressources
     *
     *  @access public
     *  @return void
     */
    public function updateAction($request, $params = null)
    {
        $params['url_retour'] = __WWW__ . '/reservation/calendar?clementine_reservation_ressource-id=' . $request->get('int', 'clementine_reservation_ressource-id');
        $this->getModel('users')->needPrivilege(array(
            'clementine_reservation_gerer_reservation' => true,
        ));
        return parent::updateAction($request, $params);
    }

    /**
     *  indexAction : Controlleur s'occupant de l'affichage des ressources sous forme de liste
     *
     *  @access public
     *  @return void
     */
    public function indexAction($request, $params = null)
    {
        $params['url_retour'] = __WWW__ . '/reservation/calendar';
        $this->getModel('users')->needPrivilege(array(
            'clementine_reservation_gerer_reservation' => true,
        ));
        return parent::indexAction($request, $params);
    }

    /**
     *  deleteAction : Controlleur s'occupant de la suppression  des ressources
     *
     *  @access public
     *  @return void
     */
    public function deleteAction($request, $params = null)
    {
        $db = $this->getModel('db');
        $id_ressource = $request->get('int', 'clementine_reservation_ressource-id');
        $sql = "DELETE FROM clementine_reservation_ressource_has_horaire WHERE ressource_id = $id_ressource";
        $db->query($sql);
        $sql = "DELETE FROM clementine_reservation_ressource_has_reservation WHERE ressource_id = $id_ressource";
        $db->query($sql);
        $sql = "DELETE FROM clementine_reservation_ressource WHERE id = $id_ressource";
        $db->query($sql);
        $this->getModel('fonctions')->redirect(__WWW__ . '/reservation/calendar');
    }

    /**
     *  rename_fields : Renomme tous les champs pour qu'il soit plaisant à l'affichage
     *
     *  @access public
     *  @return void
     */
    public function rename_fields($request, $params = null)
    {
        $ret = parent::rename_fields_create_or_update($request, $params);
        if (Clementine::$config['module_fullcalendar']['lang'] == 'fr') {
            $this->mapFieldName('clementine_reservation_ressource.maximum_number_place', 'Nombre de place maximum');
            $this->mapFieldName('clementine_reservation_ressource.maximum_number_place_by_reservation', 'Nombre de place maximum par réservation');
            $this->mapFieldName('clementine_reservation_ressource.time_creneaux', 'Temp par creneaux');
        } else {
            $this->mapFieldName('clementine_reservation_ressource.maximum_number_place', 'Maximum number place');
            $this->mapFieldName('clementine_reservation_ressource.maximum_number_place_by_reservation', 'Maximum number place by reservation');
            $this->mapFieldName('clementine_reservation_ressource.libelle', 'Wording');
            $this->mapFieldName('clementine_reservation_ressource.time_creneaux', 'Time by slot');
        }
        return $ret;
    }

    /**
     *  alter_values : Controlleur s'occupant de la création des ressources
     *
     *  @access public
     *  @return void
     */
    public function alter_values($request, $params = null)
    {
        $ret = parent::alter_values($request, $params);
        $users = $this->getModel('users');
        $auth = $users->getAuth();
        $user = $users->getUserByLogin($auth['login']);
        $id_user_actual = $user['id'];
        $this->setDefaultValue('clementine_reservation_ressource.client_id', $id_user_actual);
        $ressource = $this->getModel('ressource');
        $this->setDefaultValue('clementine_reservation_ressource.id', $ressource->getMaxIdRes() + 1);
        return $ret;
    }

    /**
     *  override_fields_create_or_update : surcharge les champs dans la vue create ou update
     *
     *  @access public
     *  @return void
     */
    public function override_fields_create_or_update($request, $params = null)
    {
        $ret = parent::override_fields_create_or_update($request, $params);
        $this->overrideField('clementine_reservation_ressource.client_id', array(
            'type' => 'hidden'
        ));
        $this->overrideField('clementine_reservation_ressource.id', array(
            'type' => 'hidden',
        ));
        $this->setMandatoryField('clementine_reservation_ressource.maximum_number_place');

        $this->setMandatoryField('clementine_reservation_ressource.libelle');
        $ressource_mdl = $this->getModel('ressource');
        $id_ressource = $request->get('int', 'clementine_reservation_ressource-id');
        if ($ressource_mdl->ressourceHasHoraire($id_ressource) > 0) {
            $this->overrideField('clementine_reservation_ressource.time_creneaux', array(
                'readonly' => 'true',
            ));
        } else {
            $this->setMandatoryField('clementine_reservation_ressource.time_creneaux');
        }
        return $ret;
    }

    /**
     *  hide_fields_index : cache les champs dans la vue index
     *
     *  @access public
     *  @return void
     */
    public function hide_fields_index($request, $params = null)
    {
        $ret = parent::hide_fields_index($request, $params);
        $this->hideField('clementine_reservation_ressource.id');
        $this->hideField('clementine_reservation_ressource.client_id');
        return $ret;
    }

    /**
     *  move_fields : S'occupe de bouger les champs pour les mettres à leur placess
     *
     *  @access public
     *  @return void
     */
    public function move_fields($request, $params = null)
    {
        $ret = parent::move_fields($request, $params);
        $this->moveField('clementine_reservation_ressource.libelle', 'clementine_reservation_ressource.maximum_number_place');
        $this->moveField('clementine_reservation_ressource.maximum_number_place_by_reservation', 'clementine_reservation_ressource.time_creneaux');
        return $ret;
    }

    /**
     *  validate : valide le faites que les nombre de place maximum par réservation
     *
     *  @access public
     *  @return void
     */
    public function validate($insecure_values, $insecure_primary_key = null, $params = null)
    {
        $my_errors = parent::validate($insecure_values, $insecure_primary_key, $params);
        if ($insecure_values['clementine_reservation_ressource-maximum_number_place_by_reservation'] > $insecure_values['clementine_reservation_ressource-maximum_number_place']) {
            $my_errors['number_people'] = "Il doit y avoir moins de nombre de place maximum par réservation que de nombre de place maximum";
        }
        return $my_errors;
    }

}
