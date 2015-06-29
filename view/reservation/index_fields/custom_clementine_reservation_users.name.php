<?php
$reservation_mdl = $this->getModel('reservation');
$user_mdl = $this->getModel('users');
if (empty($data['ligne']['clementine_reservation_users.name'])) {
    $id = $reservation_mdl->getIdClemByIdResa($data['ligne']['clementine_reservation.id']);
    $usr = $user_mdl->getUser($id);
    echo $usr[Clementine::$config['module_reservation']['getuser_lastname']];
} else {
    echo $data['ligne']['clementine_reservation_users.name'];
}
