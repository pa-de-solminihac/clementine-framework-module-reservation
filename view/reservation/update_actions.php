<?php
$this->getParentBlock($data, $request);
// Si c'est une vue de type créate on donne la possibilité au client de bloquer un créneaux on créé donc un bouton
if ($data['alldata']['formtype'] == 'create') {
?>
<a class="clementine_crud-update-blocked clementine_crud-blockedbutton <?php
    echo implode(' ', $data['alldata']['more_classes_delbutton']); ?>"
    href="<?php
    echo __WWW__ . '/reservation/block?start_date=' . $request->get('string', 'start_date') . '&clementine_reservation_ressource-id=' . $request->get('int', 'clementine_reservation_ressource-id') . '&is_iframe=1'; ?>"
    title="Blocker un créneaux">
    <i class="glyphicon glyphicon-lock"></i><span class="text-hide">Blocker un créneaux</span>
</a> 
<?php
}
