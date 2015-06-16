<?php
$user = $this->getModel('users');
$privileges = array(
    'gerer_reservation' => true
);
if (!empty($request->GET['clementine_reservation_ressource-id']) || !empty($request->GET['id_ressource'])) {
    $this->getParentBlock($data, $request);
} else if ($user->hasPrivilege($privileges)) {
    $lang = clementine::$config['module_fullcalendar']['lang'];
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
