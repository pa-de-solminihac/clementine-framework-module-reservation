<?php
$this->getParentBlock($data, $request);
if ($request->ACT == "update") {
    if (clementine::$config['module_fullcalendar']['lang'] == "fr") {
?>
            <h1 class="titre_page">
                Modification d'une horaire
            </h1>
<?php
    } else {
?>
            <h1 class="titre_page">
                Changing a schedule
            </h1>
<?php
    }
} else {
    if (clementine::$config['module_fullcalendar']['lang'] == "fr") {
?>
            <h1 class="titre_page">
                Gestion des horaires
            </h1>
<?php
    } else {
?>
            <h1 class="titre_page">
                Schedule management
            </h1>   
<?php
    }
}
