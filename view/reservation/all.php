<?php
// Au premier appel du fichier on charge le css et on affiche le input du nombre de personnes, ainsi que l'affichage
if (!$request->AJAX && empty($data['is_iframe']) && empty($data['hidden_sections']['header'])) {
    $cssjs = $this->getModel('cssjs');
    $this->getBlock('reservation/header', $data, $request);
?>
    <div>
        <div style="margin-bottom: 50px;">
            <h1 class="titre_page" id="title_all">
                Gérer toutes les reservations
            </h1>
        </div>
        <div id="menu_calendar">
<?php
    $this->getBlock('fullcalendarresa/legende', $data, $request);
?>
            <div id="number_people">
                <input class="input_reservation form-control input-sm" type="text" id="nb_recherche" name="recherche" placeholder="Nombre de personne(s)">
            </div>
        </div>
    </div>
<?php
    $cssjs->register_foot('fullcalendarresa/js_fullcalendarall', $this->getBlockHtml('fullcalendarresa/js_fullcalendarall', $data, $request));
    $this->getBlock('fullcalendarresa/calendar_modal');
?>   
    <div id="calendar" class="reservation_calendar_container">
        <div class='reservation_calendar_loading'></div>
    <div>
<?php
    $this->getBlock('reservation/footer_content', $data, $request);
    $this->getBlock('reservation/footer', $data, $request);
} else {
    // Au deuxième appel effectué en AJAX, on ajoute la timeline des reservation
    echo json_encode($data['timeline_resa']);
}
