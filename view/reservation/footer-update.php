<?php
// Envoie de mail si on le désire
if (!$request->AJAX && Clementine::$config['mail']['send'] == 1) {
?>
<script type="text/javascript">
	jQuery('form').on('submit', function(e)) {
		if(confirm("Voulez vous envoyez un mail")) {
			<?php
    $data['send'] = true;
?>
		}	
		
	}
</script>
<?php
}
$this->getParentBlock($data, $request);
?>
<style>
    #wrapper{
          margin-bottom: 100px;
    }
</style>
