<?php
$this->getBlock('fullcalendarresa/calendar_modal');
$user_mdl = $this->getModel('users');
$ressource_mdl = $this->getModel('ressource');
$privileges = array(
    'gerer_reservation' => true
);
$co = true;
if (!$user_mdl->hasPrivilege($privileges)) {
    $co = false;
}
$libelle = $data['libelle'];
$id_ressource = $data['id_ressource'];
$lang = clementine::$config['module_fullcalendar']['lang'];
$auth = $user_mdl->getAuth();
if ($lang == 'fr') {
    $list = 'Liste';
} else {
    $list = 'List form';
}
// Affiche le libelle de chaque ressource en lui donnant un lien si c'est un client

if ($data['choix_ress'] != - 1) {
    if ($co) {
?>
	    <div id="<?php
        echo $libelle; ?>">
	    	<h1 class="titre_page"> 
	    		<a href="<?php
        echo __WWW__ . '/ressource/update?clementine_reservation_ressource-id=' . $id_ressource; ?>" class="titre_page">
	         		<?php
        echo 'Reservation : ' . $libelle; ?>
	         	</a>
	        </h1>
        </div>
    <div id="menu_calendar">
<?php
        $this->getBlock('fullcalendarresa/legende', $data, $request);
?>
        <div id="number_people">
            <input class="input_reservation form-control input-sm" placeholder="Nombre de personne(s)" type="text" id="nb_recherche" name="recherche">
        </div>
    </div> 
<?php
    } else if ($auth && !$co) {
?>
    	<div id="<?php
        echo $libelle; ?>">
			<h1 style="margin-left: -200px" class="titre_page"> 
	        	<?php
        echo 'Reservation : ' . $libelle; ?>
	        </h1>
        </div>
    <div id="menu_calendar">
<?php
        $this->getBlock('fullcalendarresa/legende', $data, $request);
?>
        <div id="number_people_unco">
            <input class="input_reservation form-control input-sm" placeholder="Nombre de personne(s)" type="text" id="nb_recherche" name="recherche">
        </div>
    </div> 
<?php
    } else {
?>
		<div id="<?php
        echo $libelle; ?>">
			<h1 style="margin-left: -200px" class="titre_page"> 
	        	<?php
        echo 'Reservation : ' . $libelle; ?>
	        </h1>
        </div>
        <div id="menu_calendar">
<?php
        $this->getBlock('fullcalendarresa/legende', $data, $request);
?>
            <div id="number_people_unco">
                <input class="input_reservation form-control input-sm" placeholder="Nombre de personne(s)" type="text" id="nb_recherche" name="recherche">
            </div>
        </div> 
<?php
    }
?>

	<div id="calendar<?php
    echo $id_ressource; ?>">
        <div id='loading' style='z-index:9; position:absolute; top:250px ; left:0% ; width:100% ; height :100% ;  background-color: rgba(255,255,255,0.5);  ' > </div>
    </div>
<?php
} else {
    if ($co) {
        for ($i = 0; $i < count($data['matrice_valeur']); ++$i) {
?>
            <div>
            	<a href="<?php
            echo __WWW__ . '/reservation'; ?>">
            		Sous forme de liste
            	</a>
            </div>
            <div id="<?php
            echo $data['matrice_valeur'][$i][1]; ?>">
            	<h1>
            		<a href="<?php
            echo __WWW__ . '/ressource/update?clementine_reservation_ressource-id=' . $data['matrice_valeur'][$i][0]; ?>"> 
                 		<?php
            echo $data['matrice_valeur'][$i][1] ?>
                 	</a>
                </h1>
            </div>
            <div id="<?php
            echo 'calendar' . $data['matrice_valeur'][$i][0]; ?>"></div>
<?php
        }
    } else {
        for ($i = 0; $i < count($data['matrice_valeur']); ++$i) {
?>
            <div id="<?php
            echo $data['matrice_valeur'][$i][1]; ?>">
            	<h1> 
                	<?php
            echo $data['matrice_valeur'][$i][1]; ?>
                </h1>
            </div>
            <div id="<?php
            echo 'calendar' . $data['matrice_valeur'][$i][0]; ?>"></div>
<?php
        }
    }
}

