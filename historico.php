<?php
$h = is_numeric($_GET['horas']) ? $_GET['horas'] : 24;
?><!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width">
        <title>Tanque de agua</title>
        <link rel="stylesheet" href="style.css">
    </head>
    <body>
      <div id="container-historico">
        <h1>Tanque de agua</h1>
        <div>
            <canvas id="historico"></canvas>
        </div>
        <div id="formulario"><form>
            Mostrar datos de las últimas <input type="number" style="width: 3em" name="horas" value="<?php echo $h;?>"> horas.
            <input type="submit" value="Mostrar">  
        </form></div>
        <div id="volver"><p><a href="index.html">Volver</a></p></div>
      </div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function(event) { 
    const url = "mostrar_historico.php?horas=<?php echo $h;?>"
    fetch(url)
        .then(respuesta => respuesta.json())
        .then(datos => {estructurar_grafico(datos)})
        .catch(error => {console.log(error);});
});

function estructurar_grafico(datos)
{
    const ctx = document.querySelector('#historico');
    let data = { datasets: [{
            'label' : "Porcentaje de agua en el tanque",
            'data' : datos,
        }]
    };
    const config = {
        type: 'line',
        data: data,
        options: {
            elements: {
                line: {
                    tension : 0.2  // smooth lines
                },
            },
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Histórico de uso de agua'
                }
            }
        },
    };
    new Chart(ctx, config);
}

</script>
    </body>
</html>
