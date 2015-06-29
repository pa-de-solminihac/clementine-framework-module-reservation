<?php
$this->getBlock('reservation/header', $data, $request);
// Création de la vue du bloquage, on peut donné une raison au bloquage du créneaux

?>

<form method="post" class="clementine_crud-update_form clementine_crud-form">
    <div class="clementine_crud-row clementine_crud-update-row form-group">
        <label for="commentaire" class="clementine_crud-title_column
                                   clementine_crud-update-title_column
                                   clementine_reservation-comment-title_column">Raison du bloquage de créneaux</label>
        <div class="clementine_crud-value_column clementine_crud-update-value_column">
            <textarea id="commentaire" name="commentaire" class="clementine_crud-type-textarea clementine_crud-update_type-textarea form-control"></textarea>
        </div>
    </div>

    <a href="<?php echo __WWW__ . '/reservation/create?start_date=' . $request->get('string', 'start_date') . '&clementine_reservation_ressource-id=' . $request->get('int', 'clementine_reservation_ressource-id'); ?>"
       class="clementine_crud-backbutton
              clementine_crud-update-backbutton
              backbutton btn btn-lg btn-default
              btn-raised btn-white pull-left btn-fab">
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
<?php
$this->getBlock('reservation/footer', $data, $request);
?>
