<?php
$users = $this->getModel('users');
$lang = clementine::$config['module_fullcalendar']['lang'];
$privileges = array(
    'clementine_reservation_gerer_reservation' => true
);
$admin = $users->hasPrivilege($privileges);
if ($lang == 'fr') {
    $affiche_hor = 'Affichage des horaires';
} else {
    $affiche_hor = 'Viewing hours';
}
if (isset($data['h_active']) && $data['h_active'] == 'true') {
    $checked = "checked";
} else {
    $checked = "";
}
if ($admin) {
?>
    <div id="horaire" class="togglebutton">
        <label>
            <?php
    echo $affiche_hor; ?>
            <input type="checkbox" style="margin-left: 5px;" id="h_active" name="h_active" value="true" <?php echo $checked; ?>> 
        </label>
    </div>

<?php
}
