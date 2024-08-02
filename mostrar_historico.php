<?php
require_once 'Tanque.php';

$archivo_mediciones = 'cliente/mediciones.csv';

$h = is_numeric($_GET['horas']) ? $_GET['horas'] : 24;

$t = new Tanque($archivo_mediciones);
$hora_limite = $t->calcular_horario_hace($h);
$datos = $t->datos_para_historico($hora_limite);
$graficar = $t->preparar_datos_para_grafico($datos);

header('Content-Type: application/json; charset=utf-8');
echo json_encode($graficar);
