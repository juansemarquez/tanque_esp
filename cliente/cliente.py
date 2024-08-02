import websocket
import time
import datetime

def medir_tanque():
    '''Esta función corre cada tantos minutos (croneada), para escribir las
    mediciones que arroja el sensor en un archivo csv, para que luego sea
    utilizado por la aplicación PHP'''

    # Archivo que contendrá las mediciones del sensor:
    archivo_mediciones = "$HOME/tanque_esp/cliente/mediciones.csv"

    # Distancia desde el sensor hasta la superficie del agua cuando el tanque
    # está lleno y cuando está vacío.
    tanque_lleno = 20
    total_tanque = 100
    # Cambiar por la IP y el puerto que corresponda a la placa ESP.
    url_websocket = "ws://192.168.17.100:81"

    # La dependencia de websocket es el paquete websocket-client
    ws = websocket.WebSocket()
    ws.connect(url_websocket, origin="")
    distancia = ws.recv()
    ws.close()
    
    distancia = int(distancia)
    altura_agua = total_tanque - distancia
    porcentaje = 100 * altura_agua / (total_tanque-tanque_lleno)
    if porcentaje > 100:
        porcentaje = 100
    elif porcentaje < 0:
        porcentaje = 0
    else:
        porcentaje = int(porcentaje)

    ahora = datetime.datetime.now()
    fecha = str(ahora.date())
    hora = str(ahora.time())

    string_csv = "'" + fecha + "','" + hora + "',"
    string_csv = string_csv + str(distancia) + "," + str(porcentaje) + "\n"

    archivo = open(archivo_mediciones, "a")
    archivo.write(string_csv)
    archivo.close()

if __name__ == "__main__":
    # Cantidad de mediciones que toma el sensor en cada ejecución:
    cantidad_mediciones = 10

    i = 0
    while(i < cantidad_mediciones):
        medir_tanque()
        time.sleep(1)
        i += 1

