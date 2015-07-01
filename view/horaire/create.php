<?php
$user = $this->getModel('users');
$privileges = array(
    'clementine_reservation_gerer_reservation' => true
);
$ressource_id = $request->get('int', 'clementine_reservation_ressource-id');
$id_ressource = $request->get('int', 'id_ressource');
if ($ressource_id || $id_ressource) {
    $this->getParentBlock($data, $request);
} else if ($user->hasPrivilege($privileges)) {
    $lang = Clementine::$config['module_reservation']['lang'];
    $this->getBlock('reservation/header', $data, $request);
    if ($lang == "fr") {
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
