<?php
// Initialisation de toutes les variables déstinés à être utilisés dans le calendrier
$data['info_taille'] = '00:10:00';
$this->getBlock('fullcalendarresa/calendar_css', $data);
$user_mdl = $this->getModel('users');
$ressource_mdl = $this->getModel('ressource');
$nbPlaceMax = $ressource_mdl->getMaximumNumberPlace($data['id_ressource']);
$helper = $this->getHelper('fullcalendarresa');
$module_name = $this->getCurrentModule();
$privileges = array(
    'clementine_reservation_gerer_reservation' => true
);
$co = true;
$horraire_except_util = array();
if (!$user_mdl->hasPrivilege($privileges)) {
    $co = false;
}
$auth = $user_mdl->getAuth();
$data['info_taille'] = '00:40:00';
$horraire_except_util = $data['list_horraire_util'];
$lang = Clementine::$config['module_fullcalendar']['lang'];
$horaire_mdl = $this->getModel('horaire');
$min_time = $horaire_mdl->getMinHoraire($data['id_ressource']);
$max_time = $horaire_mdl->getMaxHoraire($data['id_ressource']);
$creneaux = $ressource_mdl->getCreneaux($data['id_ressource']);
$request = $this->getRequest();
$under_file = $request->ACT;
if (isset($_GET['h_active'])) {
    $h_active = $_GET['h_active'];
} else {
    $h_active = false;
}
$mois_ac = Clementine::$config['module_fullcalendar']['mois_active'];
if ($mois_ac == "true") {
    $nb_avant_more = Clementine::$config['module_fullcalendar']['nb_avant_more'];
} else {
    $nb_avant_more = false;
}
?>
<script type="text/javascript">
if(window.location.hash) {
  var hash = window.location.hash.substring(1);
  var tab_hash = hash.split('-');
  var hash = tab_hash[0];
  var hash_date = tab_hash[1];
  if ( hash == 'semaine' ) {
    hash = "agendaWeek";
  }
  else if ( hash == 'mois' ) {
    hash = "month";
  }
  else if ( hash == 'jour' ) {
    hash = "agendaDay";
  } else if ( hash == '3jours'){
    hash = 'agendaThreeDay';
 } else {
    hash = "month";
  }
} else {
    var hash = "month";
}
var auth = <?php echo json_encode($auth); ?>; 
var id_ressource = <?php echo json_encode($data['id_ressource']); ?>; 
    jQuery(document).ready(function() { 
        var nbPlaceMax = <?php echo json_encode($nbPlaceMax); ?> ;
        var under_file = <?php echo json_encode($under_file); ?>;
        var lang = <?php echo json_encode($lang); ?>;
        var id_ressource = <?php echo json_encode($data['id_ressource']); ?>; // Créer les variable javascript
        var confirmer = false;
        var libelle =  <?php echo json_encode($data['libelle']); ?>;  
        var plage_horraire = <?php echo json_encode($data['plage_horraire']); ?>;
        var co = <?php echo json_encode($co); ?>;
        var code = <?php echo json_encode(__WWW__); ?>;
        var h_active = <?php echo json_encode($h_active); ?>; 
        var cache = 0;
        var nb_avant_more = parseInt(<?php echo json_encode($nb_avant_more); ?>);
        var mois_ac = <?php echo json_encode($mois_ac); ?>;
        var choix_ress = <?php echo json_encode($this->data['choix_ress']); ?>;
        var css_url = code+'/reservation/calendar_css';
        var click ='';
        var hash_change = false;     
        // Fonction gérant les horaires cliqués
        jQuery('#h_active').click(function() {
            view = $('#calendar'+id_ressource).fullCalendar('getView');
            currentDate = $('#calendar'+id_ressource).fullCalendar('getDate');
            if(this.checked) {
                h_active=true;
                $('#calendar'+id_ressource).fullCalendar('destroy');
                $('#calendar'+id_ressource).fullCalendar(
                    $.extend(fcOpts, {
                        defaultView: view.name,
                        defaultDate: currentDate,
                        eventSources: code + '/reservation/' + under_file + '?id_ressource=' 
                                           + id_ressource + '&h_active=' + h_active,
                    })
                );
            } else {
                h_active = false;
                $('#calendar'+id_ressource).fullCalendar('destroy');
                $('#calendar'+id_ressource).fullCalendar(
                    $.extend(fcOpts, {
                        defaultView: view.name,
                        defaultDate: currentDate,
                        eventSources: code + '/reservation/' + under_file + '?id_ressource='+id_ressource+'&h_active='+h_active,
                    }));
            }
        });
        var focusout = true;
        $("#head_button").click(function() {
            if (focusout == true) {
                focusout = false;
                $("#head_left").fadeOut(500);
                $("#head_right").fadeOut(500, function() {
                    $("#head").css("height", "30px");
                });
            } else {
                focusout = true;
                $("#head_left").fadeIn(500);
                $("#head_right").fadeIn(500);
                $("#head").css("height", "200px");
            }
        });

        // Fonction qui détermine si une personne n'est plus en traint de taper
        ;(function($) {
            $.fn.extend({
                donetyping: function(callback, timeout) {
                    timeout = timeout || 1e3;
                    var timeoutReference,
                        doneTyping = function(el) {
                            if (!timeoutReference) return;
                            timeoutReference = null;
                            callback.call(el);
                        }
                    return this.each(function(i, el) {
                        var $el = $(el);
                        $el.is(':input') && $el.on('keyup keypress', function(e) {
                            if (e.type=='keyup' && e.keyCode!=8) {
                                return;
                            } 
                            if (timeoutReference) {
                                clearTimeout(timeoutReference);
                            }
                            timeoutReference = setTimeout(function(){
                                doneTyping(el);
                            }, timeout);
                        });
                    });
                }
            });
        })(jQuery);

        $(document).click(function(event) { 
            var left_evt = event.pageX;
            var top_evt = event.pageY;
            var left = click.pageX;
            var top = click.pageY;
            if(!(left_evt == left) && !(top_evt == top)) {
                $('.popover').hide()
                $('.popover').html('');
            }
        });
        // Bloque s'occupant de l'envoie en ajax quand l'input du nombre de personne a fini d'être rentré 
        jQuery('#nb_recherche').donetyping(function(event) {
            view = $('#calendar'+id_ressource).fullCalendar('getView');
            var start = view.start.format("YYYY-MM-DD");
            var end_d = view.end.format("DD");
            end_d = String(parseInt(end_d)-1);
            if (end_d.length == 1) {
                end_d = '0'+end_d;
            }
            var end_m = view.end.format("MM");
            var end_y = view.end.format("YYYY");
            var end = end_y + "-" + end_m + "-" + end_d;
            var nb_recherche = parseInt(this.value);
            
            $.ajax({
                method: "POST",
                url: "numberpeople",
                data: { start_date : start, end_date : end, nb_recherche : nb_recherche, id_ressource : id_ressource },
            }).done(function(nb_place_max) {
                currentDate = $('#calendar'+id_ressource).fullCalendar('getDate');
                $('#calendar'+id_ressource).fullCalendar('destroy');
                $('#calendar'+id_ressource).fullCalendar(
                    $.extend(fcOpts, {
                        defaultView: view.name,
                        defaultDate: currentDate,
                        eventSources: code + '/reservation/' + under_file + '?id_ressource='+id_ressource+'&nb_recherche='+nb_recherche,
                    })
                );
            });

        });

        //Langue pour le calendrier
        if (lang == 'fr') {
            var buttonText = {  
                today : 'Aujourd\'hui' ,
                month : 'Mois',
                week  : 'Semaine',
                day   : 'Jour' 
            };
            var columnFormat = 'ddd D/M';
            var three_days = '3 jours';
        } else {
            var buttonText = {  
                today:    'today',
                month:    'month',
                week:     'week',
                day:      'day' 
            };
            var columnFormat = 'ddd M/D';
            var three_days = '3 days';
        }

        var source = code + '/reservation/' + under_file 
                    + '?id_ressource=' + id_ressource 
                    + '&h_active='+h_active;
        var fcOpts = {
            buttonText : buttonText,
            height : "auto",  
            defaultDate : hash_date,
            eventBackgroundColor : '#ABFFA6',
            eventColor : 'transparent',
            eventBorderColor : 'transparent',
            eventTextColor : '#000',
            eventDurationEditable : false,
            viewRender : function (view, element) {
                jQuery('.fc-left').empty();
                jQuery('.fc-left').append("<button id='all_form' class='fc-button fc-state-default' onclick='location.href=\""+code+"/reservation/all\"'>Tous </button>");
                if (co) {
                    jQuery('.fc-left').append("<button id='list_form' class='fc-button fc-state-default' onclick='location.href=\""+code+"/reservation\"'>Liste </button>");

                } else if (auth && !co) {
                    jQuery('.fc-left').append("<button id='list_form' class='fc-button fc-state-default' onclick='location.href=\""+code+"/reservation/profil\"'>Liste </button>");

                } 
            },
            columnFormat : columnFormat,
            axisFormat : 'HH:mm',
            timeFormat : 'H:mm',
            eventSources : source,
            // Affiche un gif quand il y a un chargement
            loading: function(bool) {
                if (bool) {
                    $('#loading').show();
                }
            },
            // Lorsque l'on clique sur un jour nous renvoie sur la vue adéquate
            dayClick: function(date, jsEvent, view) {
                $('.popover').html('');
                $('.popover').hide();
                var offset = $(this).offset();
                var left = jsEvent.pageX;
                var top = jsEvent.pageY;
                var theHeight = $('.popover').height();
                $('.popover').append("<input type='button' class='btn_oui' value='Voir ce jour' />");
                $('.popover').show();
                $('.popover').css('left', (left+10) + 'px');
                $('.popover').css('top', (top-(theHeight/2)-10) + 'px'); 
                jQuery('.btn_oui').click( function() {
                    jQuery('#calendar'+id_ressource).fullCalendar( 'changeView', 'agendaDay' );
                    jQuery('#calendar'+id_ressource).fullCalendar( 'gotoDate', date );
                    $('.popover').html('');
                    $('.popover').hide();
                    
                });
                click = jsEvent;
                
            },
            // Si l'on clique sur un évenement affiche la modale correspondantes
            eventRender: function (event, element) {
                    if (event.url) {
                        var config = <?php echo json_encode(Clementine::$config['module_reservation']['url_connect']); ?>;
                        if (event.url.search("choix") == -1 && event.url.search(config) == -1) {
                            videoSRC = event.url,
                            element.attr('href', 'javascript:void(0);');
                            element.click(function(jsEvent) {
                                $('.popover').html('');
                                $('.popover').hide();
                                var offset = $(this).offset();
                                var left = jsEvent.pageX;
                                var top = jsEvent.pageY;
                                var theHeight = $('.popover').height();
                                if ( event.title == 'Dispo' ) {
                                    $('.popover').append("<input type='button' class='reserver' value='Réserver' />");
                                } else if ( co ){
                                    $('.popover').append("<input type='button' class='reserver' value='Modifier' />");
                                }
                                $('.popover').show();
                                $('.popover').css('left', (left + 10) + 'px');
                                $('.popover').css('top', (top - (theHeight / 2) - 10) + 'px'); 
                                jQuery('.reserver').click( function() {
                                    // $('.popover').html('');
                                    // $('.popover').hide();
                                    var theModal = "#videoModal",
                                    videoSRC = event.url+'&is_iframe=1',
                                    videoSRCauto = videoSRC ;

                                    $(theModal + ' iframe').attr('src', videoSRCauto);
                                    $(theModal + ' button.close').click(function () {
                                        $(theModal + ' iframe').attr('src', videoSRC);
                                    });
                                    $('#Modal').trigger('click');
                                });
                                click = jsEvent;
                            });
                        }
                    }
            },  
                  
            // S'occupe de la langue
            lang : lang,
            //Si la souris survole le calendrier
            eventMouseover: function() {
                            
            },
            // Si la souris quitte le calendrier
            eventMouseout: function() {

            },
            eventLimit: mois_ac, 
            // Configuration des vues
            views: {
                agendaThreeDay: { 
                    type: 'agenda', 
                    duration: { days: 3 }, 
                    buttonText: three_days 
                }, 
                month: { 
                    eventLimit: nb_avant_more, 
                },
            },
            header : {       
                left: '',
                center: 'prev, next' ,
                right:  'agendaDay , agendaWeek , month , agendaThreeDay,agendaListView'
            },
            // Ce que doivent faire les évenements après le premier rendus.
            eventAfterRender: function(event, element, view) {  
                // jQuery('a[style="background-color:transparent;border-color:transparent;color:#000"]').remove();
                // jQuery('.fc-bg:empty').height(40);
                if (view.name == 'month') {
                    jQuery('tbody tr').css('height','100%');
                } 
                // jQuery('.full').mouseover(function() {
                // var date = event.start;
                // var _this = this;
                // var allSegs = _this.getCellSegs(event);
                // var reslicedAllSegs = _this.resliceDaySegs(allSegs, date);
                    // jQuery('#calendar'+id_ressource).showSegPopover(element,jQuery('#calendar'+id_ressource) ,reslicedAllSegs);
                // });
                
            },
            defaultView : hash,  
            eventDragStart : function( event, jsEvent, ui, view ) { 

            },
            
            // Quand le drag s'arrete, c'est à dire qu'un évenement décolle de sa place
            eventDragStop : function( event, jsEvent, ui, view ) { 
               date_start_before = event.start.format();
               date_end_before = event.end.format();
            },
            // Lorsqu'un évenement est posé
            eventDrop: function(event, delta, revertFunc) {
                if (confirm("Etes vous sûre de vouloir changer ce créneaux?")) {
                    var date = event.start.format();
                    var date1 = date.replace('T', ' ');
                    if (event.title != '...') {
                        var url = event.url;
                        var res = url.split("?");
                        var update = res[1].split("&");
                        var reservation_idreservation = update[0].split("=");
                        var ressource_idressource = update[2].split("=");
                        var id_reservation1 = reservation_idreservation[1];
                        var datas = { 
                            id_reservation : id_reservation1,
                            date : date1, 
                            id_ressource : id_ressource ,
                            time_creneaux : event.time_creneaux
                        };
                    } else {
                        date_start_before = date_start_before.replace('T', ' ');
                        var datas = { 
                            date_start_before : date_start_before, 
                            date : date1,
                            id_ressource : id_ressource ,
                            time_creneaux : event.time_creneaux
                        };  
                    }
                    $.ajax({
                        method: "POST",
                        url: code + "/updateajax/updateajax?nb_recherche="+nb_recherche.number_max,
                        data: datas,
                    }).done(function (resultat) {
                        if (resultat == 1) {
                          alert("le changement c'est bien passé");
                          jQuery('#calendar'+id_ressource).fullCalendar( 'refetchEvents' );
                        } else {
                          alert(resultat);
                        }
                        if (resultat != 1) {
                            revertFunc();
                        }               
                    }); 
                } else {
                    revertFunc();
                }

            },
            allDaySlot : false,
            eventLimitText : "autres",            
            displayEventEnd : true,
            snapDuration : <?php echo json_encode($creneaux); ?>,
            eventAfterAllRender: function(view) {
                current_Date = $('#calendar' + id_ressource).fullCalendar('getDate');
                current_Date = current_Date.format('YYYY-MM-DD');
                if (jQuery("#dtp").length) {
                    jQuery("#dtp").remove();
                    var htm = '<div class="container fc-state-default"  id="dtp" style="width:130px; border-radius: 4px;"><div class="row"><div class="col-sm-6" style="width:120px;"><input type="text" class="form-control" id="datetimepicker" style="width:100px; background-color:transparent ;background-repeat: repeat-x;"/></div></div></div>';
                    jQuery('#calendar'+id_ressource+' .fc-prev-button').after(htm);
                    jQuery(function () { 
                        $('#datetimepicker').datetimepicker({
                            defaultDate: current_Date,
                            format: 'YYYY/MM/DD',
                            widgetPositioning: { horizontal : 'left'},
                        }); 
                    });
                } else {
                    var htm = '<div class="container fc-state-default"  id="dtp" style="width:130px; border-radius: 4px;"><div class="row"><div class="col-sm-6" style="width:120px;"><input type="text" class="form-control" id="datetimepicker" style="width:100px;background-color:transparent  ;background-repeat: repeat-x;"/></div></div></div>';
                    jQuery('#calendar'+id_ressource+' .fc-prev-button').after(htm);
                    jQuery(function () { 
                        $('#datetimepicker').datetimepicker({
                            defaultDate: current_Date,
                            format: 'YYYY/MM/DD',
                            widgetPositioning: { horizontal : 'left' },
                        }); 
                    });
                }
                jQuery('#dtp').click(function () {

                    jQuery('#datetimepicker').on('dp.change', function(e) {
                        var date_select = new Date(e.date);
                        var jour_select = date_select.getUTCDate()+1;
                        if (jour_select < 10) {
                            jour_select = '0'+jour_select;
                        }
                        var mois_select = date_select.getUTCMonth()+1;
                        if (mois_select < 10) {
                            mois_select = '0'+mois_select; 
                        }
                        var annee_select = date_select.getUTCFullYear();
                        var date_select = annee_select+'/'+mois_select+'/'+jour_select;
                        if (date_select != current_Date) { 
                            $('#calendar'+id_ressource).fullCalendar('gotoDate', e.date)
                        }
                    });
                });
                if (cache == 0) {
                    cache = $('#calendar' + id_ressource).fullCalendar('getEventCache');
                }
                var evts = $('#calendar' + id_ressource).fullCalendar('clientEvents'),
                minTime = moment("2014-01-01 23:59:59").format("HH:mm:ss"),
                maxTime = moment("2014-01-01 00:00:00").format("HH:mm:ss"),
                currentDate = view.calendar.getDate(),
                currentMinTime = view.calendar.options.minTime,
                currentMaxTime = view.calendar.options.maxTime;
                var view = $('#calendar' + id_ressource).fullCalendar('getView');
                var start_y = view.start.format("YYYY");
                var start_m = view.start.format("MM");
                var start_d = view.start.format("DD");
                var y_end = view.end.format("YYYY");
                var m_end = view.end.format("MM");
                var d_end = view.end.format("DD");
                // Vérifie la totalité des vues et adapte le temps de chargement
                if (view.name == 'agendaDay') {
                    var hashtag = 'jour';
                    var date_av = start_y + '-' + start_m + '-' + String(parseInt(start_d) - 1);
                    var date_af = y_end + '-' + m_end + '-' + String(parseInt(d_end) + 1);
                } else if (view.name == 'agendaWeek') {
                    var hashtag = 'semaine';
                    var date_av = start_y + '-' + start_m + '-' + String(parseInt(start_d) - 7);
                    var date_af = y_end + '-' + m_end + '-' + String(parseInt(d_end) + 7);
                } else if (view.name == 'month') {
                    var hashtag = 'mois';
                    start_m = String(parseInt(start_m) - 1);
                    if (start_m.length == 1) {
                        start_m = '0' + start_m;
                    }
                    var date_av = start_y + '-' + start_m + '-00';
                    m_end = String(parseInt(m_end) + 1);
                    if (m_end.length == 1) {
                        m_end = '0'+m_end;
                    }
                    var date_af = y_end + '-' + m_end + '-00';
                } else if (view.name == 'agendaThreeDay') {
                    var hashtag = '3jours';
                }
                var cur_date = jQuery('#calendar' + id_ressource).fullCalendar('getDate');
                cur_date = cur_date.format('YYYY/MM/DD');
                hashtag = hashtag + '-' + cur_date;
                hash_change = true;
                
                setTimeout(function(){
                    hash_change = false;
                }, 100);
                
                var start_week = view.start.format("MMDD");
                var end_week = view.end.format("MMDD");
                var evenements = new Array();
                var snap = <?php echo json_encode($creneaux); ?>;
                var snap_base = snap;
                var snap_sec = timeToSec(snap);
                // Détermine 
                for(var i in evts) {
                    var day = evts[i].start.format("MMDD");  
                    if (day >= start_week && day < end_week) {
                        evenements.push(evts[i]);
                        minTime = timeDiff(minTime, evts[i].start.format("HH:mm:ss"), true);
                        maxTime = timeDiff(maxTime, evts[i].end.format("HH:mm:ss"), false);
                        if (snap != evts[i].time_creneaux) {
                            if (typeof evts[i].time_creneaux != 'undefined') {
                                var time_sec = timeToSec(evts[i].time_creneaux);
                                if( snap_sec > time_sec ) {
                                    var snap_sec = pgcd(snap_sec,time_sec);
                                } else {
                                    var snap_sec = pgcd(time_sec,snap_sec);
                                }
                            }
                        }
                    }
                }
                snap = secToTime(snap_sec);
                creneaux_change = false;
                new_url = css_url+'?time='+snap;
                ancien_url = jQuery('link[data-key="js_reservation_css"]').attr('href');
                if (new_url != ancien_url) {
                    jQuery('link[data-key="js_reservation_css"]').attr('href',css_url+'?time='+snap+'');
                    // creneaux_change = true ;
                }
                // Coupe les horaires en fonction du temp maximum et du temp minimum
                if (minTime != currentMinTime || maxTime != currentMaxTime || creneaux_change ) {
                    $('#calendar' + id_ressource).fullCalendar('destroy');
                    var v =  $('#calendar' + id_ressource).fullCalendar('getView');
                    $('#calendar' + id_ressource).fullCalendar(
                        $.extend(fcOpts, {
                            defaultView: view.name,
                            defaultDate: currentDate,
                            // events : event,
                            snapDuration : snap,
                            minTime: minTime,
                            maxTime: maxTime
                        })
                    );
                } else {
                    $('#loading').hide();
                    window.location.hash = hashtag;
                    if (co) {
                        var start = view.start.format("MM/DD/YYYY");
                        var end_d = view.end.format("DD");
                        end_d = String(parseInt(end_d) - 1);
                        if (end_d.length == 1) {
                            end_d = '0' + end_d;
                        }
                        var end_m = view.end.format("MM");
                        var end_y = view.end.format("YYYY");
                        var end = end_m + '/' + end_d + '/' + end_y;
                        var url_horaire = code + "/horaire/create?date_debut=" + start + "&date_fin=" + 
                                          end + "&id_ressource=" + id_ressource;
                    }
                }
            }
        };
        // Charge la totalité du calendrier dans la div calendar
        jQuery('#calendar' + id_ressource).fullCalendar(fcOpts);
        // Calcul la différence entre deux temps
        function timeDiff(time1, time2, getMin) {
            var d1 = new Date('2014-01-01T' + time1+'Z'),
                d2 = new Date('2014-01-01T' + time2+'Z');
            if (getMin) {
                return d1.getTime(d1) - d2.getTime(d2) < 0 ? time1 : time2;
            } else {
                return d1.getTime(d1) - d2.getTime(d2) > 0 ? time1 : time2;
            }
        }
        function pgcd(a, b) { // Algorithme d'Euclide  
          while (b > 0) {   
            var r = a % b;  
            a = b;  
            b = r;  
          }   
          return a;  
        }
        function timeToSec(string) {
            var res = string.split(":");
            var heure = res[0];
            var minute = res[1];
            var sec = res[2];
             if (sec != '00') {
                if (minute != '00' ) {
                    if (heure != '00') {
                        var time_seconds = heure * 3600 + minute * 60 + sec;
                    } else {
                        var time_seconds = minute * 60 + sec;
                    }
                } else {
                    if (heure != '00') {
                        var time_seconds = heure * 3600 + sec;
                    } else {
                        var time_seconds = sec;
                    }
                }
            } else {
                if (heure != '00') {
                    if (minute != '00') {
                        var time_seconds = heure * 3600 + minute * 60;
                    } else {
                        var time_seconds = heure * 3600;
                    }
                } else if (minute != '00') {
                    var time_seconds = minute * 60;
                } else {
                    var time_seconds = 0;
                }
            }
            return time_seconds;
        }
        
        function secToTime(secs)  {
            secs = Math.round(secs);
            var hours = Math.floor(secs / (60 * 60));
            var divisor_for_minutes = secs % (60 * 60);
            var minutes = Math.floor(divisor_for_minutes / 60);
            var divisor_for_seconds = divisor_for_minutes % 60;
            var seconds = Math.ceil(divisor_for_seconds);
            if (hours < 10) {
                hours = "0" + hours;
            }
            if (minutes < 10) {
                minutes = "0" + minutes;
            }
            if (seconds < 10) {
                seconds = "0" + seconds;
            }
            var obj = hours + ':' + minutes + ':' + seconds;
            return obj;
        }
        // Equivalent du in_array de php
        function inArray(needle, haystack) {
            var length = haystack.length;
            for(var i = 0; i < length; i++) {
                if(haystack[i] == needle) {
                    return true;
                }
            }
            return false;
        }
        $(window).on('hashchange', function() {
            var body_class = $('body').attr('class');
            body_class = body_class.split(' ');
            if (typeof(body_class[1]) != 'undefined') {
                if (String(body_class[1].trim()) == String('modal-open')) {
                    $(".close").trigger("click");
                }
            } else if (hash_change == false) {
                location.reload();
            } 
        });
    });  

</script>
<!-- <img src='../loader2.gif' id='loading' style='z-index:9; position:fixed; top:50% ; left:50% '   alt='loading' height='30' width='200' > -->

<div class="popover">
</div>
