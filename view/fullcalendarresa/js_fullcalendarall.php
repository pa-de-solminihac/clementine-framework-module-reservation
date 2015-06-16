<?php
// Déclaration des variables utile à la création d'un calendrier
$ressource = $data['timeline_ressource'];
$ressource_name = 'Ressources';
if (!empty(clementine::$config['module_fullcalendar']['ressource'])) {
    $ressource_name = clementine::$config['module_fullcalendar']['ressource'];
}
$creneaux = '00:30:00';
$user_mdl = $this->getModel('users');
$privileges = array(
    'gerer_reservation' => true
);
$co = true;
if (!$user_mdl->hasPrivilege($privileges)) {
    $co = false;
}
$auth = $user_mdl->getAuth();
?>
<script>
$(document).ready(function() {
    var auth = <?php echo json_encode($auth); ?>; 
    var ressource = <?php echo json_encode($ressource); ?>;
    var code = <?php echo json_encode(__WWW__); ?>;
    var ressource_name = <?php echo json_encode($ressource_name); ?>;
    var choix_ress = <?php echo json_encode($this->data['choix_ress']); ?>;
    var co = <?php echo json_encode($co); ?>;
    var click = '';
    // Fonction donetyping, qui détermine quand un utilisateur a finis de taper
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
        if(!(left_evt == left) || !(top_evt == top)) {
            $('.popover').hide()
            $('.popover').html('');
        }
    });
    // Lorsqu'un utilisateur a finis de taper dans l'input nb_recherche appele cette fonction
    jQuery('#nb_recherche').donetyping(function(event) {
        view = $('#calendar').fullCalendar('getView');
        var start = view.start.format("YYYY-MM-DD");
        var end_d = view.end.format("DD");
        end_d = String(parseInt(end_d) - 1);
        if (end_d.length == 1) {
            end_d = '0' + end_d;
        }
        var end_m = view.end.format("MM");
        var end_y = view.end.format("YYYY");
        var end = end_y + "-" + end_m + "-" + end_d;
        var nb_recherche = parseInt(this.value);            
        $.ajax({
            method: "POST",
            url: "numberpeople",
            data: { 
                start_date : start, 
                end_date : end, 
                nb_recherche : nb_recherche, 
                id_ressource : -1 
            },
        }).done(function(nb_place_max) {
            currentDate = $('#calendar').fullCalendar('getDate');
            $('#calendar').fullCalendar('destroy');
            $('#calendar').fullCalendar(
                $.extend(fcOpts, {
                    defaultView: view.name,
                    defaultDate: currentDate,
                    events: code + '/reservation/all?nb_recherche='+nb_recherche,
                })
            );
        });
    });
    var buttonText = {  
        day : 'Jour' ,
    };
    var fcOpts = {
        height: "auto",
        timeFormat: 'H:mm',
        aspectRatio: 1.8,
        slotWidth : '50',
        scrollTime: '00:00',
        header: {
            left: '',
            center: 'prev,next',
            right: 'timelineDay,timelineThreeDays'
        },
        minTime: '12:00',
        maxTime: '14:00',
        defaultView: 'timelineDay',
        views: {
            timelineThreeDays: {
                type: 'timeline',
                duration: { days: 3 },
                buttonText: ' 3 jours',
            }
        },
        loading: function(bool) {
            if (bool) {
                $('#loading').show();
            }
        },
        viewRender : function (view, element) {
            jQuery('.fc-left').empty();
            if (co) {
                jQuery('.fc-left').append("<button id='list_form' class='fc-button fc-state-default' onclick='location.href=\""+code+"/reservation\"'>Liste </button>");
            } else if (auth && !co) {
                jQuery('.fc-left').append("<button id='list_form' class='fc-button fc-state-default' onclick='location.href=\""+code+"/reservation/profil\"'>Liste </button>");
            }
        },
        resourceAreaWidth: '25%',
        resourceLabelText: ressource_name,
        resources: ressource,
        events: code + '/reservation/all',
        eventClick: function(calEvent, jsEvent, view) {
            if (event.url) {
                alert(event.url);
            }
        },
        eventDurationEditable: false,
        // dayClick: function(date, jsEvent, view) {
                // $('.popover').html('');
                // $('.popover').hide();
                // var offset = $(this).offset();
                // var left = jsEvent.pageX;
                // var top = jsEvent.pageY;
                // var theHeight = $('.popover').height();
                // $('.popover').append("<input type='button' class='btn_oui' value='"+date.format('DD/MM/YYYY')+"' />");
                // $('.popover').show();
                // $('.popover').css('left', (left+10) + 'px');
                // $('.popover').css('top', (top-(theHeight/2)-10) + 'px'); 
                // jQuery('.btn_oui').click( function() {
                    // jQuery('#calendar').fullCalendar( 'changeView', 'timelineDay' );
                    // jQuery('#calendar').fullCalendar( 'gotoDate', date );
                    // $('.popover').html('');
                    // $('.popover').hide();
                    
                // });
                // click = jsEvent;
            
        // },
       
        buttonText : buttonText,
        eventRender: function (event, element) {
            if (event.url) {
                videoSRC = event.url,
                element.attr('href', 'javascript:void(0);');
                element.click(function(jsEvent) {
                    $('.popover').html('');
                    $('.popover').hide();
                    var offset = $(this).offset();
                    var left = jsEvent.pageX;
                    var top = jsEvent.pageY;
                    var theHeight = $('.popover').height();
                    if (event.title == 'Dispo') {
                        $('.popover').append("<input type='button' class='reserver' value='Réserver' />");
                    } else if (co){
                        $('.popover').append("<input type='button' class='reserver' value='Modifier' />");
                    }
                    $('.popover').show();
                    $('.popover').css('left', (left+10) + 'px');
                    $('.popover').css('top', (top-(theHeight/2)-10) + 'px'); 
                    jQuery('.reserver').click(function() {
                        $('.popover').html('');
                        $('.popover').hide();
                        var theModal = "#videoModal",
                        videoSRC = event.url+'&is_iframe=1',
                        videoSRCauto = videoSRC;
                        $(theModal + ' iframe').attr('src', videoSRCauto);
                        $(theModal + ' button.close').click(function () {
                            $(theModal + ' iframe').attr('src', videoSRC);
                        });
                        $('#Modal').trigger('click');
                    });
                    click = jsEvent;
                });
            }
        },  
        eventAfterAllRender: function(view) {
            jQuery('.fc-resource-area').css('width','100px');
            current_Date = $('#calendar').fullCalendar('getDate');
            current_Date = current_Date.format('YYYY-MM-DD');
            if (jQuery("#dtp").length) {
                jQuery("#dtp").remove();
                var htm = '<div class="container fc-state-default"  id="dtp" style="width:130px; border-radius: 4px;"><div class="row"><div class="col-sm-6" style="width:120px;"><input type="text" class="form-control" id="datetimepicker" style="width:100px; background-color:transparent ;background-repeat: repeat-x;"/></div></div></div>';
                jQuery('#calendar .fc-prev-button').after(htm);
                jQuery(function () { $('#datetimepicker').datetimepicker({
                        defaultDate: current_Date,
                        format: 'YYYY/MM/DD',
                        widgetPositioning: { horizontal : 'left'},
                        });
                    });
            } else {
                var htm = '<div class="container fc-state-default"  id="dtp" style="width:130px; border-radius: 4px;"><div class="row"><div class="col-sm-6" style="width:120px;"><input type="text" class="form-control" id="datetimepicker" style="width:100px;background-color:transparent  ;background-repeat: repeat-x;"/></div></div></div>';
                jQuery('#calendar .fc-prev-button').after(htm);
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
                    if (jour_select < 10 ) {
                        jour_select = '0'+jour_select;
                    }
                    var mois_select = date_select.getUTCMonth()+1;
                    if (mois_select < 10) {
                        mois_select = '0'+mois_select; 
                    }
                    var annee_select = date_select.getUTCFullYear();
                    var date_select = annee_select+'/'+mois_select+'/'+jour_select;
                    if (date_select != current_Date) { 
                        $('#calendar').fullCalendar('gotoDate', e.date)
                    } 
                });
            });
            var evts = $('#calendar').fullCalendar('clientEvents'),
            minTime = moment("2014-01-01 23:59:59").format("HH:mm:ss"),
            maxTime = moment("2014-01-01 00:00:00").format("HH:mm:ss"),
            currentDate = view.calendar.getDate(),
            currentMinTime = view.calendar.options.minTime,
            currentMaxTime = view.calendar.options.maxTime;
            var view = $('#calendar').fullCalendar('getView');
            var start_y = view.start.format("YYYY");
            var start_m = view.start.format("MM");
            var start_d = view.start.format("DD");
            var y_end = view.end.format("YYYY");
            var m_end = view.end.format("MM");
            var d_end = view.end.format("DD");
            var start_week = view.start.format("MMDD");
            var end_week = view.end.format("MMDD");
            var evenements = new Array();
            var snap = <?php echo json_encode($creneaux); ?>;
            var snap_sec = timeToSec(snap);
                // Détermine 
            for(var i in evts) {
                var day = evts[i].start.format("MMDD");  
                if (day >= start_week && day < end_week) {
                    evenements.push(evts[i]);
                    minTime = timeDiff(minTime, evts[i].start.format("HH:mm:ss"), true);
                    maxTime = timeDiff(maxTime, evts[i].end.format("HH:mm:ss"), false);
                    if ( snap != evts[i].time_creneaux ) { 
                        if (typeof evts[i].time_creneaux != 'undefined') {
                            var time_sec = timeToSec(evts[i].time_creneaux);
                            if(snap_sec > time_sec) {
                                var snap_sec = pgcd(snap_sec,time_sec);
                            } else {
                                var snap_sec = pgcd(time_sec,snap_sec);
                            }
                        }
                    }
                }
            }
            if ( evts.length < 1 ) {
                minTime = '12:00';
                maxTime = '12:10';
            }
            if ((minTime != currentMinTime || maxTime != currentMaxTime)) {
                $('#calendar').fullCalendar('destroy');
                var v = $('#calendar').fullCalendar('getView');
                $('#calendar').fullCalendar(
                    $.extend(fcOpts, {
                        defaultView: view.name,
                        defaultDate: currentDate,
                        minTime: minTime,
                        maxTime: maxTime,
                    })
                );
            } else {
                $('#loading').hide();
            }
        }		
    };
// Met le traitement précedent dans la div ayant pour id calendar
    jQuery('#calendar').fullCalendar(fcOpts);
    if (co) {
        jQuery('#page-wrapper').prepend('<button id="hide" ><<</button>'),
        jQuery('#hide').css('position','absolute');
        jQuery('#hide').css('width','30px');
        jQuery('#hide').css('top','65px');
        jQuery('#hide').css('left','260px');
        var nav_activ = true
        jQuery('#hide').click(function() {
            if (nav_activ) {
                jQuery('#page-wrapper').css('margin','0 0 0 5px');
                jQuery('.sidebar').hide();
                jQuery('#hide').css('left','5px');
                jQuery('#hide').html('>>');
                nav_activ = false ;
            } else {
                jQuery('#page-wrapper').css('margin','0 0 0 250px');
                jQuery('.sidebar').show();
                jQuery('#hide').css('left','260px');
                jQuery('#hide').html('<<');
                nav_activ = true;
            }
        });
    }
// timeDiff calcule la différence entre deux date
    function timeDiff(time1, time2, getMin) {
        var d1 = new Date('2014-01-01T' + time1+'Z'),
            d2 = new Date('2014-01-01T' + time2+'Z');
        if (getMin) {
            return d1.getTime(d1) - d2.getTime(d2) < 0 ? time1 : time2;
        } else {
            return d1.getTime(d1) - d2.getTime(d2) > 0 ? time1 : time2;
        }
    }
});

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
        if (minute != '00') {
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
</script>
<style>

.fc-content {
    height : 100%;
}
@media (max-width: 767px) { 
    #hide {
        display : none;
    }
}
.fc-timelineDay-button {
    margin-right:10px !important;
    margin-left: 10px !important;
}

#list_form, .fc-timelineDay-button, .fc-timelineThreeDays-button {
    border-radius : 5px !important;
    height: 30px !important;
}
#calendar {
    margin-left : -28px !important;
    margin-right : -23px !important;
}
input[type="date"]::-webkit-clear-button {
    display: none;
}
.popover {
    position:absolute;
    display:none;
    background:#fff;
    border: 1px solid #999;
    padding:10px;
    width:auto;
    box-shadow:0 0 10px rgba(0, 0, 0, .5);
    color:black;
}
.popover:after, .popover:before {
    right: 100%;
    border: solid transparent;
    content:" ";
    height: 0;
    width: 0;
    position: absolute;
    pointer-events: none;
}
.fc-body {
    height: 100px !important;
}
.popover:after {
    border-color: rgba(255, 255, 255, 0);
    border-right-color: #ffffff;
    border-width: 10px;
    top: 50%;
    margin-top: -10px;
}
.popover:before {
    border-color: rgba(201, 201, 201, 0);
    border-right-color: #c9c9c9;
    border-width: 11px;
    top: 50%;
    margin-top: -11px;
}

#dtp {
    margin-left: 10px !important;
}

.fc-prev-button, .fc-next-button {
    height : 30px !important;
}
.fc-center {
    width : 200px !important;
}
.fc-next-button {
    position : relative !important;
    top : -30px !important;
    margin-left : 190px !important;
}

body {
	margin: 0;
	padding: 0;
	font-family: "Lucida Grande",Helvetica,Arial,Verdana,sans-serif;
	font-size: 14px;
}
#top {
	background: #eee;
	border-bottom: 1px solid #ddd;
	padding: 8px 10px;
	font-size: 12px;
	font-weight: bold;
	font-weight: bold;
	text-align: center;
}

.fc-content {
    height: 100%;
}

.full {
    background-color:red !important;
}
.occupe {
    background-color:orange !important;
}
.dispo{
    background-color:green !important;
}
.listeOrange {
    background-color: orange !important;
}

@media (max-width: 768px) { 
    #calendar {
        margin-left : -8px !important;
        margin-right : -13px !important;
    }
}
</style>
<?php

?>
<div class="popover">
</div>
