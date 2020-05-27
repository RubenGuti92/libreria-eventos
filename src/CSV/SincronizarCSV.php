<?php declare(strict_types=1);


namespace SincronizarEvento\CSV;


use http\Exception\BadMethodCallException;
use SincronizarEvento\Calendario;
use SincronizarEvento\Evento;
use SincronizarEvento\Sincronizar;
use PDO;

class SincronizarCSV implements Sincronizar
{

    public $logger;
    const DELIMITADOR_FICHERO = ';';

    /**
     * SincronizarCSV constructor.
     * @param $logger
     */
    public function __construct($logger)
    {

        $this->logger = $logger;
    }


    /**
     * @inheritDoc
     */
    public function aniadirEvento(Evento $evento, Calendario $calendario)
    {

        //Fila de los datos del evento
        $fila_datos_evento = $evento->getIdEvento() . self::DELIMITADOR_FICHERO;
        $fila_datos_evento .= ($evento->getTitulo() != null || $evento->getTitulo()) ? $evento->getTitulo() . self::DELIMITADOR_FICHERO : self::DELIMITADOR_FICHERO;
        $fila_datos_evento .= ($evento->getDescripcion() != null || $evento->getDescripcion()) ? $evento->getDescripcion() . self::DELIMITADOR_FICHERO : self::DELIMITADOR_FICHERO;
        $fila_datos_evento .= ($evento->getZonaHoraria() != null || $evento->getZonaHoraria()) ? $evento->getZonaHoraria() . self::DELIMITADOR_FICHERO : self::DELIMITADOR_FICHERO;
        $fila_datos_evento .= ($evento->getLocalizacion() != null || $evento->getLocalizacion()) ? $evento->getLocalizacion() . self::DELIMITADOR_FICHERO : self::DELIMITADOR_FICHERO;
        $fila_datos_evento .= ($evento->getFechaInicio() != null || $evento->getFechaInicio()) ? $evento->getFechaInicio() . self::DELIMITADOR_FICHERO : self::DELIMITADOR_FICHERO;
        $fila_datos_evento .= ($evento->getHoraInicio() != null || $evento->getHoraInicio()) ? $evento->getHoraInicio() . self::DELIMITADOR_FICHERO : self::DELIMITADOR_FICHERO;
        $fila_datos_evento .= ($evento->getFechaFin() != null || $evento->getFechaFin()) ? $evento->getFechaFin() . self::DELIMITADOR_FICHERO : self::DELIMITADOR_FICHERO;
        $fila_datos_evento .= ($evento->getHoraFin() != null || $evento->getHoraFin()) ? $evento->getHoraFin() . self::DELIMITADOR_FICHERO : self::DELIMITADOR_FICHERO;
        $fila_datos_evento .= ($evento->getRecordatorioMinutosEmail() != null || $evento->getRecordatorioMinutosEmail()) ? $evento->getRecordatorioMinutosEmail() . self::DELIMITADOR_FICHERO : self::DELIMITADOR_FICHERO;
        $fila_datos_evento .= ($evento->getRecordatorioMinutosPopUp() != null || $evento->getRecordatorioMinutosPopUp()) ? $evento->getRecordatorioMinutosPopUp() . self::DELIMITADOR_FICHERO : self::DELIMITADOR_FICHERO;
        $fila_datos_evento .= ($evento->getColorId() != null || $evento->getColorId()) ? $evento->getColorId() . self::DELIMITADOR_FICHERO : self::DELIMITADOR_FICHERO;
        $fila_datos_evento .= self::DELIMITADOR_FICHERO; // Añadimos la ultima casilla que sería la de la accion


        //Fila de los datos de la relación
        $fila_datos_eventos_calendario = $evento->getIdEvento() . self::DELIMITADOR_FICHERO;
        $fila_datos_eventos_calendario .= $calendario->getCalendario() . self::DELIMITADOR_FICHERO;
        $fila_datos_eventos_calendario .= $evento->getEstado() . self::DELIMITADOR_FICHERO; // Añadimos a mayores el estado en el que estaba el evento

        $filas_evento['evento'] = $fila_datos_evento;
        $filas_evento['eventos_calendario'] = $fila_datos_eventos_calendario;

        return $filas_evento;
    }

    /**
     * @inheritDoc
     */
    public function modificarEvento(Evento $evento, Calendario $calendarios)
    {
        // Función no implementada
        return new BadMethodCallException('Esta función no esta implementada');

    }

    /**
     * @inheritDoc
     */
    public function eliminarEvento(Evento $evento, Calendario $calendarios)
    {
        // Función no implementada
        return new BadMethodCallException('Esta función no esta implementada');
    }

    /**
     * @inheritDoc
     */
    public function comprobarEvento(Evento $evento, Calendario $calendario)
    {
        // Función no implmentada
        return new BadMethodCallException('Esta función no esta implementada');
    }

    /**
     * @inheritDoc
     */
    public function obtenerEventos($opciones)
    {
        //En las opciones debemos enviar el nombre (sin extensión) de los ficheros alojados en var/ficheros
        $fichero_eventos = __DIR__ . '/../../var/ficherosCSV/' . $opciones['eventos'] . '.csv';
        $fichero_eventos_calendarios = __DIR__ . '/../../var/ficherosCSV/' . $opciones['eventosCalendarios'] . '.csv';

        //Abrimos nuestro archivo
        $archivo = fopen($fichero_eventos, "r");
        //Lo recorremos
        $datosEventos = array();
        $fila = 0;
        while (($datos = fgetcsv($archivo, 0, self::DELIMITADOR_FICHERO)) == true) {
            $fila++;
            if (trim($datos[0]) === "" || $fila == 1) {
                //Omitimos la linea en caso de que sea la primera (serán las cabeceras) o este vacia
                continue;
            }
            $evento = new EventoCSV();
            $evento->setIdEvento($datos[0]);
            $evento->setTitulo($datos[1]);
            $evento->setDescripcion($datos[2]);
            $evento->setZonaHoraria((isset($datos[3])) ? $datos[3] : "");
            $evento->setLocalizacion((isset($datos[4])) ? $datos[4] : "");

            $evento->setFechaInicio($datos[5]);
            $evento->setHoraInicio((isset($datos[6])) ? $datos[6] : "");
            $evento->setFechaFin((isset($datos[7])) ? $datos[7] : "");
            $evento->setHoraFin((isset($datos[8])) ? $datos[8] : "");

            $evento->setRecordatorioMinutosEmail((isset($datos[9])) ? (int)$datos[9] : "");
            $evento->setRecordatorioMinutosPopUp((isset($datos[10])) ? (int)$datos[10] : "");
            $evento->setColorId((isset($datos[11])) ? (int)$datos[11] : "");
            $evento->setAccion((isset($datos[12])) ? trim($datos[12]) : "");

            if (
                (($evento->getFechaInicio() == $evento->getFechaFin()) && ($evento->getHoraInicio() == '' || $evento->getHoraInicio() == ''))
                ||
                ($evento->getFechaFin() == "" && ($evento->getHoraInicio() == '' || $evento->getHoraInicio() == ''))
            ) {
                $evento->setTodoElDia(true);
            }

            $datosEventos[$evento->getIdEvento()] = $evento;
        }

        $this->logger->debug('Se han cargado los eventos.', $datosEventos);

        //Abrimos nuestro archivo
        $archivo = fopen($fichero_eventos_calendarios, "r");
        //Lo recorremos
        $datosEventosCalendario = array();
        $fila = 0;
        while (($datos = fgetcsv($archivo, 0, self::DELIMITADOR_FICHERO)) == true) {
            $fila++;
            if ($datos[0] == null || trim($datos[0]) === "" || $fila == 1) {
                //Omitimos la linea en caso de que sea la primera (serán las cabeceras) o este vacia
                continue;
            }
            $calendario = new CalendarioCSV();
            $calendario->setCalendario($datos[1]);

            $evento_calendario = new EventoCalendarioCSV();
            $evento_calendario->setCalendario($calendario);
            $evento_calendario->setEvento($datosEventos[$datos[0]]);


            $datosEventosCalendario[] = $evento_calendario;
        }

        //Cerramos el archivo
        fclose($archivo);

        return $datosEventosCalendario;

        $this->logger->debug('Se han cargado los eventos y calendarios.', $datosEventosCalendario);


        return $datosEventosCalendario;
    }
}