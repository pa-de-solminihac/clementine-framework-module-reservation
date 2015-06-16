<?php
if (!$request->AJAX && empty($data['is_iframe']) && empty($data['hidden_sections']['header'])) {

    $cssjs = $this->getModel('cssjs');

    if ($data['choix_ress'] == - 1) // Affichage de tout les calendriers
    {
        for ($i = 0; $i < count($data['matrice_valeur']); ++$i) {
            $data['id_ressource'] = $data['matrice_valeur'][$i][0];
            $data['libelle'] = $data['matrice_valeur'][$i][1];
            $data['plage_horraire'] = $data['matrice_valeur'][$i][3];
            $data['total_horaire_resa'] = $data['matrice_valeur'][$i][4];
            $cssjs->register_foot('fullcalendarresa/js_fullcalendar-' . $data['id_ressource'], $this->getBlockHtml('fullcalendarresa/js_fullcalendar', $data, $request));
        }
    } else { // Affichage que du calendrier de la ressource séléctionné
        $data['total_horaire_resa'] = $data['plage_horraire_util'];
        $cssjs->register_foot('fullcalendarresa/js_fullcalendar-' . $data['id_ressource'], $this->getBlockHtml('fullcalendarresa/js_fullcalendar', $data, $request));
    }
    // Si l'heure est activez, checker le bouton

?>
     <!-- <form action="" method="post" >
        <select id="list_ressources" name='ressource'> -->
<?php
    // if ($list_total_ressource[$i][0] == $request->get('int', 'clementine_reservation_ressource-id')) {
    // echo '<option value="' . $list_total_ressource[$i][0] . '" selected>' . $list_total_ressource[$i][1] . '</option>';
    // } else {
    // echo '<option value="' . $list_total_ressource[$i][0] . '">' . $list_total_ressource[$i][1] . '</option>';
    // }
    // echo '<option value="-1">Tous</option>'

?>
      <!--  </select>
        <input type="submit" value="select">
    </form>-->
<?php
} else {
    $cssjs = $this->getModel('cssjs');
    if ($data['choix_ress'] == - 1) // Affichage de tout les calendriers
    {
        for ($i = 0; $i < count($data['matrice_valeur']); ++$i) {
            $data['id_ressource'] = $data['matrice_valeur'][$i][0];
            $data['libelle'] = $data['matrice_valeur'][$i][1];
            $data['total_horaire_resa'] = array_unique($data['matrice_valeur'][$i][4], SORT_REGULAR);
            $data['plage_horraire'] = $data['matrice_valeur'][$i][3];
            $data['list_horraire_util'] = $data['matrice_valeur'][$i][5];
            if ($request->get('int', 'idRes') == $data['id_ressource']) {
                echo json_encode(array_merge($data['total_horaire_resa'], $data['list_horraire_util']));
            }
        }
    } else { // Affichage que du calendrier de la ressource séléctionné
        $data['total_horaire_resa'] = array_unique($data['plage_horraire_util'], SORT_REGULAR);
        echo json_encode(array_merge($data['total_horaire_resa'], $data['list_horraire_util']));
    }
}
