<?php
$this->getBlock('design/footer-admin', $data, $request);
// Envoie de mail si on le désire
if (Clementine::$config['mail']['send'] == 1 && $request->ACT == 'update' && $request->CTRL == 'reservation' && !isset($data['send'])) {
?>
<script type="text/javascript">
    var code = <?php
    echo json_encode(__WWW__); ?>;
    var ok = false;
    if(confirm("Voulez vous envoyez un mail si il y a une modification")) {
        ok = true;
    }
    $("#head_button").change(function() {
        if (
    });
    jQuery.ajax({
        method: "POST",
        url: code + '/reservation/mail',
        data: { ok : ok },
    }).done(function(e) {
        jQuery('a[title="Supprimer"]').attr('href', code 
                                                    + '/reservation/delete?clementine_reservation-id=' 
                                                    + <?php echo json_encode($data['id_reservation']); ?> 
                                                    + '&send=' + ok
                                                    + '&start_date=' + <?php echo json_encode($data['start_date']); ?>
                                                    + '&clementine_reservation_ressource-id=' 
                                                    + <?php echo json_encode($request->get('int', 'clementine_reservation_ressource-id')); ?>);
    });
</script>
<?php
}
