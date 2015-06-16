<?php
$dispo = clementine::$config['module_fullcalendar']['dispo'];
$incomplet = clementine::$config['module_fullcalendar']['incomplet'];
$full = clementine::$config['module_fullcalendar']['complet'];
$lang = clementine::$config['module_fullcalendar']['lang'];
if ($lang == 'fr') {
    $title_dispo = 'Dispo';
    $title_incomplet = 'Occupé';
    $title_full = 'Plein';
    $title_list = '... (liste)';
} else {
    $title_dispo = 'Available';
    $title_incomplet = 'Incomplete';
    $title_full = 'full';
    $title_list = '... (list)';
}
$user = $this->getModel('users');
$privileges = array(
    'gerer_reservation' => true
);
$admin = $user->hasPrivilege($privileges);
?>

    <a id="dispo" class="fc-time-grid-event fc-v-event fc-event fc-start fc-end fc-short dispo legende" style="border-color: green; color: rgb(0, 0, 0);  z-index: 0; background-color:<?php
echo $dispo; ?>;">
 		<div class="fc-content">
 			<div class="fc-time" data-start="9:00" data-full="09:00 - 09:30">
 				<span>
 					9:00 - 9:30
 				</span>
 			</div>
 			<div class="fc-title">
 				<?php
echo $title_dispo; ?>
 			</div>
 		</div>
 		<div class="fc-bg" style="height: 40px; width: 100%;" ></div>
 	</a>
 <?php
if ($admin) {
?>
	<a id="incomplet" class="fc-time-grid-event fc-v-event fc-event fc-start fc-end fc-short occupe legende" style="border-color: green; color: rgb(0, 0, 0); z-index: 0; background-color: <?php
    echo $incomplet; ?>;">
	    <div class="fc-content">
	    	<div class="fc-time" data-start="9:00" data-full="09:00 - 09:30">
	    		<span>
	    			9:00 - 9:30
	    		</span>
	    	</div>
	    	<div class="fc-title">
	    		<?php
    echo $title_incomplet; ?>
	    	</div>
	    </div>
	    <div class="fc-bg" style="height: 40px; width: 100%; "></div>
	</a>
<?php
}
?>
	<a id="full" 
	   class="fc-time-grid-event fc-v-event fc-event fc-start fc-end fc-short full legende" 
	   style="border-color: green; color: rgb(0, 0, 0); z-index: 0; background-color: <?php
echo $full; ?>;">
	   	<div class="fc-content">
	   		<div class="fc-time" data-start="9:00" data-full="09:00 - 09:30">
	   			<span>
	   				9:00 - 9:30
	   			</span>
	   		</div>
	   		<div class="fc-title">
	   			<?php
echo $title_full; ?>
	   		</div>
	   	</div>
	   	<div class="fc-bg" style="height: 40px; width: 100%; "></div>
	</a>
<?php
if ($admin) {
?>
	<a  id="list"
	    class="fc-time-grid-event fc-v-event fc-event fc-start fc-end fc-short list legende" 
	    style="border-color: green; color: rgb(0, 0, 0); 
	    	   z-index: 0; background-color: <?php
    echo $full; ?>;">
	   	<div class="fc-content">
	   		<div class="fc-time" data-start="9:00" data-full="09:00 - 09:30">
	   			<span>
	   				9:00 - 9:30
	   			</span>
	   		</div>
	   		<div class="fc-title">
	   			<?php
    echo $title_list; ?>
	   		</div>
	   	</div>
	   	<div class="fc-bg" style="height: 40px; width: 100%; ">
	 	</div>
	</a>
<?php
}

