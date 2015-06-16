<?php
$ressource_mdl = $this->getModel('ressource');
$helper = $this->getHelper('fullcalendarresa');
// Calcul d'un créneaux pour les médias queries
$info_taille = '00:10:00';
$sec = $helper->timeToSecond($info_taille);
$pix = (3600 / $sec) * 40;
$pix_600 = (3600 / $sec) * 35;
$pix_880 = (3600 / $sec) * 30;
$pix_1200 = (3600 / $sec) * 20;
?>

<style>

#list_form, #all_form {
    border-radius : 5px !important;
    height: 30px !important;
}

input[type="date"]::-webkit-clear-button {
 -webkit-appearance: none;
  display: none;
}

.fc-title {
    position:absolute !important ; 
    top:10px !important; 
    left : 10px !important
}

.fc-body table {
    height:100px !important;
}
.fc-content{
    height : 30px;
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

.fc-prev-button, .fc-next-button {
    height : 30px !important;
}
.fc-center {
   width : 300px !important;
}
.fc-next-button {

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

h2 {
    font-size : 150%;
    width:60%;
    }
    .fc-center {
        width :400px;
    }
    .fc-agendaWeek-button {
        width:48px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .fc-agendaThreeDay-button{
        width:48px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .fc-today-button{
        width:65px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
.fc-view-container,.fc-toolbar{
    width:100%;
}
.fc-body tr{
    height:<?php
echo $pix; ?>px;
}
h1 {
    text-align:center;
}

@media (min-width: 600px) {
    h2 {
    font-size : 100%;
    width:100px;
    }
    .fc-center {
        width :400px;
    }
    .fc-body tr{
        height:<?php
echo $pix_600; ?>px;
    } 
}    
.fc-center{
    }
@media (min-width: 768px) { 
    h2 {
    font-size : 100%;
    width:250px;
    }
    .fc-center {
        width :400px;
        width :400px;
    }
    .fc-agendaWeek-button {
        width:auto;
        overflow: visible;
    }
    .fc-agendaThreeDay-button{
        width:auto;
        overflow: visible;
    }
    .fc-today-button{
        width:auto;
        overflow: visible;
    }
    .fc-center{
    }
}
@media (min-width: 880px) {
    h2 {
    font-size : 100%;
    width:100px;
    }
    .fc-center {
        width :400px;
    }
    .fc-body tr{
        height:<?php
echo $pix_880; ?>px;
    }

@media (min-width: 992px) { 
    h2 {
    font-size : 100%;
    width:200px;
    }
    .fc-center {
        width :300px;
    }

}
@media (min-width: 1020px) { 
    .fc-center {
    }

}
@media (min-width: 1200px) {
    h2 {
    margin-bottom: 100px;
    font-size : 120%;
    width:250px;
    }
    .fc-center {
        width :350px;
    }
    .fc-body tr{
        height:<?php
echo $pix_1200; ?>px;
    }
}
.fc-time-grid-event{
    height:auto;
}

</style>

