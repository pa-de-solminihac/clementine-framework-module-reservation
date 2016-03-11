<script type="text/javascript">
    jQuery(document).ready(function() { 
        var repeat_all_html = "<div id='repeat_all' class='repeat'>Repeter tous les : <select name='select_value'><option value='0'>...</option><option value='1'>Jour</option><option value='2'>Semaine</option><option value='3'>Mois</option></select></div>";    
        jQuery('#clementine_reservation_horaire-option').after(repeat_all_html);
        var html_week ='<div id="check_week" class="repeat"><input type="checkbox" class="week_radio" name="select_week1" value="Monday">Lundi </input><input type="checkbox" class="week_radio" name="select_week2" value="Tuesday">Mardi </input><input type="checkbox" class="week_radio" name="select_week3" value="Wednesday">Mercredi </input><input type="checkbox" class="week_radio" name="select_week4" value="Thursday">Jeudi </input><input type="checkbox" class="week_radio" name="select_week5" value="Friday">Vendredi </input><input type="checkbox" class="week_radio" name="select_week6" value="Saturday">Samedi </input><input type="checkbox" class="week_radio" name="select_week7" value="Sunday">Dimanche </input> Toutes les <input id="nb_fois" name="nb_fois" type="number" style="width:40px;" />  semaines</div>';      
        var htm_till = '<hr /><div id="div_till" class="repeat"> Jusqu\'à :  <input placeholder="AAAA-MM-JJ" type="text" id="till" name="till" /></div>';
        
        jQuery('#repeat_all').after(html_week);
        jQuery('#check_week').hide();
        jQuery('#check_week').after(htm_till);
        jQuery('#clementine_reservation_horaire-start_date').change( function() {
            var newValue = jQuery(this).val();
            var value = newValue.split('-');
            var date  = new Date(value[0],value[1]-1,value[2]);
            var tab_jour = new Array("dimanche", "lundi", "mardi", "mercredi", "jeudi", "vendredi", "samedi");
            var jour = tab_jour[date.getDay()]; 
            var jour_ind = date.getDate();
            var date_deb_mois = new Date(value[0],value[1]-1,'1');
            var jour_deb_mois = tab_jour[date_deb_mois.getDay()];
            var second = 7;
            if (jour_ind <= second) {
                var occur = 1;
            } else {
                for (i = 0; i < 5; ++i) {
                    if (jour_ind > (second + i * 7) && jour_ind <= (second  + ((i+1) * 7))) {
                        var occur = i + 2;
                    }
                }
            }
            var date_fin_mois = new Date(value[0],value[1],'0');
            var jour_fin_mois = tab_jour[date_fin_mois.getDay()];
            var ind_fin_mois = date_fin_mois.getDate();
            var fin_mois = date_fin_mois.getDay();
            
            if (jour_ind > (ind_fin_mois - 7) && jour_ind <= ind_fin_mois) {
                var occur = 0 ;
            }
            
            if (occur == 0) {
                var string_occur = 'derniers';
            } else if (occur == 1) {
                var string_occur = 'premiers';
            } else if (occur == 2) {
                var string_occur = 'seconds';
            } else if (occur == 3) {
                var string_occur = 'troisièmes';
            } else if (occur == 4) {
                var string_occur = 'quatrièmes';
            } 
            
            
            jQuery('#repeat_all option:eq(0)').text(jour);
            jQuery('#repeat_all option:eq(0)').attr('value',jour);
            
            var html_mois ='<div id="check_mois" style="display: none;"><input type="checkbox" name="select_mois0" value="1'+value[2]+'" >Tous les '+value[2]+' du mois </input><input type="checkbox" name="select_mois'+occur+'" value="'+occur+'">Tous les '+string_occur+ ' '+jour+' du mois </input></div>';
            if (jQuery('#check_mois')) {
                jQuery('#check_mois').empty();
            }
            jQuery('#repeat_all').after(html_mois);         
        });
        $("#clementine_reservation_horaire-option").change(function() {
            if ($('#clementine_reservation_horaire-option option:selected').val() == 1) {
                jQuery('#till').attr('required', 'required');
                jQuery ('#repeat_all').show();
                jQuery('hr').show();
                jQuery('#div_till').show();
            } else {
                jQuery('#till').removeAttr('required');
                jQuery('#repeat_all').hide();
                jQuery('hr').hide();
                jQuery('#div_till').hide();
            }
        });
        jQuery('#repeat_all').change (function() {
            $("input[type=checkbox]").prop("checked", false);
            var newValue = jQuery('#repeat_all option:selected').text();
            if (newValue == "Semaine"){
                jQuery('#check_mois').hide();
                jQuery('#check_week').show();
            } else if (newValue == "Mois") {
                jQuery('#check_week').hide();
                jQuery('#check_mois').show();
            } else {
                jQuery('#check_week').hide();
                jQuery('#check_mois').hide();
            }
         });
                 
    });
    
    </script>
