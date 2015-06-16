<?php
header('Cache-Control: no-cache, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Content-type: application/json');
$array;
if (!empty($data['has_horaire'])) {
    $array = array(
        'has_horaire' => $data['has_horaire'],
        'number_max' => $data['number_max']
    );
} else {
    $array = array(
        'number_max' => $data['number_max']
    );
}
echo json_encode($array);
