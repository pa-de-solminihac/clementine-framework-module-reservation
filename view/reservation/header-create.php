<?php
$this->getParentBlock($data, $request);
if (Clementine::$config['module_reservation']['lang'] == "fr") {
?>
        <h1 class="titre_page">
            Création d'une réservation 
        </h1>
<?php
} else {
?>
        <h1 class="titre_page">
            Creating a reservation 
        </h1>         
<?php
}
?>
