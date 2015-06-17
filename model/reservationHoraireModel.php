<?php
class reservationHoraireModel extends reservationHoraireModel_Parent
{
    public $table_reservation_ressource = 'clementine_reservation_ressource';
    public $table_reservation_ressource_has_horaire = 'clementine_reservation_ressource_has_horaire';
    public $table_reservation_horaire = 'clementine_reservation_horaire';
    public $table_horaire_has_option = 'clementine_reservation_horaire_has_option';
    /** 
     * _init : fonction s'occupant de la géneration du crud sur la table Reservation
     *
     * @access public
     * @return void
     */
    public function _init($params = null)
    {
        $this->tables = array(
            $this->table_reservation_ressource => '',
            $this->table_reservation_ressource_has_horaire => array(
                'inner join' => '`' . $this->table_reservation_ressource . '`.`id` = `' . $this->table_reservation_ressource_has_horaire . '`.`ressource_id`',
            ) ,
            $this->table_reservation_horaire => array(
                'inner join' => '`' . $this->table_reservation_horaire . '`.`id` = `' . $this->table_reservation_ressource_has_horaire . '`.`horaire_id`'
            ) ,
        );
        $this->metas['readonly_tables'] = array(
            $this->table_reservation_ressource => '',
        );
    }
    /**
     * getMinHoraire : Renvoie l'horaire minimum pour une ressource
     *
     * @access public
     * @return void
     */
    public function getMinHoraire($id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT MIN(start_hour) as sh
    FROM clementine_reservation_horaire, clementine_reservation_ressource_has_horaire
    WHERE clementine_reservation_horaire.id = horaire_id
      AND clementine_reservation_ressource_has_horaire.ressource_id = $id_ressource
SQL;
        $stmt = $db->query($sql);
        $res = $db->fetch_assoc($stmt);
        return $res['sh'];
    }
    /**
     *  getMaxIdHoraire : On s'en sert pour prévoir le futur id d'horaire créé ou avoir le dernier horaire créée..
     *
     *  @access public
     *  @return max_id renvoie l'id maximum de la base
     */
    public function getMaxIdHoraire()
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT `AUTO_INCREMENT`
    FROM  INFORMATION_SCHEMA.TABLES
    WHERE TABLE_SCHEMA = 'quai13'
    AND   TABLE_NAME   = 'clementine_reservation_horaire';
SQL;
        $stmt = $db->query($sql);
        $res = $db->fetch_assoc($stmt);
        return $res['AUTO_INCREMENT'];
    }
    /**
     *  getMaxHoraire : Renvoie l'horaire maximum pour une ressource donnée pour pouvoir tronqué le calendrier
     *
     *  @access public
     *  @return void
     */
    public function getMaxHoraire($id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT MAX(end_hour) as eh
    FROM clementine_reservation_horaire, clementine_reservation_ressource_has_horaire
    WHERE clementine_reservation_horaire.id = horaire_id
      AND clementine_reservation_ressource_has_horaire.ressource_id = $id_ressource
SQL;
        $stmt = $db->query($sql);
        $res = $db->fetch_assoc($stmt);
        return $res['eh'];
    }
    /**
     *  createHoraireSuppr : Créée une horaire suppression dans la base lorsqu'un créneaux est bloqué.
     *
     *  @access public
     *  @return void
     */
    public function createHoraireSuppr($id_ressource, $start_date, $commentaire)
    {
        $db = $this->getModel('db');
        $fullcalendar_helper = $this->getHelper('fullcalendarresa');
        $ressource_mdl = $this->getModel('ressource');
        list($start_date, $start_hour) = explode('_', $start_date);
        $end_hour = $fullcalendar_helper->secondToTime($fullcalendar_helper->timeToSecond($start_hour) + $fullcalendar_helper->timeToSecond($ressource_mdl->getCreneaux($id_ressource)));
        $sql = <<<SQL
    INSERT INTO clementine_reservation_horaire (id, start_date, start_hour, end_hour, end_date, to_add, comment)
    VALUES (DEFAULT, "$start_date", "$start_hour", "$end_hour", "$start_date", -1, "$commentaire");
SQL;
        $db->query($sql);
        $last_id_horaire = $this->getMaxIdHoraire();
        $sql = <<<SQL
    INSERT INTO clementine_reservation_ressource_has_horaire (ressource_id, horaire_id)
    VALUES ($id_ressource, $last_id_horaire)
SQL;
        $db->query($sql);
    }
    /**
     *  getDateById : Renvoie la date d'un horaire par un id donnée.
     *
     *  @access public
     *  @return void
     */
    public function getDateById($id_horaire)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT start_hour, start_date, end_date, end_hour
    FROM clementine_reservation_horaire
    WHERE clementine_reservation_horaire.id = $id_horaire
    LIMIT 1
SQL;
        $stmt = $db->query($sql);
        $fetch_all = $db->fetch_all($stmt, MYSQLI_ASSOC);
        return $fetch_all[0];
    }
    /**
     *  getNumberPlaceMaxHoraire : Renvoie le nombre de place maximum pour un jour donnée à une heure donnée.
     *
     *  @access public
     *  @return void
     */
    public function getNumberPlaceMaxHoraire($day, $hour)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT maximum_number_place_by_reservation, maximum_number_place
    FROM clementine_reservation_horaire
    WHERE start_date <= "$day" AND "$day" <= end_date AND "$hour" >= start_hour AND "$hour" <= end_hour
    LIMIT 1  
SQL;
        $stmt = $db->query($sql);
        if ($db->num_rows($stmt) > 0) {
            $res = $db->fetch_all($stmt, MYSQLI_ASSOC);
            return $res[0];
        }
        return 0;
    }
    /**
     *  getNumberPlaceMaxHoraireBetweenDate : Renvoie le nombre de place maximum entre deux dates pour une ressource donnée.
     *
     *  @access public
     *  @return void
     */
    public function getNumberPlaceMaxHoraireBetweenDate($start_date, $end_date, $id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT max(maximum_number_place_by_reservation) as mx_number_place
    FROM clementine_reservation_horaire, clementine_reservation_ressource_has_horaire
    WHERE clementine_reservation_horaire.id = clementine_reservation_ressource_has_horaire.horaire_id
      AND clementine_reservation_ressource_has_horaire.ressource_id = $id_ressource
      AND (("$start_date" <= start_date AND "$end_date" >= start_date) 
       OR ("$start_date" <= end_date AND "$end_date" >= end_date)
       OR (("$start_date" >= start_date AND "$start_date" <= end_date) OR ("$end_date" >= start_date AND "$end_date" <= end_date)))
SQL;
        $stmt = $db->query($sql);
        if ($db->num_rows($stmt) > 0) {
            $res = $db->fetch_all($stmt);
            return $res[0][0];
        }
        return 0;
    }
    /**
     *  getAllNumberPlaceHoraireBetweenDate : Renvoie le nombre maximum de place sur une horaire, les dates,
     *                                        et le maximum de place par réservation
     *                                        entre deux dates pour une ressource donnée.
     *
     *  @access public
     *  @return void
     */
    public function getAllNumberPlaceHoraireBetweenDate($start_date, $end_date, $id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT maximum_number_place_by_reservation, ressource_id, start_date, end_date, start_hour, end_hour, maximum_number_place
    FROM clementine_reservation_horaire, clementine_reservation_ressource_has_horaire
    WHERE clementine_reservation_ressource_has_horaire.horaire_id = clementine_reservation_horaire.id
      AND clementine_reservation_ressource_has_horaire.ressource_id = $id_ressource 
      AND (("$start_date" <= start_date AND "$end_date" >= start_date) 
       OR ("$start_date" <= end_date AND "$end_date" >= end_date)
       OR (("$start_date" >= start_date AND "$start_date" <= end_date) OR ("$end_date" >= start_date AND "$end_date" <= end_date)))
SQL;
        $stmt = $db->query($sql);
        if ($db->num_rows($stmt) > 0) {
            $fetch_all = $db->fetch_all($stmt, MYSQLI_ASSOC);
            $fetch_all = array_unique($fetch_all, SORT_REGULAR);
            return $fetch_all;
        }
        return 0;
    }
    /**
     *  getRessourceIdById : Renvoie l'id de la ressource associé à l'horaire
     *
     *  @access public
     *  @return void
     */
    public function getRessourceIdById($horaire_id)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT ressource_id
    FROM clementine_reservation_horaire, clementine_reservation_ressource_has_horaire
    WHERE clementine_reservation_horaire.id = clementine_reservation_ressource_has_horaire.horaire_id
      AND clementine_reservation_horaire.id = $horaire_id
    LIMIT 1
SQL;
        $stmt = $db->query($sql);
        $fetch_all = $db->fetch_all($stmt, MYSQLI_ASSOC);
        return $fetch_all[0]["ressource_id"];
    }
    /**
     *  getIdByDateAndRessource : Renvoie l'id de l'horaire associé à une ressource entre deux dates
     *
     *  @access public
     *  @return void
     */
    public function getIdByDateAndRessource($start_date, $end_date, $id_ressource)
    {
        $db = $this->getModel('db');
        list($start_date, $start_hour) = explode(" ", $start_date);
        list($end_date, $end_hour) = explode(" ", $end_date);
        $sql = <<<SQL
    SELECT id
    FROM clementine_reservation_horaire, clementine_reservation_ressource_has_horaire
    WHERE clementine_reservation_horaire.id = clementine_reservation_ressource_has_horaire.horaire_id
      AND clementine_reservation_ressource_has_horaire.ressource_id = $id_ressource
      AND (("$start_date" <= start_date AND "$end_date" >= start_date) 
       OR ("$start_date" <= end_date AND "$end_date" >= end_date)
       OR (("$start_date" >= start_date AND "$start_date" <= end_date) OR ("$end_date" >= start_date AND "$end_date" <= end_date)))
    LIMIT 1
SQL;
        $stmt = $db->query($sql);
        $fetch_all = $db->fetch_all($stmt, MYSQLI_ASSOC);
        if (isset($fetch_all[0]["id"])) {
            return $fetch_all[0]["id"];
        }
        return 0;

    }
    /**
     *  getFirstHoraire : Renvoie l'id du premier horaire venant, permet de vérifier si une horaire est créé
     *
     *  @access public
     *  @return void
     */
    public function getFirstHoraire()
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT *
    FROM clementine_reservation_horaire
    LIMIT 1
SQL;
        $ressource = $db->fetch_assoc($db->query($sql));
        return $ressource['id'];

    }
    /**
     *  getAllInfo : Renvoie toutes les informations d'un horaire par rapport à son id.
     *
     *  @access public
     *  @return void
     */
    public function getAllInfo($id_horaire)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT *
    FROM clementine_reservation_horaire, clementine_reservation_horaire_has_option
    WHERE clementine_reservation_horaire.id = clementine_reservation_horaire_has_option.id_horaire
      AND clementine_reservation_horaire_has_option.id_horaire = $id_horaire
      AND clementine_reservation_horaire.option = 1
SQL;
        return $db->fetch_all($db->query($sql) , MYSQLI_ASSOC);
    }
}
