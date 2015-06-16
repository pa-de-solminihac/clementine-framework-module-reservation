<?php
$ns = $this->getModel('fonctions');
$current_key = $ns->htmlentities($data['current_key']);
$sections = array(
    "updatebutton" => 'getParent',
    "ressource" => array(
        'url' => __WWW__ . '/ressource/update?' . $current_key,
        'icon' => 'glyphicon glyphicon-list-alt',
        'label' => 'Gerer la ressource',
    ) ,
    "reservation" => array(
        'url' => __WWW__ . '/reservation/calendar?' . $current_key,
        'icon' => 'glyphicon glyphicon-list-alt',
        'label' => 'Gérer les reservations',
    ) ,
    "delbutton" => "getParent"
);
$data['crud-sections'] = $sections;
$this->getParentBlock($data, $request);
