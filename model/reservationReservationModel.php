<?php
class reservationReservationModel extends reservationReservationModel_Parent
{
    public $table_reservation = 'clementine_reservation';
    public $table_reservation_users = 'clementine_reservation_users';
    public $table_ressource_has_reservation = 'clementine_reservation_ressource_has_reservation';
    public $table_ressource = 'clementine_reservation_ressource';
    /** 
     * _init : fonction s'occupant de la géneration du crud sur la table Reservation
     *
     * @access public
     * @return void
     */
    public function _init($params = null)
    {
        $this->tables = array(
            $this->table_ressource => '',
            $this->table_ressource_has_reservation => array(
                'inner join' => '`' . $this->table_ressource . '`.`id` = `' . $this->table_ressource_has_reservation . '`.`ressource_id`'
            ) ,
            $this->table_reservation => array(
                'inner join' => '`' . $this->table_ressource_has_reservation . '`.`reservation_id` = `' . $this->table_reservation . '`.`id`',
            ) ,
            $this->table_reservation_users => array(
                'inner join' => '`' . $this->table_reservation . '`.`user_id` = `' . $this->table_reservation_users . '`.`id`',
            ) ,
        );
        $user_mdl = $this->getModel('users');
        $auth = $user_mdl->getAuth();
        $privileges = array(
            'clementine_reservation_gerer_reservation' => true
        );
        if ($auth != false && (!$user_mdl->hasPrivilege($privileges))) {
            if ($this->testUser($auth['id'])) {
                $this->metas['readonly_tables'] = array(
                    $this->table_ressource => '',
                    $this->table_reservation_users => '',
                    $this->table_ressource,
                );
            }
        } else {
            $this->metas['readonly_tables'] = array(
                $this->table_ressource => ''
            );
        }
    }
    /** 
     * getFirstIdRessource : Renvoie le premier id d'une ressource
     *
     * @access public
     * @return void
     */
    public function getFirstIdRessource($params = null)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT id
    FROM clementine_reservation_ressource
    LIMIT 1
SQL;
        $ressource = $db->fetch_assoc($db->query($sql));
        return $ressource['id'];
    }
    /** 
     * getAllInfoReservationById : Renvoie les informations d'une reservation d'une ressource
     *
     * @access public
     * @return void
     */
    public function getAllInfoReservationById($id_reservation)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT *
    FROM clementine_reservation, clementine_reservation_users
    WHERE clementine_reservation.user_id = clementine_reservation_users.id
      AND clementine_reservation.id = $id_reservation
    LIMIT 1
SQL;
        $stmt = $db->query($sql);
        $tab = $db->fetch_all($stmt, MYSQLI_ASSOC);
        return $tab[0];
    }
    /**
     * testUser : Vérifie qu'un utilisateur connecté à une réservation
     *
     * @access public
     * @return bool
     */
    public function testUser($id_user)
    {
        $db = $this->getModel('db');
        $stmt = $db->query('SELECT * FROM ' . $this->table_reservation_users . ' WHERE clementine_users_id = ' . $id_user);
        if ($db->num_rows($stmt) > 0) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * getIdUser : Renvoie l'id_user correspondant à l'id d'un user connecté.
     *
     * @access public
     * @return void
     */
    public function getIdUser($id_user_connecte)
    {
        $db = $this->getModel('db');
        $stmt = $db->query('SELECT id FROM clementine_reservation_users WHERE clementine_users_id = ' . $id_user_connecte);
        $res = $db->fetch_assoc($stmt);
        return $res['id'];
    }
    /**
     * getNameUser : Renvoie le nom et le prénom d'un utilisateur correspondant à l'id d'un user non connecté.
     *
     * @access public
     * @return void
     */
    public function getNameUser($id)
    {
        $db = $this->getModel('db');
        $stmt = $db->query('SELECT name, firstname FROM clementine_reservation_users WHERE id = ' . $id);
        $res = $db->fetch_assoc($stmt);
        return $res['name'] . ' ' . $res['firstname'];
    }
    /**
     * getUrlResa : Renvoie la totalité des urls de reservation d'une ressource, url que l'on va mettre dans le calendrier
     *
     * @access public
     * @return void
     */
    public function getUrlResa($id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT *
    FROM clementine_reservation, clementine_reservation_ressource_has_reservation
    WHERE clementine_reservation.id = clementine_reservation_ressource_has_reservation.reservation_id
      AND clementine_reservation_ressource_has_reservation.ressource_id = $id_ressource
      AND clementine_reservation.cancel = 0
SQL;
        $stmt = $db->query($sql);
        $tab_url_reservation = array();
        while ($res = $db->fetch_assoc($stmt)) {
            list($start_date_reservation, $start_hour_reservation) = explode(' ', $res['start_date']);
            list($end_date_reservation, $end_hour_reservation) = explode(' ', $res['end_date']);
            $tab_url_reservation[$res['start_date'] . '-' . $res['end_date']] = __WWW__ . '/reservation/update?clementine_reservation-id=' . $res['id'] . '&start_date=' . $start_date_reservation . '_' . $start_hour_reservation . '&clementine_reservation_ressource-id=' . $res['ressource_id'];
        }
        return $tab_url_reservation;
    }
    /**
     * getUrlUpdateByHour : Renvoie les urls des reservations entre deux dates données pour une ressource
     *                      des urls de reservation d'une ressource, url que l'on va mettre dans le calendrier
     *
     * @access public
     * @return void
     */
    public function getUrlUpdateByHour($start_date, $end_date, $id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT *
    FROM clementine_reservation, clementine_reservation_ressource_has_reservation
    WHERE clementine_reservation.id = clementine_reservation_ressource_has_reservation.reservation_id
      AND clementine_reservation_ressource_has_reservation.ressource_id = $id_ressource
      AND clementine_reservation.cancel = 0
      AND start_date = "$start_date"
      AND end_date = "$end_date"
SQL;
        $stmt = $db->query($sql);
        $inc = 0;
        $tabUrl = array();
        while ($res = $db->fetch_assoc($stmt)) {
            $tabUrl[$inc] = __WWW__ . '/reservation/update?clementine_reservation-id=' . $res['id'] . '&start_date=' . str_replace(' ', '_', $start_date) . '&clementine_reservation_ressource-id=' . $res['ressource_id'];
            $tabUrl['id_reservation' . $inc] = $res['id'];
            $inc+= 1;
        }
        return $tabUrl;
    }
    /**
     * getNameByIdResa : Renvoie le nom d'un utilisateur non connecté pour la réservation
     *
     * @access public
     * @return void
     */
    public function getNameByIdResa($idResa)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT name, firstname
    FROM clementine_reservation_users, clementine_reservation
    WHERE clementine_reservation.id = $idResa
      AND clementine_reservation_users.id = clementine_reservation.user_id
SQL;
        $stmt = $db->query($sql);
        $res = $db->fetch_assoc($stmt);
        return $res['name'] . ' ' . $res['firstname'];
    }
    /**
     * getTabNbPlaceSup1 : Renvoie le tableau ou les reservation possèdent un nombre de place supérieur ou égale à 1
     *                     mais inférieur aux nombre de place maximum
     *
     * @access public
     * @return void
     */
    public function getTabNbPlaceSup1($id_ressource)
    {
        if ($id_ressource >= 0) {
            $ressourceMdl = $this->getModel('ressource');
            $db = $this->getModel('db');
            $number_place_max = $ressourceMdl->getMaximumNumberPlace($id_ressource);
            $query = <<<SQL
    SELECT start_date, end_date
    FROM clementine_reservation, clementine_reservation_ressource_has_reservation 
    WHERE number_people > 0 
      AND number_people < $number_place_max 
      AND clementine_reservation_ressource_has_reservation.ressource_id = $id_ressource 
      AND clementine_reservation.id = clementine_reservation_ressource_has_reservation.reservation_id 
      AND cancel = 0
SQL;
            $stmt = $db->query($query);
            $tab = array();
            while ($res = $db->fetch_assoc($stmt)) {
                if (isset($tab[$res['start_date'] . '-' . $res['end_date']])) {
                    $nb = $nb + 1;
                } else {
                    $nb = 1;
                }
                $tab[$res['start_date'] . '-' . $res['end_date']] = $nb;
            }
            return $tab;
        }
    }
    /**
     * getNbPlacePrise : Renvoie le nombre de place prise durant un créneaux
     *
     * @access public
     * @return int
     */
    public function getNbPlacePrise($start_date, $end_date, $id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT number_people
    FROM clementine_reservation, clementine_reservation_ressource_has_reservation
    WHERE start_date = "$start_date"
      AND end_date = "$end_date"
      AND clementine_reservation_ressource_has_reservation.ressource_id = $id_ressource
      AND id = reservation_id
      AND cancel = 0 
SQL;
        $stmt = $db->query($sql);
        $number_place_already_taken = 0;
        while ($res = $db->fetch_assoc($stmt)) {
            $number_place_already_taken+= $res['number_people'];
        }
        return $number_place_already_taken;
    }
    /**
     * getNbPlaceByIdReservation : Renvoie le nombre de place prise par une réservation
     *
     * @access public
     * @return int
     */
    public function getNbPlaceByIdReservation($id_reservation)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT number_people
    FROM clementine_reservation
    WHERE clementine_reservation.id = $id_reservation
    LIMIT 1
SQL;
        $stmt = $db->query($sql);
        $res = $db->fetch_assoc($stmt);
        return $res['number_people'];
    }
    /**
     * updateAjax : A pour but de changer la date d'une reservation du à un drag and drop
     *
     * @access public
     * @return void
     */
    public function updateAjax($start_date, $end_date, $idResa)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    UPDATE clementine_reservation
    SET start_date = "$start_date", end_date = "$end_date"
    WHERE id = $idResa
SQL;
        $db->query($sql);
    }
    /**
     * getDateById : Renvoie les dates d'une réservation sa date de départ et sa date de fin
     *
     * @access public
     * @return void
     */
    public function getDateById($id_reservation)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT start_date, end_date
    FROM clementine_reservation
    WHERE id = $id_reservation
    LIMIT 1
SQL;
        $stmt = $db->query($sql);
        $fetch_all = $db->fetch_all($stmt, MYSQLI_ASSOC);
        return $fetch_all[0];
    }
    /**
     * getIdAndDateById : Renvoie toutes les dates des réservations pour une ressource
     *
     * @access public
     * @return void
     */
    public function getIdAndDateById($id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT start_date, end_date
    FROM clementine_reservation, clementine_reservation_ressource_has_reservation, clementine_reservation_ressource
    WHERE clementine_reservation.id = clementine_reservation_ressource_has_reservation.reservation_id
      AND clementine_reservation_ressource_has_reservation.ressource_id = clementine_reservation_ressource.id
      AND clementine_reservation_ressource.id = $id_ressource
      AND cancel = 0
SQL;
        $stmt = $db->query($sql);
        return $db->fetch_all($stmt);
    }
    /**
     * getIdClemByIdResa : renvoie l'id clémentine correspondant à une réservation renvoie 0
     *                     si la réservation n'a pas été faites par une personne connecté renvoie 0
     *
     * @access public
     * @return void
     */
    public function getIdClemByIdResa($id_reservation)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT clementine_users_id
    FROM clementine_reservation_users, clementine_reservation
    WHERE clementine_reservation.user_id =  clementine_reservation_users.id
      AND clementine_reservation.id = $id_reservation
    LIMIT 1
SQL;
        $stmt = $db->query($sql);
        $fetch_all = $db->fetch_all($stmt);
        if (isset($fetch_all[0][0])) {
            return $fetch_all[0][0];
        } else {
            return 0;
        }
    }

    public function getMaxId()
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT max(id)
    FROM clementine_reservation   
SQL;
        $stmt = $db->query($sql);
        $fetch_all = $db->fetch_all($stmt);
        return $fetch_all[0][0];
    }
}
