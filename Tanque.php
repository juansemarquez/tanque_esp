<?php

class Tanque
{
    public $archivo;
    public $cantidad_ultimas;
    public $maximo_plazo;
    
    /**
     * Constructor
     *
     * @param string $archivo_medicion Archivo donde se encuentran las mediciones
     * @param int    $cantidad_ultimas Cuántas mediciones hacia atrás se toman
     *                                 para calcular la medición actual
     * @param int    $plazo            Las mediciones que tengan más minutos de
     *                                 antigüedad que este valor, no se tienen
     *                                 en cuenta para la medición puntual.
     */ 
    public function __construct($archivo_medicion, $cantidad_ultimas = 50, $plazo = 10) {
        $this->archivo = $archivo_medicion;
        $this->cantidad_ultimas = (int) $cantidad_ultimas;
        $this->maximo_plazo = (int) $plazo;
    }


    /**
     * Toma el promedio de las últimas mediciones y las promedia. Luego elimina
     * las que estén a una distancia mayor del 10% del promedio, y vuelve a
     * promediar las que quedaron.
     *
     * @return Array [
     *              'porcentaje' => El porcentaje de llenado actual del tanque,
     *              'mediciones' => La cantidad de mediciones válidas en las que
     *                              se basa el cálculo,
     *              'ultima'     => Fecha y hora de la última medición,
     *              ];
     */
    public function get_ultima_medicion()
    {
        $ahora = new DateTime();
        $datos = $this->get_ultimas_filas();
        $datos = $this->validar_diferencia_tiempos($datos);
        $mediana = $this->calcular_mediana($datos);
        $datos = $this->eliminar_outliers($datos, $mediana);
        $ultima = new DateTime(trim($datos[count($datos) - 1][0],"'") . " " . trim($datos[count($datos) - 1][1],"'")); 
        $resultado = $this->calcular_mediana($datos);
        return [
            'porcentaje' => (int) $resultado,
            'mediciones' => count($datos),
            'ultima'     => $ultima->format("d/m/Y H:i:s"),
        ];
    }

    /**
     * Esta función retorna las últimas $this->cantidad->ultimas filas del
     * archivo csv
     *
     * @return array La estructura del csv convertida en array: [
     *                  ['fecha', 'hora', distancia, porcentaje],
     *                  ['fecha', 'hora', distancia, porcentaje],
     *               ];
     */
    public function get_ultimas_filas()
    {
        $a = fopen($this->archivo, "r");
        if ($a === false) {
            die("ERROR al abrir el archivo $this->archivo");
        }
        $datos = [];
        $linea = 0;
        while ($data = fgetcsv($a, 100, ",")) {
            $datos[] = $data;
            if (count($datos) > $this->cantidad_ultimas) {
                unset($datos[0]);
                $datos = array_values($datos);
            }
        }
        fclose($a);
        return $datos;
    }

    /**
     * Esta función "limpia" los datos cuya antigüedad en minutos es mayor a
     * $this->maximo_plazo.
     *
     * @param Array $datos El array retornado por $this->get_ultimas_filas()
     * 
     * @return Array El mismo array recibido, pero excluyendo los datos cuya
     *               antigüedad supera los $this->maximo_plazo minutos.
     */
    public function validar_diferencia_tiempos($datos)
    {
        if (count($datos) == 1) {
            return $datos;
        }
        $ultimo = count($datos) - 1;
        $tiempo_ultimo = new DateTime(trim($datos[$ultimo][0],"'") . ' ' . trim($datos[$ultimo][1],"'"));
        $tiempo_inicial = new DateTime(trim($datos[0][0],"'") . ' ' . trim($datos[0][1],"'"));
        $diferencia = date_diff($tiempo_ultimo, $tiempo_inicial);
        if ($diferencia->i <= $this->maximo_plazo) {
            return $datos;
        } else {
            unset($datos[0]);
            $datos = array_values($datos);
            return $this->validar_diferencia_tiempos($datos);
        }
    }

    /**
     * Esta función calcula la mediana de los porcentajes de los datos recibidos
     *
     * @param Array $datos Los datos recibidos de $this->validar_diferencia_tiempos()
     *
     * @return int La mediana de los porcentajes medidos.
     */
    public function calcular_mediana($datos)
    {
        foreach ($datos as $x) {
            $d[]=$x[3];
        }
        sort($d);
        // FIXME: No es exactamente la mediana, porque arranca en cero
        $medio = count($d)/2;
        if (is_int($medio)) {
            return $d[$medio];
        } else {
            return ($d[ceil($medio)]+$d[floor($medio)])*0.5;
        }
    }

    /**
     * Esta función elimina los outliers del conjunto de datos.
     *
     * @param Array $datos Los datos recibidos de $this->validar_diferencia_tiempos()
     * @param int $mediana La mediana de los porcentajes de $datos
     *
     * @return Array El mismo array recibido en $datos, pero excluyendo los
     *               valores cuyo porcentaje está a una diferencia mayor a +/-3
     *               de la mediana.
     */
    public function eliminar_outliers($datos, $mediana)
    {
        $eliminar = [];
        foreach ($datos as $k => $d) {
            if (
                !isset($d[3]) || !is_numeric($d[3]) || $d[3] < 0 || $d[3] >100
                || (abs($d[3] - $mediana) > 3)
            ) {
                $eliminar[] = $k;
            }
        }
        if (count($eliminar) == 0) {
            return $datos;
        }
        foreach ($eliminar as $e) {
            unset($datos[$e]);
        }
        return array_values($datos);
    }

    /**
     * Retorna un objeto DateTime indicando qué hora era hace $horas horas.
     *
     * @param int $horas Horas a restar a partir del momento actual.
     *
     * @return DateTime Qué hora era hace $horas horas.
     */
    public function calcular_horario_hace($horas) {
        $ahora = new DateTime();
        $intervalo = new DateInterval("PT{$horas}H");
        $ahora->sub($intervalo);
        return $ahora;
    }

    /**
     * Toma los datos para hacer los gráfincos históricos, desde la hora límite
     * recibida por parámetro hasta ahora.
     *
     * @param DateTime $hora_limite La hora a partir de la cual voy a retornar
     *                              datos.
     *
     * @return Array Los datos a partir de los cuales se realizará la gráfica
     *               de históricos. Las claves son siempre horas múltiplos de 10
     *               minutos. Ej: en la clave 12:00 estarán todas las mediciones
     *               entre las 12:00 y las 12:09:59. El valor es un array de los
     *               porcentajes obtenidos en las mediciones de esos 10 minutos.
     *               Estructura:
     *               [
     *                  '12:00' => [80, 81, 80, 80, 82, 79],
     *                  '12:10' => [78, 79, 77],
     *                  ...
     *               ]
     */
    public function datos_para_historico($hora_limite)
    {
        $a = fopen($this->archivo, "r");
        if ($a === false) {
            die("ERROR al abrir el archivo $this->archivo");
        }
        $datos = [];
        $linea = 0;
        $encontre_hora_limite = false;
        while ($data = fgetcsv($a, 100, ",")) {
            if (!$encontre_hora_limite) {
                $fecha = $data[0];
                $hora = $data[1];
                $fh = str_replace("'", "", $fecha . " " . $hora);
                $momento = new DateTime($fh);
                if ($momento < $hora_limite) {
                    continue;
                }
                $encontre_hora_limite = true;
            }
            $hora_con_minutos = substr($data[1], 1, 4) . '0';
            $datos[$hora_con_minutos][] = (int) $data[3];
        }
        return $datos;
    }
    
    /**
     * Resume en un solo valor los datos obtenidos en cada rango de 10 minutos.
     *
     * @param Array $datos Los datos tal como son obtenidos desde $this->datos_para_historico()
     *
     * @return Array Un array con el formato ['x' => hora, 'y' => Un valor entero ]
     */
    function preparar_datos_para_grafico($datos)
    {
        $datos_resumidos = [];
        foreach ($datos as $hora => $conjunto) {
            $mediana = $this->calcular_mediana_solo_numeros($conjunto);
            $d = $this->eliminar_outliers_solo_numeros($conjunto, $mediana);
            if (count($d) > 0) {
                $datos_resumidos[] = [ 'x' => $hora, 'y' => $this->calcular_mediana_solo_numeros($d)];
            }
        }
        return $datos_resumidos;
    }


    /**
     * Calcula la mediana
     *
     * @param array $numeros Un array de numeros enteros
     *
     * @return int La mediana de esos números
     */
    function calcular_mediana_solo_numeros($numeros) {
        if (count($numeros) == 1) {
            return $numeros[0];
        }
        sort($numeros);
        // FIXME: No es exactamente la mediana, porque arranca en cero.
        if (is_int(count($numeros) / 2)) {
            return ($numeros[floor(count($numeros) / 2)] + $numeros[ceil(count($numeros) / 2)] ) * 0.5; 
        } else {
            return $numeros[ceil(count($numeros) / 2)];
        }

    }

    /**
     * Esta función elimina los outliers del conjunto de datos.
     *
     * @param Array $datos Un conjunto de enteros
     * @param int $mediana La mediana de los datos recibidos
     *
     * @return Array El mismo array recibido en $datos, pero excluyendo los
     *               elementos cuyo valor está a una diferencia mayor a +/-3
     *               de la mediana.
     */
    public function eliminar_outliers_solo_numeros($datos, $mediana)
    {
        $eliminar = [];
        foreach ($datos as $k => $d) {
            if (
                $d < 0 || $d >100
                || (abs($d - $mediana) > 3)
            ) {
                $eliminar[] = $k;
            }
        }
        if (count($eliminar) == 0) {
            return $datos;
        }
        foreach ($eliminar as $e) {
            unset($datos[$e]);
        }
        return array_values($datos);
    }
}
