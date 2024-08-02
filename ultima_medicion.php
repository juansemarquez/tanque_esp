<?php
require_once 'Tanque.php';

$archivo_mediciones = 'cliente/mediciones.csv';

$t = new Tanque($archivo_mediciones);
$medicion = $t->get_ultima_medicion();

header('Content-Type: application/json; charset=utf-8');
echo json_encode($medicion);
