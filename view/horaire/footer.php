<?php
$this->getBlock('design/footer-admin', $data, $request);
if ($data['formtype'] == 'update' && isset($request->GET['has_horaire'])) {
?>
<script type="text/javascript">
	if (jQuery("#videoModal").hasClass("in")) {
		jQuery('.is-an-iframe').hide();
		jQuery('.navbar').hide();
	}
	alert('Impossible de supprimer cette horaire il y a des réservations');	
</script>
<?php
}
?>
<script type="text/javascript">
	$('#clementine_reservation_horaire-to_add').change(function() {
		if ($('#clementine_reservation_horaire-to_add option:selected').val() == -1) {
			$("#clementine_reservation_horaire-maximum_number_place").hide();
			$("label[for=\"clementine_reservation_horaire-maximum_number_place\"]").hide();
			$("#clementine_reservation_horaire-maximum_number_place_by_reservation").hide();
			$("label[for=\"clementine_reservation_horaire-maximum_number_place_by_reservation\"").hide();
			$("#clementine_reservation_horaire-time_creneaux").hide();
			$("label[for=\"clementine_reservation_horaire-time_creneaux\"").hide();
		} else {
			$("#clementine_reservation_horaire-maximum_number_place").show();
			$("label[for=\"clementine_reservation_horaire-maximum_number_place\"]").show();
			$("#clementine_reservation_horaire-maximum_number_place_by_reservation").show();
			$("label[for=\"clementine_reservation_horaire-maximum_number_place_by_reservation\"").show();
			$("#clementine_reservation_horaire-time_creneaux").show();
			$("label[for=\"clementine_reservation_horaire-time_creneaux\"").show();
		}
	});
</script>
<?php
$this->getBlock('horaire/footer_content', $data, $request);
