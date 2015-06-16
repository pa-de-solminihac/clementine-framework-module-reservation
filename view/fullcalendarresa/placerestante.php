<?php
$fullcalendar_mdl = $this->getModel('fullcalendarresa');
$reservation_mdl = $this->getModel('reservation');
$fullcalendar_helper = $this->getHelper('fullcalendarresa');
$ressource_mdl = $this->getModel('ressource');
$horaire_mdl = $this->getModel('horaire');
$id_ressource = $request->GET['clementine_reservation_ressource-id'];
list($day, $hour) = explode('_', $request->GET['start_date']);
list($year, $month, $days) = explode('-', $day);
$next_day = date("Y-m-d", mktime(0, 0, 0, $month, $days + 1, $year));
$list_creneaux = $data['list_creneaux'];
$list_creneaux = array_values(array_filter($list_creneaux));
$incr = 0;
foreach ($list_creneaux as $tab_total) {

    if (strpos($tab_total, '-') == false) {
        $list_creneaux[$incr] = '';
    }
    ++$incr;

}
sort($list_creneaux, SORT_REGULAR);
$list_creneaux = array_values(array_filter($list_creneaux));
$nb_place_max = $ressource_mdl->getMaximumNumberPlace($id_ressource);
$nb_place_max_resa = $ressource_mdl->getNbPlaceMax($id_ressource);
$nb_place_max_horaire = $horaire_mdl->getAllNumberPlaceHoraireBetweenDate($day, $next_day, $id_ressource);
$nb_place_max_tmp = $nb_place_max;
$creneaux = $ressource_mdl->getCreneaux($id_ressource);
$list_nb_place_restante = array();
foreach ($list_creneaux as $cre) {
    list($db, $df) = explode('-', $cre);
    if (!empty($nb_place_max_horaire)) {
        foreach ($nb_place_max_horaire as $key => $value) {
            if ($db >= $value['start_hour'] || $df <= $value['end_hour']) {
                if (!empty($value["maximum_number_place"]) && $value["maximum_number_place"] != $nb_place_max) {
                    $nb_place_max = $value["maximum_number_place"];
                }
                if (!empty($value['maximum_number_place_by_reservation']) && $value['maximum_number_place_by_reservation'] != $nb_place_max_resa) {
                    $nb_place_max_resa = $value['maximum_number_place_by_reservation'];
                }
            }
        }
    }
    $nb_place_prise = $reservation_mdl->getNbPlacePrise($day . ' ' . trim($db) , $day . ' ' . trim($df) , $id_ressource);
    $nb_place_restante = $nb_place_max - $nb_place_prise;
    if (!empty($nb_place_max_resa) && $nb_place_max_resa < $nb_place_restante) {
        $nb_place_restante = $nb_place_max_resa;
    }
    $list_nb_place_restante[$cre] = $nb_place_restante;
    $nb_place_max = $nb_place_max_tmp;
}
$end_date = $fullcalendar_helper->secondToTime($fullcalendar_helper->timeToSecond($hour) + $fullcalendar_helper->timeToSecond($creneaux));
$end_date = date($day . ' ' . $end_date);
$start_date = date($day . ' ' . $hour);
$nb_place_prise = $reservation_mdl->getNbPlacePrise($start_date, $end_date, $id_ressource);
$nb_place_restante = $nb_place_max - $nb_place_prise;
$lang = clementine::$config['module_fullcalendar']['lang'];
?>
<script type="text/javascript">
    jQuery(document).ready(function() { 
        var lang = <?php
echo json_encode($lang); ?>; 
        var list_nb_place_restante = <?php
echo json_encode($list_nb_place_restante); ?>;
        var Select = jQuery("#creneaux option:selected").text(); 
        var place = list_nb_place_restante[Select];
        jQuery('#place_restante').empty();
        if (lang == "fr") {
            jQuery('.creneaux-value_column').prepend('<div id="place_restante"> il reste ' 
                                                     + place
                                                     +' place pour ce creneaux</div>');
        } else {
            jQuery('.creneaux-value_column').prepend('<div id="place_restante"> it remains ' 
                                                     + place 
                                                     + ' place for this slot</div>');
        }
        jQuery('#creneaux').change(function() {
            var Select = jQuery("#creneaux option:selected").text(); 
            var place = list_nb_place_restante[Select];
            jQuery('#place_restante').empty();
            if (lang == "fr") {
                jQuery('#place_restante').prepend("il reste " + place + " place pour ce creneaux");
            } else {
                jQuery('#place_restante').prepend("it remains " + place + " place for this slot");
            }
        });
    });
</script>
