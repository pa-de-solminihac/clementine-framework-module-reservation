<?php
$cssjs = $this->getModel('cssjs');
$cssjs->register_css('valid_create_reservation_css', array(
    'src' => __WWW_ROOT_RESERVATION__ . '/skin/css/clementine_reservation.css'
));
if (!$request->AJAX && empty($data['is_iframe']) && empty($data['hidden_sections']['footer'])) {
    $this->getBlock('design/header-admin', $data, $request);
    $this->getBlock('horaire/header_content', $data, $request);
}

