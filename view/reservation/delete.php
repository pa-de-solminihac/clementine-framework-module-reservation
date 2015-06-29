<?php
$this->getBlock('reservation/header', $data, $request);
$this->getBlock('reservation/delete_header_content', $data, $request);
$this->getParentBlock($data, $request);
// Surcharge de la vue delete pour que le client puisse donner une raison à la suppression d'une reservation
$start_date = $request->get('string', 'start_date');
if (!$start_date) {
    $reservation_mdl = $this->getModel('reservation');
    $info_resa = $reservation_mdl->getAllInfoReservationById($request->get('int', 'clementine_reservation-id'));
    $start_date = $info_resa['start_date'];
    list($date, $hour) = explode(' ', $start_date);
    $start_date = $date . '_' . $hour;
}
?>
<script type="text/javascript">
    alert("Vous pouvez donné la raison de la suppression ou le laissez vide");
</script>
<form method="post" class="clementine_crud-update_form clementine_crud-form">
    <div class="clementine_crud-row clementine_crud-update-row form-group">
        <label for="raison" class="clementine_crud-title_column 
                                   clementine_crud-update-title_column 
                                   clementine_reservation-comment-title_column">Raison de la suppression</label>
        <div class="clementine_crud-value_column clementine_crud-update-value_column">
            <textarea id="raison" name="raison" class="clementine_crud-type-textarea clementine_crud-update_type-textarea form-control"></textarea>
        </div>
    </div>
    <a href="<?php echo __WWW__ . '/reservation/update?clementine_reservation-id=' . $request->get('int', 'clementine_reservation-id') . '&start_date=' . $start_date . '&clementine_reservation_ressource-id=' . $request->get('int', 'clementine_reservation_ressource-id'); ?>"
       class="clementine_crud-backbutton 
              clementine_crud-update-backbutton backbutton 
              btn btn-lg btn-default btn-raised btn-white 
              pull-left btn-fab">
        <i class="glyphicon glyphicon-arrow-left"></i>
        <span class="text-hide">Annuler</span>
        <div class="ripple-wrapper">
            <div class="ripple ripple-on" 
                 style="left: 21px; top: 32px; -webkit-transform: scale(7); 
                 transform: scale(7); background-color: rgba(0, 0, 0, 0.843137);">
            </div>
        </div>
    </a>
    <button type="submit" class="clementine_crud-savebutton clementine_crud-update-savebutton savebutton btn btn-lg btn-primary btn-raised pull-right btn-fab" title="Enregistrer">
        <i class="glyphicon glyphicon-ok"></i>
        <span class="text-hide">Enregistrer</span>
        <div class="ripple-wrapper"></div>
    </button>
</form>
<style>

    .is-an-iframe .navbar, .is-an-iframe .clementine_crud-backbutton {
        display : none !important;
    }
    .btn-white:not(.btn-link):not(.btn-flat) {
        background-color: #fff;
        color: rgba(0,0,0,.84);
    }
</style>

<?php
$this->getBlock('reservation/delete_footer_action', $data, $request);
$this->getBlock('reservation/delete_footer_content', $data, $request);
$this->getBlock('reservation/footer', $data, $request);
