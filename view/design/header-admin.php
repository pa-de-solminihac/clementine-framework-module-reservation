<?php
$sidebar = array();
$ns = $this->getModel('fonctions');
$users = $this->getModel('users');
$auth = $users->getAuth();
$userActual = $users->getUserByLogin($auth['login']);
if (isset($request->GET['clementine_reservation_ressource-id'])) {
    $data['id_ressource'] = $request->GET['clementine_reservation_ressource-id'];
} else {
    $ressource_mdl = $this->getModel('ressource');
    $data['id_ressource'] = $ressource_mdl->getFirstIdRessource();
}
$ressource_title = 'Gérer les ressources';
// Pour faciliter l'affichage si config il y a on affiche gerer la seule ressource qui est dans le config.ini
if (!empty(clementine::$config['module_fullcalendar']['ressource'])) {
    $ressource_title = "Gérer les " . clementine::$config['module_fullcalendar']['ressource'] . 's';
}
$menus = array(
    $ressource_title,
);
if (clementine::$config['module_fullcalendar']['lang'] == 'en') {
    $ressource_title = 'Managing resources';
    if (!empty(clementine::$config['module_fullcalendar']['ressource'])) {
        $ressource_title = 'Manage ' . clementine::$config['module_fullcalendar']['ressource'] . 's';
    }
    $menus = array(
        $ressource_title,
    );
}
if ($users->hasPrivilege('gerer_reservation')) {
    $sidebar['Reservations'] = array(
        'url' => '#',
        'icon' => '<i class="glyphicon glyphicon-calendar"></i>',
        'badge' => '',
        'recursive_menu' => array(
            'Ressource' => array(
                'url' => __WWW__ . '/ressource?clementine_reservation_ressource-id=' . $data['id_ressource'],
                'icon' => '<i class="glyphicon glyphicon-menu-right"></i>',
                'recursive_menu' => array(
                    $menus[0] => array(
                        'url' => __WWW__ . '/ressource?clementine_reservation_ressource-id=' . $data['id_ressource'],
                        'icon' => '<i class="glyphicon glyphicon-pencil"></i>',
                    ) ,
                ) ,
            ) ,
        )
    );
    $ressource_mdl = $this->getModel('ressource');
    $list_total_ressource = $ressource_mdl->getListRessource();
    if (!empty($list_total_ressource)) {
        foreach ($list_total_ressource as $key => $value) {
            $sidebar['Reservations']['recursive_menu']['Ressource']['recursive_menu'][$value[1]] = array(
                'url' => __WWW__ . '/reservation/calendar?clementine_reservation_ressource-id=' . $value[0],
                'icon' => '<i class="glyphicon glyphicon-menu-right"></i>'
            );
        }
    } 
    $sidebar['Reservations']['recursive_menu']['Ressource']['recursive_menu']['Toutes les ressource'] = array(
        'url' => __WWW__ . '/reservation/all',
        'icon' => '<i class="glyphicon glyphicon-menu-right"></i>'
    );
}
if (empty($data['navbar-sidebar'])) {
    $data['navbar-sidebar'] = $sidebar;
} else {
    $data['navbar-sidebar'] = $ns->array_override($sidebar, $data['navbar-sidebar']);
}
$this->getParentBlock($data, $request);
