<?php
header("Content-type: text/css");
$ressource_mdl = $this->getModel('ressource');
$helper = $this->getHelper('fullcalendarresa');
// Calcul d'un créneaux pour les médias queries
if (isset($_GET['time'])) {
    $info_taille = $_GET['time'];
} else {
    $info_taille = '00:10:00';
}
$sec = $helper->timeToSecond($info_taille);
$pix = (3600 / $sec) * 40;
$pix_600 = (3600 / $sec) * 35;
$pix_880 = (3600 / $sec) * 30;
$pix_1200 = (3600 / $sec) * 20;
?>
.full { 
    background-color : red !important; 
} 
.occupe { 
    background-color : orange !important; 
}
.dispo{ 
    background-color : green !important;
}
.listeOrange { 
    background-color : orange !important;
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
tbody tr{
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
    tbody tr{
        height:<?php
echo $pix_600; ?>px;
    } 
}    
@media (min-width: 768px) { 
    h2 {
    font-size : 100%;
    width:250px;
    }
    .fc-center {
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
}
@media (min-width: 880px) {
    h2 {
    font-size : 100%;
    width:100px;
    }
    .fc-center {
        width :400px;
    }
    tbody tr{
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

@media (min-width: 1200px) {
    h2 {
    margin-bottom: 100px;
    font-size : 120%;
    width:250px;
    }
    .fc-center {
        width :350px;
    }
    tbody tr{
        height:<?php
echo $pix_1200; ?>px;
    }
}
.fc-time-grid-event{
    height:auto;
}


