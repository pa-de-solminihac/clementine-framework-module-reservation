<?php
// Laisse la possibilité d'un choix le client peut passer la réservation connecté ou non
$this->getBlock('reservation/header');
?>
	<div class="bouton_redirection">
		<a class="redirection" href="<?php
echo __WWW__ . clementine::$config['module_reservation']['url_connect'] . '?start_date=' . $request->GET['start_date'] . '&clementine_reservation_ressource-id=' . $request->GET['clementine_reservation_ressource-id'] . '&nb_recherche=' . $request->GET['nb_recherche']; ?>">Connectez-Vous</a>
		<a class="redirection" href="<?php
echo __WWW__ . '/reservation/create?start_date=' . $request->GET['start_date'] . '&clementine_reservation_ressource-id=' . $request->GET['clementine_reservation_ressource-id'] . '&nb_recherche=' . $request->GET['nb_recherche']; ?>">Reservez sans ce connecter</a>
	</div>
<?php
$this->getBlock('reservation/footer');
