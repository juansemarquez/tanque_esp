# Medición de tanque de agua

Este proyecto muestra cómo obtuvimos una medición automatizada de la cantidad de
agua que hay en el tanque de nuestra casa.

## Sensor

_Completar con especificaciones de hardware_

## Placa ESP-8266

_Completar con especificaciones de hardware_

### Código

Ver el archivo `esp8266/tanque_esp.ino`.

Se debe modificar las siguientes líneas:

```c
const char* ssid = "ID-DE-LA-RED";
const char* password = "CLAVE-DE-LA-RED";
```

Tomamos este código de un video en youtube, que no pudimos volver a encontrar.
Sobre esta base, realizamos algunas modificaciones.

Conviene que la placa ESP-8266 tenga ip estática dentro de la red, para que
los scripts que siguen a continuación la "encuentren" en la red local.

## Raspberry Pi: Código python

En una raspberry pi que tenemos prendida 24hs, tenemos el código incluido en
`cliente/cliente.py`. 

Para esto, debemos instalar la dependencia `websocket-client`, con el comando:

```
pip install websocket-client
```

En ese archivo, debemos modificar las siguientes líneas

```python
    # Archivo que contendrá las mediciones del sensor:
    archivo_mediciones = "$HOME/tanque_esp/cliente/mediciones.csv"

    # Distancia desde el sensor hasta la superficie del agua cuando el tanque
    # está lleno y cuando está vacío.
    tanque_lleno = 20
    total_tanque = 100
    # Cambiar por la IP y el puerto que corresponda a la placa ESP.
    url_websocket = "ws://192.168.17.100:81"

    # Sobre el final del archivo:

    # Cantidad de mediciones que toma el sensor en cada ejecución:
    cantidad_mediciones = 10 # Podemos cambiar este valor por el que juzguemos conveniente.
```

## Sitio Web

Los archivos `historico.php`, `index.html`, `mostrar_historico.php`, `style.css`, `Tanque.php` y `ultima_medicion.php`. Debemos realizar los siguientes cambios:

En los archivos `mostrar_historico.php` y `ultima_medicion.php`:

```php
$archivo_mediciones = 'cliente/mediciones.csv';
```

Este sitio debe estar en un servidor web (Apache, nginx, phpcli), etc.
Otra posibilidad es hacer `git push` del archivo de mediciones cada cierto
tiempo, hacia un repositorio en la web que contenga este sitio. De este modo,
podremos consultar las mediciones a través de internet.

## Rotar logs

En el archivo `rotar_logs/rotar_logs.sh`, hay un script para que el archivo de
mediciones tenga siempre los datos de ayer y de hoy. Los datos de días
anteriores se copian a archivos aparte, que llevan en su nombre la fecha a la
que corresponden.

De esta manera evitamos que, luego de muchos días de ejecución el archivo de
mediciones se vuelva demasiado grande, lo que dificulta su manipulación.

En este archivo cambiaremos la carpeta donde está el proyecto, en la línea que
dice:

```bash
raiz_del_proyecto=$HOME/tanque_esp
```

## Cron

Luego, programaremos este script para que corra con `cron` cada noche a las
23:59hs. Para ello, escribimos el comando `crontab -e`, y allí agregamos:

```
59 23 * * * /ruta/hacia/el/archivo/rotar_logs/rotar_logs.sh
*/3 * * * * python3 /ruta/hacia/el/archivo/cliente/cliente.py
```

La primera de las líneas rota los logs, la segunda toma las mediciones del
sensor permanentemente, cada 3 minutos. En ambos casos, se debe editar la parte
que dice `/ruta/hacia/el/archivo`, reemplazandola por una ruta existente.



