<?php declare(strict_types=1);

namespace SincronizarEvento;

use Exception;
use http\Exception\RuntimeException;
use SincronizarEvento\CSV\CalendarioCSV;
use SincronizarEvento\CSV\EventoCSV;
use SincronizarEvento\CSV\SincronizarCSV;
use SincronizarEvento\DB\SincronizarDB;
use SincronizarEvento\GSUITE\SincronizarGSUITE;

require "autoload.php";

/**
 * Class SincronizarEventos
 * @package SincronizarEvento
 *
 * Clase que se encarga de realizar de forma automatica todas las acciones indicadas en los eventos
 *
 */
class SincronizarEventos
{

    /**
     * De donde obtendremos los eventos
     * @var Sincronizar
     */
    private $origen;

    /**
     * Donde los almacenaremos
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

    /**
     * Función encargada de realizar de forma automatica todo el proceso
     * @param array|null $opciones
     */
    public function sincronizar(array $opciones = null)
    {
        //Obtenemos toda la informacion, eventos y eventos calendario del origen
        $datos_origen = $this->origen->obtenerEventos($opciones);

        $this->logger->debug('Eventos cargados'); //Indicamos que ya han sido cargados
        $this->logger->debug('Calendarios cargados'); //Indicamos que ya han sido cargados
        $this->logger->debug('Eventos Calendario cargados'); //Indicamos que ya han sido cargados

        //Comprobamos si el destino es Sincronizar CSV
        if ($this->destino instanceof SincronizarCSV) {

            //Comprobamos si se han adjuntado los nombres o por el contrario utilizaremos los de por defecto
            if (!isset($opciones['nombre_ficheros']) || $opciones['nombre_ficheros'] == null || $opciones['nombre_ficheros'] == '') {
                $nombre_fichero_evento = 'datosEventos';
                $nombre_fichero_eventos_calendario = 'datosEventosCalendario';
            } else {
                $nombre_fichero_evento = $opciones['nombre_ficheros']['nombre_fichero_eventos'];
                $nombre_fichero_eventos_calendario = $opciones['nombre_ficheros']['nombre_fichero_eventos_calendario'];
            }

            //Obtenemos los datos de ambos ficheros
            $archivo_evento_csv = fopen(__DIR__ . '/../var/ficherosCSV/' . $nombre_fichero_evento . '.csv', 'w');
            $archivo_eventos_calendario_csv = fopen(__DIR__ . '/../var/ficherosCSV/' . $nombre_fichero_eventos_calendario . '.csv', 'w');

            //Recorremos cada linea y conformamos el evento y el evento calendario
            if ($archivo_evento_csv && $archivo_eventos_calendario_csv) {
                fputs($archivo_evento_csv, 'idEventoUnico;Titulo;Descripcion;Zona horaria;Localización;"Fecha Inicio YYYY-MM-DD";"Hora Inicio HH:MM:SS";"Fecha FinYYYY-MM-DD";"Hora Fin HH:MM:SS";"Alerta Mail (en minutos)";"Alerta POPUP (en minutos)";color;acción' . PHP_EOL);
                fputs($archivo_eventos_calendario_csv, 'idEvento;Calendario;Estado' . PHP_EOL);

                if (count($datos_origen) > 0) {

                    foreach ($datos_origen as $evento_calendario) {
                        /**
                         * @var EventoCalendario $evento_calendario
                         */
                        $lineas_ficheros = $this->destino->aniadirEvento($evento_calendario->getEvento(), $evento_calendario->getCalendario());
                        fputs($archivo_evento_csv, $lineas_ficheros['evento'] . PHP_EOL);
                        fputs($archivo_eventos_calendario_csv, $lineas_ficheros['eventos_calendario'] . PHP_EOL);
                    }

                }

                fclose($archivo_evento_csv); //Cerramos el fichero
                fclose($archivo_eventos_calendario_csv); //Cerramos el fichero
            } else {

                new RuntimeException('Ha ocurrido un error con los ficheros');

            }


        } else {
            //En caso de que no sea CSV se realizan las siguientes operaciones

            try {
                //Recorremos cada evento calendario
                foreach ($datos_origen as $evento_calendario) {
                    /**
                     * @var $evento_calendario EventoCalendario
                     */
                    if ($this->destino instanceof SincronizarGSUITE && !$this->destino->comprobarCalendario($evento_calendario->getCalendario())) {
                        //En caso de que no exista el calendario en Gsuite no se puede insertar el evento por lo que alamcenamos en el log error y continuamos
                        $this->logger->warning('El calendario indicado no existe.');
                        continue;
                    }
                    $this->logger->debug('Calendario: ' . $evento_calendario->getCalendario()->getCalendario() . ' - Evento: ' . $evento_calendario->getEvento()->getIdEvento() . ' - Accion: ' . $evento_calendario->getEvento()->getAccion());
                    $comprobacion_evento = $this->destino->comprobarEvento($evento_calendario->getEvento(), $evento_calendario->getCalendario());
                    if (($evento_calendario->getEvento()->getAccion() == "" || $evento_calendario->getEvento()->getAccion() == null) && $evento_calendario->getEvento()->getEstado() == Sincronizar::ESTADO_EVENTO_ACTIVO) {
                        $evento_calendario->getEvento()->setAccion(Sincronizar::ACCION_INSERTAR);
                    } else if (($evento_calendario->getEvento()->getAccion() == "" || $evento_calendario->getEvento()->getAccion() == null) && $evento_calendario->getEvento()->getEstado() == Sincronizar::ESTADO_EVENTO_ELIMINADO) {
                        $evento_calendario->getEvento()->setAccion(Sincronizar::ACCION_ELIMINAR);
                    }

                    //Comprobamos que acción se indica que se debe realizar
                    switch ($evento_calendario->getEvento()->getAccion()) {
                        case Sincronizar::ACCION_INSERTAR:
                            //En caso de que sea insertar, comprobamos si existe, en caso de exisitir se modificara, si no existe se insertara
                            if ($comprobacion_evento == Sincronizar::EXISTE_CAMBIOS_ESTADO_ELIMINADO) {
                                $this->logger->debug('El evento ya existe pero esta eliminado, se procede a editarlo y activarlo.');
                                //Como esta eliminado lo que hacemos es cambiar su estado a "confirmado" es decir activo
                                $evento_calendario->getEvento()->setEstado(Sincronizar::ESTADO_EVENTO_ACTIVO);
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
                            //En caso de que se determine que actualizar, comprobaremos si existe, en caso de exisitr se modifca, en caso de que no exista se inserta
                            if ($comprobacion_evento == Sincronizar::EXISTE_CAMBIOS_ESTADO_ELIMINADO) {
                                $this->logger->debug('El evento ya existe pero esta eliminado, se activará y actualizará.');
                                $evento_calendario->getEvento()->setEstado(Sincronizar::ESTADO_EVENTO_ACTIVO);
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
                            //Eliminamos el evento
                            if ($comprobacion_evento != Sincronizar::NO_EXISTE) {
                                $this->destino->eliminarEvento($evento_calendario->getEvento(), $evento_calendario->getCalendario());
                            } else {
                                $this->logger->debug('El evento no existe, no puede ser eliminado.');
                            }
                            break;
                    }

                }

            } catch (Google_Service_Exception $ex) {
                //Comprobamos si ha exisitido algun error de Gsuite
                $this->logger->warning('Ha ocurrido un error con el servicio de google:' . $ex->getError());
            }
        }


    }

}



