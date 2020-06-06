<?php declare(strict_types=1);


namespace SincronizarEvento\GSUITE;


use DateTime;
use DateTimeZone;
use Google_Client;
use Google_Service_Calendar_EventReminder;
use Google_Service_Calendar_EventReminders;
use Google_Service_Exception;
use SincronizarEvento\AbstractSincronizar;
use SincronizarEvento\Calendario;
use SincronizarEvento\Evento;
use SincronizarEvento\Sincronizar;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Psr\Log\LoggerInterface;

class SincronizarGSUITE extends AbstractSincronizar
{
    /**
     * @var Google_Client
     */
    private $cliente;
    private $logger;


    const ESTADO_EVENTO_CONFIRMADO = 'confirmed';
    const ESTADO_EVENTO_CONFIRMADO_PARCIAL = 'tentative';
    const ESTADO_EVENTO_ELIMINADO = 'cancelled';

    public function __construct($cliente, $logger)
    {

        $this->cliente = $cliente;
        $this->logger = $logger;
    }

    /**
     * @inheritDoc
     */
    public function comprobarCalendario(Calendario $calendario)
    {
        $this->cliente->setSubject($calendario->getCalendario());
        $servicio_google = new Google_Service_Calendar($this->cliente);
        try {
            $servicio_google->calendars->get('primary');
            return true;
        } catch (Google_Service_Exception $ex) {
            $this->logger->error('Error:' . $ex->getMessage());
            return false;
        }

    }

    /**
     * @inheritDoc
     */
    public function aniadirEvento(Evento $evento, Calendario $calendario)
    {
        $this->logger->debug('Se inserta un evento');
        //Correo al que se le inserta el evento
//        $cliente = $this->cliente;
        $this->cliente->setSubject($calendario->getCalendario());

        /**
         * Para generar el id utilizaremos un serie de valores para que sea único. Lo generamos debido a que al utilizar un csv para la isercción, edicion y eliminación debemos poder conocer los ids,
         * los cuales no se almacenarán.
         * Por ello utilizaremos md5 para generar dicho id formado por:
         * - correo de la persona
         * - id ÚNICO asignado al evento
         * - Año de la fecha del inicio del evento
         *
         */
        $id = self::generarIdEvento($evento, $calendario);
        $evento->setIdEvento($id);

        $evento_google = self::_generarEventoGoogle($evento);

        $servicio_google = new Google_Service_Calendar($this->cliente);
        try {
            $servicio_google->events->insert('primary', $evento_google);
            return $evento;
            $this->logger->debug('Se inserta el evento con id:' . $id);
        } catch (Google_Service_Exception $ex) {
            $this->logger->error('Error al insertar un evento:', $ex->getErrors());
        }

    }

    /**
     * @inheritDoc
     */
    public function modificarEvento(Evento $evento, Calendario $calendarios)
    {

//        $cliente = $this->cliente;
        $this->cliente->setSubject($calendarios->getCalendario());

        $id = self::generarIdEvento($evento, $calendarios);
        $evento->setIdEvento($id);
        $evento_google = self::_generarEventoGoogle($evento);

        $servicio_google = new Google_Service_Calendar($this->cliente);
        try {
            $evento = $servicio_google->events->update('primary', $evento->getIdEvento(), $evento_google);
            return $evento;
            $this->logger->debug('Evento actualizado id:' . $id);
        } catch (Google_Service_Exception $ex) {
            $this->logger->error('Error al modificar un evento:', $ex->getErrors());
        }

    }

    /**
     * @inheritDoc
     */
    public function eliminarEvento(Evento $evento, Calendario $calendarios)
    {
//        $cliente = $this->cliente;
        $this->cliente->setSubject($calendarios->getCalendario());

        $idEventoEliminar = self::generarIdEvento($evento, $calendarios);
        $evento->setIdEvento($idEventoEliminar);

        $evento_google = self::_generarEventoGoogle($evento);
        $evento_google->setStatus(self::ESTADO_EVENTO_ELIMINADO);


        $servicio_google = new Google_Service_Calendar($this->cliente);
        try {
            //Debido a que no se puede actualizar el campo id, lo que hacemos es cambiar el estado al evento a cancelado
            $servicio_google->events->update('primary', $idEventoEliminar, $evento_google);
            $this->logger->debug('Evento eliminado id:' . $idEventoEliminar);
            return true;
        } catch (Google_Service_Exception $ex) {
            $this->logger->error('Error al eliminar un evento:', $ex->getErrors());
        }
    }

    /**
     * @inheritDoc
     */
    public function comprobarEvento(Evento $evento, Calendario $calendario)
    {
        $this->cliente->setSubject($calendario->getCalendario());

        $servicio_google = new Google_Service_Calendar($this->cliente);
        try {
            $this->logger->debug('Se realiza una comprobación del evento.');

            $fechas_evento = self::_generarFechasEvento($evento);
            $recordatorios = self::_generarRecordatorios($evento);

            $idEvento = self::generarIdEvento($evento, $calendario);
            $evento_google = $servicio_google->events->get('primary', $idEvento);
            $comparacion_evento = Sincronizar::EXISTE_IGUAL;
            $string_cambios = "";//Variable donde vamos a almacenar los campos en los que se encontran cambios en el evento respecto al pasado

            //Comparamos los eventos
            if ($evento->getTitulo() != $evento_google->getSummary()) {
                $comparacion_evento = Sincronizar::EXISTE_CAMBIOS;
                $string_cambios .= "titulo, ";
            }
            if ($evento->getDescripcion() != $evento_google->getDescription()) {
                $comparacion_evento = Sincronizar::EXISTE_CAMBIOS;
                $string_cambios .= "descripción, ";
            }
            if ($evento->getZonaHoraria() != $evento_google->getStart()->getTimeZone()) {
                $comparacion_evento = Sincronizar::EXISTE_CAMBIOS;
                $string_cambios .= "zona horaria, ";
            }
            if ($evento->getLocalizacion() != $evento_google->getLocation()) {
                $comparacion_evento = Sincronizar::EXISTE_CAMBIOS;
                $string_cambios .= "localización, ";
            }
            if ($evento->isTodoElDia()) {
                if ($fechas_evento['fechaInicio']->getDate() != $evento_google->getStart()->getDate()) {
                    $comparacion_evento = Sincronizar::EXISTE_CAMBIOS;
                    $string_cambios .= "fecha inicio, ";
                }
                if ($fechas_evento['fechaFin']->getDate() != $evento_google->getEnd()->getDate()) {
                    $comparacion_evento = Sincronizar::EXISTE_CAMBIOS;
                    $string_cambios .= "fecha fin, ";
                }
            } else {
                if (date('Y-m-d H:i:s', strtotime($fechas_evento['fechaInicio']->getDateTime())) != date('Y-m-d H:i:s', strtotime($evento_google->getStart()->getDateTime()))) {
                    $comparacion_evento = Sincronizar::EXISTE_CAMBIOS;
                    $string_cambios .= "fecha inicio, ";
                }
                if (date('Y-m-d H:i:s', strtotime($fechas_evento['fechaFin']->getDateTime())) != date('Y-m-d H:i:s', strtotime($evento_google->getEnd()->getDateTime()))) {
                    $comparacion_evento = Sincronizar::EXISTE_CAMBIOS;
                    $string_cambios .= "fecha fin, ";
                }
            }

            if ($recordatorios != null) {
                $iguales = 1;
                $array_recordatorio_google = array();
                foreach ($evento_google->getReminders()->getOverrides() as $index => $recordatorio_google) {
                    $array_recordatorio_google[$recordatorio_google['method']] = $recordatorio_google['minutes'];
                }

                foreach ($recordatorios->getOverrides() as $index => $recordatorio_evento) {
                    if (!isset($recordatorio_evento['method']) && $recordatorio_evento['method'] != $array_recordatorio_google[$recordatorio_evento['method']]) {
                        $iguales = 0;
                    }
                }
                if (!$iguales) {
                    $comparacion_evento = Sincronizar::EXISTE_CAMBIOS;
                    $string_cambios .= "recordatorios, ";
                }
            }
            if ($evento->getColorId() != $evento_google->getColorId()) {
                $comparacion_evento = Sincronizar::EXISTE_CAMBIOS;
                $string_cambios .= "color, ";
            }

            if ($evento_google->getStatus() == self::ESTADO_EVENTO_ELIMINADO) {
                $comparacion_evento = Sincronizar::EXISTE_CAMBIOS_ESTADO_ELIMINADO;
                $this->logger->debug('El evento se encuentra eliminado');
                $string_cambios .= "estado ";
            }

            if ($string_cambios != "") {
                $this->logger->debug('Los siguientes campos no coinciden: ' . $string_cambios);
            }
            return $comparacion_evento;
        } catch (Google_Service_Exception $ex) {
            $this->logger->debug('El evento no existe (id buscado: ' . $idEvento . ')');
            $this->logger->error('Error:' . $ex->getMessage());
            return Sincronizar::NO_EXISTE;
        }
    }


    /**
     * @param array $opciones
     * @return int|void
     */
    public function obtenerEventos($opciones)
    {
        //Array donde almacenaremos los datos encontrados en el calendario
        $datos_evento_calendario = array();
        /**
         * @var Calendario
         */
        $calendario = $opciones['calendario'];

        //Correo al que se le inserta el evento
        $this->cliente->setSubject($calendario);

        $servicio_google = new Google_Service_Calendar($this->cliente);
        try {

            /**
             * Obtenemos la lista de eventos y lo recorremos, la función no da todos los eventos en una consulta si no que nos da tokens
             *
             */
            if (!isset($opciones['fechas']['inicio'])) {
                //Si no existe la fecha de incio cogemos la de hace 6 meses
                $fecha_hoy = date("Y-m-d", strtotime("now - 6 month"));
                $fecha_inicio_busqueda = $fecha_hoy;
            } else {
                $fecha_inicio_busqueda = $opciones['fechas']['inicio'];
            }
            $fecha_inicio_busqueda .= 'T00:00:00Z';

            if (!isset($opciones['fechas']['fin'])) {
                //Si no existe la fecha fin pero si la de incio la marcamos
                $fecha_hoy = date("Y-m-d", strtotime("now + 6 month"));
                $fecha_fin_busqueda = $fecha_hoy;
            } else {
                if ($opciones['fechas']['inicio'] == $opciones['fechas']['fin']) {
                    //Si ambas fechas son iguales sumamos un dia a la fecha fin
                    $fecha = date("Y-m-d", strtotime($opciones['fechas']['fin'] . " + 1 day"));
                    $fecha_fin_busqueda = $fecha;
                } else {
                    $fecha_fin_busqueda = $opciones['fechas']['fin'];
                }

            }
            $fecha_fin_busqueda .= 'T00:00:00Z';

            $optParams = array('orderBy' => 'startTime', 'showDeleted' => true, 'singleEvents' => 'true', 'timeMin' => $fecha_inicio_busqueda, 'timeMax' => $fecha_fin_busqueda);
            $listadoEventos = $servicio_google->events->listEvents('primary', $optParams);

            $salir = 0;
            $calendario_encontrado = new CalendarioGSUITE();
            $calendario_encontrado->setCalendario($calendario);
            do {
                foreach ($listadoEventos->getItems() as $evento) {
                    //Obtenemos el id del evento y lo desciframos
                    /**
                     * @var Google_Service_Calendar_Event $evento
                     */
                    $datos_id_evento = self::descifrarIdEvento($evento->getId());
                    if (!$datos_id_evento) {
                        //Si nos devuleve false pasamos del evento ya que no esta generado por nosotros
                        continue;
                    }

                    //Recorremos los resultados y almacenamos los datos
                    $evento_encontrado = new EventoGSUITE();
                    $evento_encontrado->setIdEvento($datos_id_evento['idEvento']);
                    $evento_encontrado->setTitulo($evento->getSummary());
                    $evento_encontrado->setLocalizacion($evento->getLocation());
                    $evento_encontrado->setDescripcion($evento->getDescription());
                    $evento_encontrado->setZonaHoraria($evento->getStart()->getTimeZone());
                    if ($evento->getStart()->getDate() != null) {
                        $evento_encontrado->setFechaInicio($evento->getStart()->getDate());
                    }
                    if ($evento->getStart()->getDateTime() != null) {
                        $evento_encontrado->setFechaInicio(date('Y-m-d', strtotime($evento->getStart()->getDateTime())));
                        $evento_encontrado->setHoraInicio(date('H:i:s', strtotime($evento->getStart()->getDateTime())));
                    }
                    if ($evento->getEnd()->getDate() != null) {
                        $evento_encontrado->setFechaFin($evento->getEnd()->getDate());
                    }
                    if ($evento->getEnd()->getDateTime() != null) {
                        $evento_encontrado->setFechaFin(date('Y-m-d', strtotime($evento->getEnd()->getDateTime())));
                        $evento_encontrado->setHoraFin(date('H:i:s', strtotime($evento->getEnd()->getDateTime())));
                    }
                    if ($evento->getColorId() != null) {
                        $evento_encontrado->setColorId($evento->getColorId());

                    }
                    //Obtenemos el estado
                    switch ($evento->getStatus()) {
                        case self::ESTADO_EVENTO_CONFIRMADO:
                        case self::ESTADO_EVENTO_CONFIRMADO_PARCIAL:
                            $estado = Sincronizar::ESTADO_EVENTO_ACTIVO;
                            break;
                        case self::ESTADO_EVENTO_ELIMINADO:
                            $estado = Sincronizar::ESTADO_EVENTO_ELIMINADO;
                            break;

                    }
                    $evento_encontrado->setEstado($estado);
                    //Recordatorios
                    $recordatorios = $evento->getReminders()->getOverrides();
                    if ($recordatorios != null) {
                        foreach ($recordatorios as $recordatorio) {
                            /**
                             * @var Google_Service_Calendar_EventReminder $recordatorio
                             */
                            switch ($recordatorio->getMethod()) {
                                case Sincronizar::RECORDATORIO_TIPO_EMAIL:
                                    $evento_encontrado->setRecordatorioMinutosEmail($recordatorio->getMinutes());
                                    break;
                                case Sincronizar::RECORDATORIO_TIPO_POPUP:
                                    $evento_encontrado->setRecordatorioMinutosPopUp($recordatorio->getMinutes());
                                    break;
                            }
                        }
                    }

                    //Generamos la relacion evento calendario y lo guardamos en el array separados por estado
                    $evento_calendario = new EventoCalendarioGSUITE();
                    $evento_calendario->setCalendario($calendario_encontrado);
                    $evento_calendario->setIdEventoCalendario(self::generarIdEvento($evento_encontrado, $calendario_encontrado));
                    $evento_calendario->setEvento($evento_encontrado);

                    $datos_evento_calendario[] = $evento_calendario;

                }
                $pageToken = $listadoEventos->getNextPageToken();
                if ($pageToken) {
                    $optParams = array('pageToken' => $pageToken);
                    $listadoEventos = $servicio_google->events->listEvents('primary', $optParams);
                } else {
                    $salir = 0;
                }
            } while ($salir == 1);


            return $datos_evento_calendario;
            $this->logger->debug('Evento actualizado id:' . $id);
        } catch (Google_Service_Exception $ex) {
            $this->logger->error('Error al modificar un evento:', $ex->getErrors());
        }

    }

    /**
     * Generamos el evento que insertaremos en google en función de sus requisitos
     *
     * @param Evento $evento
     * @return Google_Service_Calendar_Event
     */
    public function _generarEventoGoogle(Evento $evento)
    {
        $fechas_evento = self::_generarFechasEvento($evento);

        $event = new Google_Service_Calendar_Event(array(
            'id' => $evento->getIdEvento(),
            'summary' => $evento->getTitulo(), // Titulo del evento
            'location' => $evento->getLocalizacion(), // Localización del evento
            'description' => $evento->getDescripcion(), // Descripción del evento
            'start' => $fechas_evento['fechaInicio'], // Fecha inicio del evento
            'end' => $fechas_evento['fechaFin'], // Fecha fin del evento
            'guestsCanInviteOthers' => False, // no se permite que inviten a personas al evento
            'guestsCanModify' => False, // no se permite que modifiquen el evento
            'guestsCanSeeOtherGuests' => False,// no se permite que los asistentes vean al resto de asistentes
            'visibility' => 'private', // Solo los asistentes pueden ver los detalles del evento
            'colorId' => $evento->getColorId(), // Color que va a tener el evento en google calendar
//            //Fecha inicio del evento
//            'start' => array(
////                'date' => $evento->getFechaInicio(),
//                'dateTime' => $fechaInicio,
//                'timeZone' => $evento->getZonaHoraria(),
//            ),
//            //Fecha fin del evento
//            'end' => array(
////                'date' => $evento->getFechaFin(),
//                'dateTime' => $fechaFin,
//                'timeZone' => $evento->getZonaHoraria(),
//            ),
//            'recurrence' => array(
//                'RRULE:FREQ=DAILY;COUNT=2'
//            ),
//            'attendees' => array(
//                array('email' => 'lpage@example.com'),
//                array('email' => 'sbrin@example.com'),
//            ),
        ));

        if ($evento->getEstado() == self::ESTADO_EVENTO_ACTIVO) {
            $event->setStatus(self::ESTADO_EVENTO_CONFIRMADO);
        } else {
            $event->setStatus(self::ESTADO_EVENTO_ELIMINADO);
        }


        //Añadimos los recordatorios en caso de exisitir
        $reminder = self::_generarRecordatorios($evento);

        if ($reminder != null) {
            $event->setReminders($reminder);
        }


        return $event; // Devolvemos el evento de google
    }

    /**
     * @param Evento $evento
     * @return Google_Service_Calendar_EventDateTime[]
     */
    private
    function _generarFechasEvento(Evento $evento): array
    {
        //Generamos las fechas de inicio y fin del evento
        $fecha_inicio = new Google_Service_Calendar_EventDateTime(); //Declaración de la fecha
        $fecha_fin = new Google_Service_Calendar_EventDateTime(); //Declaración de la fecha

        if ($evento->isTodoElDia()) {
            //En caso de que el evento se produca en un solo día y dure todo el dia indicamos que la fecha sera la misma
            //por lo que solo indicaremos la fecha sin hora.
            $fecha_inicio->setDate($evento->getFechaInicio());
            $fecha_fin->setDate($evento->getFechaInicio());
        } else {
            $fecha_inicio->setDateTime($evento->getFechaInicio() . 'T' . $evento->getHoraInicio());
            $stringFechaFin = ($evento->getFechaFin() != "" || $evento->getFechaFin() != null) ? $evento->getFechaFin() : $evento->getFechaInicio();
            $fecha_fin->setDateTime($stringFechaFin . 'T' . $evento->getHoraFin());
        }

        if ($evento->getZonaHoraria() != null) {
            $fecha_inicio->setTimeZone($evento->getZonaHoraria()); //Definimos la zona horaria
            $fecha_fin->setTimeZone($evento->getZonaHoraria());//Definimos la zona horaria
        }

        $fechas_evento = array();
        $fechas_evento['fechaInicio'] = $fecha_inicio;
        $fechas_evento['fechaFin'] = $fecha_fin;

        return $fechas_evento;
    }

    /**
     * @param Evento $evento
     * @return Google_Service_Calendar_EventReminders|null
     */
    private
    function _generarRecordatorios(Evento $evento): Google_Service_Calendar_EventReminders
    {
        $overrides = array();
        if ($evento->getRecordatorioMinutosEmail() != "" || $evento->getRecordatorioMinutosEmail() != null || $evento->getRecordatorioMinutosEmail() != 0) {
            // Recordatorio via mail
            $overrides[] = array("method" => "email", "minutes" => (int)$evento->getRecordatorioMinutosEmail());
        }
        if ($evento->getRecordatorioMinutosPopUp() != "" || $evento->getRecordatorioMinutosPopUp() != null || $evento->getRecordatorioMinutosPopUp() != 0) {
            // Recordatorio via popup del calendario
            $overrides[] = array("method" => "popup", "minutes" => (int)$evento->getRecordatorioMinutosPopUp());
        }

        if (!empty($overrides)) {
            $reminder = new Google_Service_Calendar_EventReminders();
            $reminder->setUseDefault(false);
            $reminder->setOverrides($overrides);
        } else {
            $reminder = null;
        }

        return $reminder;
    }
}