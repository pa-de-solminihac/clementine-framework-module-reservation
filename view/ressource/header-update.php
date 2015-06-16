<?php
$this->getParentBlock($data, $request);
if ($request->ACT == "update") {
    if (clementine::$config['module_fullcalendar']['lang'] == "fr") {
?>
            <h1 class="titre_page">
                Modification d'une ressource 
            </h1>
<?php
    } else {
?>
            <h1 class="titre_page">
                Editing a resource
            </h1>
<?php
    }
} else {
    if (clementine::$config['module_fullcalendar']['lang'] == "fr") {
?>
            <h1 class="titre_page">
                Gestion des ressources 
            </h1>
<?php
    } else {
?>
            <h1 class="titre_page">
                Resource management
            </h1>   
<?php
    }
}
