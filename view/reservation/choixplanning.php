<?php
$this->getBlock('reservation/header', $data, $request);
$phrase = 'Accéder au calendrier : ';
$tous = 'Afficher toute les ressources';
$this->getBlock('reservation/footer_action', $data, $request);
$list_total_ressource = $data['list_total_ressource'];
$nb_ressources = count($list_total_ressource);
// Affichage de ressource en bas du calendrier.

?>
    <div id="affichage_calendar">
<?php
for ($i = 0; $i < $nb_ressources; ++$i) {
    echo '<a class="btn lien_calendar" href=' . __WWW__ . '/reservation/calendar?clementine_reservation_ressource-id=' . $list_total_ressource[$i][0] . ' >' . $phrase . $list_total_ressource[$i][1] . '</a><br />  ';
}
echo '  <a class="btn lien_calendar" href=' . __WWW__ . '/reservation/all >' . $tous . '</a><br />';
$this->getBlock('reservation/footer', $data, $request);
?>
    </div>
<style>
    .lien_calendar{
    
    }
    #affichage_calendar {
        margin-top : 5%;
        margin-left : 25%;
    }
    #wrapper {
        padding-top: 0;
    }
</style>
