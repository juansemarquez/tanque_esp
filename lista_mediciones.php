<?php
$archivo_mediciones = 'cliente/mediciones.csv';

$a = fopen($archivo_mediciones, "r");
if ($a === false) {
    die("ERROR al abrir el archivo $archivo_mediciones");
}
$datos = [];
$linea = 0;
while ($data = fgetcsv($a, 100, ",")) {
    $datos[] = $data;
}
fclose($a);
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <title>Listado de mediciones</title>
    </head>
    <body>
        <h1>Listado de mediciones</h1>
<table border="1" style="text-align: center;">
    <tr><th>Fecha</th><th>Hora</th><th>Distancia</th><th>Porcentaje</th></tr>
<?php
foreach($datos as $linea) {
    echo '<tr>';
    echo '<td>' . trim($linea[0], "'") . '</td>';
    echo '<td>' . substr(trim($linea[1], "'"),0,8) . '</td>';
    echo '<td>' . $linea[2] . '</td>';
    echo '<td>' . $linea[3] . '%</td>';
    echo '</tr>' . PHP_EOL;
}?>
</table>
    </body>
</html>
