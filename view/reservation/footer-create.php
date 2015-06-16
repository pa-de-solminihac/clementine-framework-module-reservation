<?php
$this->getParentBlock($data, $request);
if (!empty(clementine::$config['module_reservation']['placerestante']) && clementine::$config['module_reservation']['placerestante'] == 1) {
    $this->getBlock('fullcalendarresa/placerestante', $data, $request);
}
?>
<script>
a = jQuery('#clementine_reservation_users-mail');
a.on('input', function() {
    input = a.val();
    valide = false;
    for (var j = 1; j < (input.length); ++j) {
        if (input.charAt(j) == '@') {
            if (j < (input.length - 4)) {
                for (var k = j; k < (input.length-2); ++k) {
                    if (input.charAt(k) == '.') {
                        valide = true;
                    }
                    jQuery('#clementine_reservation_users-mail').css('background-image',
                                                                     'linear-gradient(#00FF00,#00FF00), linear-gradient(#d2d2d2,#d2d2d2)');
                }
            }
        }
    }

    if(valide==false) { 
        jQuery('#clementine_reservation_users-mail').css('background-image',
                                                         'linear-gradient(#FF0000,#FF0000), linear-gradient(#d2d2d2,#d2d2d2)');
    }
});
</script>
<style>
    #wrapper{
          margin-bottom: 100px;
    }
</style>
