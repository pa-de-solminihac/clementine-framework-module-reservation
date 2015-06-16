<?php
class reservationRessourceModel extends reservationRessourceModel_Parent
{
    public $table_reservation_ressource = 'clementine_reservation_ressource';
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
        );
    }
    /**
     *  getListRessource : La fonction getListRessource() permet de récupérer, les ressouces d'un utilisateurs par rapport à sont $idClient.
     *                     Elle renvoie un tableau permetant de créer un formulaire de séléctionné des ressources.
     *
     * @access public
     * @return void
     */
    public function getListRessource()
    {
        $db = $this->getModel('db');
        $stmt = $db->query("SELECT id, libelle FROM clementine_reservation_ressource");
        $list_total_ressource = array();
        $request = $this->getRequest();
        while ($res = $db->fetch_assoc($stmt)) {
            $input = array(
                $res['id'],
                $res['libelle']
            );
            array_push($list_total_ressource, $input);
        }
        return $list_total_ressource;
    }
    /**
     *  getFirstIdRessource : Renvoie le premier id ressource
     *                        Utilisé pour tester si une ressource est créé
     *
     * @access public
     * @return void
     */
    public function getFirstIdRessource()
    {
        $db = $this->getModel('db');
        $stmt = $db->query("SELECT id FROM clementine_reservation_ressource LIMIT 1");
        $res = $db->fetch_all($stmt);
        if ($res == null) {
            return 0;
        } else {
            return $res[0][0];
        }
    }
    /**  
     * returnAllIdRessource : Renvoie la liste de tous les id_ressources pour les affichés
     *
     * @access public
     * @return void
     */
    public function returnAllIdRessource()
    {
        $db = $this->getModel('db');
        $stmt = $db->query("SELECT id FROM clementine_reservation_ressource");
        $list_total_id = array();
        while ($res = $db->fetch_assoc($stmt)) {
            array_push($list_total_id, $res["id"]);
        }
        return $list_total_id;
    }
    /**  
     * getMaxIdRes : Utilisé pour assigner à toute les nouvelles ressources un id
     *
     * @access public
     * @return void
     */
    public function getMaxIdRes()
    {
        $db = $this->getModel('db');
        $stmt = $db->query('SELECT MAX(id) as maximum FROM ' . $this->table_reservation_ressource);
        $res = $db->fetch_assoc($stmt);
        return $res['maximum'];
    }
    /**
     *   getLibelle : La fonction getLibelle($idRessource) permet de récupérer le libelle
     *                d'une ressource par rapport à sont idRessource.
     *
     *  @access public
     *  @return void
     */
    public function getLibelle($idRessource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT libelle
    FROM clementine_reservation_ressource 
    WHERE id = $idRessource 
    LIMIT 1
SQL;
        $stmt = $db->query($sql);
        while ($res = $db->fetch_assoc($stmt)) {
            return $res["libelle"];
        }
    }
    /**
     * getToutLibelle() :  permet de récupérer tout les libelles de toutes les ressources
     *
     * @access public
     * @return void
     */
    public function getToutLibelle()
    {
        $db = $this->getModel('db');
        $stmt = $db->query("SELECT libelle FROM clementine_reservation_ressource");
        // WHERE idClient=".$idClient."
        $list_total_libelle = array();
        while ($res = $db->fetch_assoc($stmt)) {
            array_push($list_total_libelle, $res["libelle"]);
        }
        return $list_total_libelle;
    }
    /**
     * getCreneaux : permet de récupérer le temps de créneaux d'une ressource
     *
     * @access public
     * @return void
     */
    public function getCreneaux($id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT time_creneaux
    FROM clementine_reservation_ressource
    WHERE id = $id_ressource
    LIMIT 1
SQL;
        $stmt = $db->query($sql);
        $res = $db->fetch_assoc($stmt);
        $creneaux = $res['time_creneaux'];
        return $creneaux;
    }
    /**
     * getNbPlaceMax : renvoie le nombre de place maximum possible, si le nombre de place maximum par réservation n'est pas vide et
     *                 et est différent du nombre de place maximum d'une ressource alors renvoie le nombre de place maximum par reservation
     *
     * @access public
     * @return void
     */
    public function getNbPlaceMax($id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT maximum_number_place, maximum_number_place_by_reservation
    FROM clementine_reservation_ressource
    WHERE id = $id_ressource 
SQL;
        $stmt = $db->query($sql);
        $res = $db->fetch_assoc($stmt);
        if (empty($res['maximum_number_place_by_reservation']) || $res['maximum_number_place'] == $res['maximum_number_place_by_reservation']) {
            return $res['maximum_number_place'];
        }
        return $res['maximum_number_place_by_reservation'];
    }
    /**
     * getMaximumNumberPlace($id_ressource) : renvoie le nombre maximum de place de la ressource
     *
     * @access public
     * @return void
     */
    public function getMaximumNumberPlace($id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT maximum_number_place
    FROM clementine_reservation_ressource
    WHERE id = $id_ressource 
SQL;
        $stmt = $db->query($sql);
        $res = $db->fetch_assoc($stmt);
        return $res['maximum_number_place'];
    }
    /**
     * ressourceHasHoraire : renvoie le nombre d'horaire qu'a une ressource
     *
     * @access public
     * @return int
     */
    public function ressourceHasHoraire($id_ressource)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT *
    FROM clementine_reservation_ressource_has_horaire
    WHERE ressource_id = $id_ressource
SQL;
        $stmt = $db->query($sql);
        return $db->num_rows($stmt);
    }
    /**
     * ressourcehasHoraireInTimeById : Vérifie qu'une ressource à une horaire dans le temps donnée. On effectue cette fonction
     *                                 pour vérifier qu'une horaire n'empiète pas sur une autre horaire
     *
     * @access public
     * @return int
     */
    public function ressourcehasHoraireInTimeById($id_ressource, $start_date, $end_date, $start_hour, $end_hour, $id_horaire, $time_creneaux)
    {
        $db = $this->getModel('db');
        $sql = <<<SQL
    SELECT clementine_reservation_horaire.id, clementine_reservation_horaire.start_hour, clementine_reservation_horaire.end_hour, clementine_reservation_ressource.time_creneaux, clementine_reservation_horaire.time_creneaux as horaire_cren
    FROM clementine_reservation_ressource, clementine_reservation_ressource_has_horaire, clementine_reservation_horaire
    WHERE clementine_reservation_ressource.id = clementine_reservation_ressource_has_horaire.ressource_id
      AND clementine_reservation_ressource_has_horaire.horaire_id = clementine_reservation_horaire.id
      AND clementine_reservation_ressource.id = $id_ressource
      AND clementine_reservation_horaire.id != $id_horaire
      AND (((start_date = "$end_date" OR end_date = "$start_date") AND (end_hour > "$start_hour" OR "$end_hour" > start_hour))
           OR ("$start_date" >= start_date AND "$start_date" < end_date) 
           OR ("$end_date" > start_date AND "$end_date" <= end_date))
SQL;
        $stmt = $db->query($sql);
        $time_creneaux.= ':00';
        $time_creneaux = trim($time_creneaux);
        while ($res = $db->fetch_assoc($stmt)) {
            if (!empty($time_creneaux) && $time_creneaux != ":00" && $time_creneaux != "00:00:00" && $time_creneaux != trim($res['time_creneaux']) && $time_creneaux != trim($res['horaire_cren'])) {
                return true;
            }
        }
        return false;
    }
}
