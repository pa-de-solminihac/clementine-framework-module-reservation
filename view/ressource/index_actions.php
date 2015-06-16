<?php
$ns = $this->getModel('fonctions');
$current_key = $ns->htmlentities($data['current_key']);
$sections = array(
    "updatebutton" => 'getParent',
    "horaire" => array(
        'url' => __WWW__ . '/horaire?is_modif=1&' . $current_key,
        'icon' => 'glyphicon glyphicon-list-alt',
        'label' => 'Gerer les horaires',
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
