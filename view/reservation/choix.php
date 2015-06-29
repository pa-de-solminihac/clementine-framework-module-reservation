<?php
// Laisse la possibilité d'un choix le client peut passer la réservation connecté ou non
$this->getBlock('reservation/header');
?>
    <div class="bouton_redirection">
        <a class="redirection" href="<?php
echo __WWW__ . Clementine::$config['module_reservation']['url_connect'] . '?start_date=' . $request->get('string', 'start_date') . '&clementine_reservation_ressource-id=' . $request->get('int', 'clementine_reservation_ressource-id') . '&nb_recherche=' . $request->get('int', 'nb_recherche'); ?>">Connectez-Vous</a>
        <a class="redirection" href="<?php
echo __WWW__ . '/reservation/create?start_date=' . $request->get('string', 'start_date') . '&clementine_reservation_ressource-id=' . $request->get('int', 'clementine_reservation_ressource-id') . '&nb_recherche=' . $request->get('int', 'nb_recherche'); ?>">Reservez sans ce connecter</a>
    </div>
<?php
$this->getBlock('reservation/footer');
