<?php
$users = $this->getModel('users');
$lang = Clementine::$config['module_reservation']['lang'];
if ($data['id_ressource'] != 0) {
    // Affiche le nombre de personnes le calendrier etc au premier appel
    if (!$request->AJAX && empty($data['is_iframe']) && empty($data['hidden_sections']['header'])) {
        $this->getBlock('reservation/header', $data, $request);
        $ressource_mdl = $this->getModel('ressource');
        if (!$ressource_mdl->ressourceHasHoraire($data['id_ressource']) && $users->hasPrivilege(array(
            'clementine_reservation_gerer_reservation' => true
        ))) {
            if ($lang == "fr") {
?>
                <a class="alert alert-danger" style="margin-top:10px; display: block;" href="<?php
                echo __WWW__ . '/horaire/create?clementine_reservation_ressource-id=' . $data['id_ressource']; ?>">
                    Il n'y a pas d'horaire sur cette ressource sur l'affichage actuel, veuillez en créé une pour pouvoir passer des réservations
                </a>
<?php
            } else {
?>    
                <a class="alert alert-danger" style="margin-top:10px; display: block;" href="<?php
                echo __WWW__ . '/horaire/create?clementine_reservation_ressource-id=' . $data['id_ressource']; ?>">
                    There is no schedule on the actual display, please created to pass reservations
                </a>
<?php
            }
        }
        $this->getBlock('reservation/header_content', $data, $request);
        $this->getBlock('fullcalendarresa/interfaceutil', $data, $request);
        $this->getBlock('reservation/footer_calendar', $data, $request);
        $this->getBlock('reservation/footer_content', $data, $request);
        $this->getBlock('reservation/footer', $data, $request);
    } else {
        // Remplis le calendrier au deuxième appel
        $this->getBlock('fullcalendarresa/interfaceutil', $data, $request);
    }
} else if ($users->hasPrivilege(array(
    'clementine_reservation_gerer_reservation' => true
))) {
    $this->getBlock('reservation/header', $data, $request);
    if ($lang == 'fr') {
?>
        <a class="alert alert-danger" style="margin-top:10px; display: block;" href="<?php
        echo __WWW__ . '/ressource/create'; ?>">
            Il n'y a pas de ressource créé actuellement, veuillez en créé une pour pouvoir passer des réservations
        </a>
<?php
    } else {
?>
        <a class="alert alert-danger" style="margin-top:10px; display: block;" href="<?php
        echo __WWW__ . '/ressource/create'; ?>">
            There is no resource presently created , please created to pass reservations
        </a>
<?php
    }
    $this->getBlock('reservation/footer', $data, $request);
}
