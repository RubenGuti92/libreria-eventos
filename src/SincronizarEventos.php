<?php declare(strict_types=1);

namespace SincronizarEvento;

use Exception;
use http\Exception\RuntimeException;
use SincronizarEvento\CSV\CalendarioCSV;
use SincronizarEvento\CSV\EventoCSV;
use SincronizarEvento\CSV\SincronizarCSV;

require "autoload.php";


class SincronizarEventos
{

    /**
     * @var Sincronizar
     */
    private $origen;

    /**
     * @var Sincronizar
     */
    private $destino;

    /**
     * @var Sincronizar
     */
    private $logger;

    /**
     * SincronizarEventos constructor.
     * @param Sincronizar $origen
     * @param Sincronizar $destino
     * @param $logger
     */
    public function __construct(Sincronizar $origen, Sincronizar $destino, $logger)
    {
        $this->origen = $origen;
        $this->destino = $destino;
        $this->logger = $logger;
    }

    public function sincronizar(array $opciones = null)
    {

        $datos_origen = $this->origen->obtenerEventos($opciones);

        if ($this->destino instanceof SincronizarCSV) {

            if (!isset($opciones['nombre_ficheros']) || $opciones['nombre_ficheros'] == null || $opciones['nombre_ficheros'] == '') {
                $nombre_fichero_evento= 'datosEventos';
                $nombre_fichero_eventos_calendario = 'datosEventosCalendario';
            }else{
                $nombre_fichero_evento= $opciones['nombre_ficheros']['nombre_fichero_eventos'];
                $nombre_fichero_eventos_calendario = $opciones['nombre_ficheros']['nombre_fichero_eventos_calendario'];
            }

            $archivo_evento_csv = fopen(__DIR__ . '/../var/ficherosCSV/Exportacion/' . $nombre_fichero_evento. '.csv', 'w');
            $archivo_eventos_calendario_csv = fopen(__DIR__ . '/../var/ficherosCSV/Exportacion/' . $nombre_fichero_eventos_calendario . '.csv', 'w');

            if ($archivo_evento_csv && $archivo_eventos_calendario_csv) {
                fputs($archivo_evento_csv, 'idEventoUnico;Titulo;Descripcion;Zona horaria;Localización;"Fecha Inicio YYYY-MM-DD";"Hora Inicio HH:MM:SS";"Fecha FinYYYY-MM-DD";"Hora Fin HH:MM:SS";"Alerta Mail (en minutos)";"Alerta POPUP (en minutos)";color;acción' . PHP_EOL);
                fputs($archivo_eventos_calendario_csv, 'idEvento;Calendario;Estado' . PHP_EOL);

                if (isset($datos_origen['eventos']) && isset($datos_origen['eventos_calendario']) ) {

                    foreach ($datos_origen['eventos_calendario'] as $index => $datos_evento_calendario) {
                        foreach ($datos_evento_calendario as $index => $evento_calendario) {
                            /**
                             * @var EventoCalendario $evento_calendario
                             */
                            $lineas_ficheros = $this->destino->aniadirEvento($evento_calendario->getEvento(), $evento_calendario->getCalendario());
                            fputs($archivo_evento_csv, $lineas_ficheros['evento'] . PHP_EOL);
                            fputs($archivo_eventos_calendario_csv, $lineas_ficheros['eventos_calendario'] . PHP_EOL);
                        }
                    }

                }

                fclose($archivo_evento_csv);
                fclose($archivo_eventos_calendario_csv);
            } else {

                new RuntimeException('Ha ocurrido un error con los ficheros');

            }


        } else {

            $this->logger->debug('Eventos cargados');
            $this->logger->debug('Calendarios cargados');
            $this->logger->debug('Eventos Calendario cargados');


            try {

                foreach ($datos_origen as $evento_calendario) {
                    /**
                     * @var $evento_calendario EventoCalendario
                     */
                    if (!$this->destino->comprobarCalendario($evento_calendario->getCalendario())) {
                        $this->logger->warning('El calendario indicado no existe.');
                        continue;
                    }
                    $this->logger->debug('Calendario: ' . $evento_calendario->getCalendario()->getCalendario() . ' - Evento: ' . $evento_calendario->getEvento()->getIdEvento() . ' - Accion: ' . $evento_calendario->getEvento()->getAccion());
                    $comprobacion_evento = $this->destino->comprobarEvento($evento_calendario->getEvento(), $evento_calendario->getCalendario());
                    switch ($evento_calendario->getEvento()->getAccion()) {
                        case Sincronizar::ACCION_INSERTAR:
                            if ($comprobacion_evento == Sincronizar::EXISTE_CAMBIOS_ESTADO_ELIMINADO) {
                                $this->logger->debug('El evento ya existe pero esta eliminado, se procede a editarlo y activarlo.');
                                //Como esta eliminado lo que hacemos es cambiar su estado a "confirmado" es decir activo
                                $evento_calendario->getEvento()->setEstado(Sincronizar::ESTADO_EVENTO_CONFIRMADO);
                                $this->destino->modificarEvento($evento_calendario->getEvento(), $evento_calendario->getCalendario());
                            } else if ($comprobacion_evento == Sincronizar::NO_EXISTE) {
                                $this->logger->debug('El evento no existe.');
                                $this->destino->aniadirEvento($evento_calendario->getEvento(), $evento_calendario->getCalendario());
                            } else if ($comprobacion_evento == Sincronizar::EXISTE_CAMBIOS) {
                                $this->logger->debug('El evento ya existe pero esta modificado, se actualiza.');
                                $this->destino->modificarEvento($evento_calendario->getEvento(), $evento_calendario->getCalendario());
                            } else {
                                $this->logger->debug('El evento ya existe y es identico.');
                            }
                            break;
                        case Sincronizar::ACCION_COMPROBAR:
                            //Se llama a la función previamente por lo que no es necesario
                            break;
                        case Sincronizar::ACCION_ACTUALIZAR:
                            if ($comprobacion_evento == Sincronizar::EXISTE_CAMBIOS_ESTADO_ELIMINADO) {
                                $this->logger->debug('El evento ya existe pero esta eliminado, se activará y actualizará.');
                                $evento_calendario->getEvento()->setEstado(Sincronizar::ESTADO_EVENTO_CONFIRMADO);
                                $this->destino->modificarEvento($evento_calendario->getEvento(), $evento_calendario->getCalendario());
                            } else if ($comprobacion_evento == Sincronizar::EXISTE_CAMBIOS) {
                                $this->destino->modificarEvento($evento_calendario->getEvento(), $evento_calendario->getCalendario());
                            } else if ($comprobacion_evento == Sincronizar::NO_EXISTE) {
                                $this->logger->debug('El evento no existe por lo que se insertará.');
                                $this->destino->aniadirEvento($evento_calendario->getEvento(), $evento_calendario->getCalendario());
                            } else {
                                $this->logger->debug('El evento no es identico por lo que no es necesaria la actualización.');
                            }
                            break;
                        case Sincronizar::ACCION_ELIMINAR:
                            if ($comprobacion_evento != Sincronizar::NO_EXISTE) {
                                $this->destino->eliminarEvento($evento_calendario->getEvento(), $evento_calendario->getCalendario());
                            } else {
                                $this->logger->debug('El evento no existe, no puede ser eliminado.');
                            }
                            break;
                    }

                }

            } catch (Google_Service_Exception $ex) {
                $this->logger->warning('Ha ocurrido un error con el servicio de google:' . $ex->getError());
            }
        }


    }

}



