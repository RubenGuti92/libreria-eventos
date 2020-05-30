<?php declare(strict_types=1);


namespace SincronizarEvento\DB;


use Exception;
use PDOException;
use RuntimeException;
use SincronizarEvento\AbstractSincronizar;
use SincronizarEvento\Calendario;
use SincronizarEvento\Evento;
use SincronizarEvento\Sincronizar;
use PDO;

class SincronizarDB extends AbstractSincronizar
{

    private $conexion;
    private $logger;

    public function __construct($conexion, $logger)
    {

        $this->conexion = $conexion;
        $this->logger = $logger;
    }


    /**
     * @inheritDoc
     */
    public function aniadirEvento(Evento $evento, Calendario $calendario)
    {
        $comprobacion_evento = self::_comprobacionEventoTabla($evento);

        if ($comprobacion_evento == self::NO_EXISTE) {
            try {
                $stmt = $this->conexion->prepare('INSERT INTO "eventos" ("idEvento","titulo", "descripcion","fechaInicio", "fechaFin","horaInicio","horaFin","zonaHoraria","localizacion","recordatorioMinutosEmail","recordatorioMinutosPopUp","colorId","accion")
                                                       VALUES (:idEvento,:titulo,:descripcion,:fechaInicio,:fechaFin,:horaInicio,:horaFin,:zonaHoraria,:localizacion,:recordatorioMinutosEmail,:recordatorioMinutosPopUp,:colorId,:accion)');

                $horaInicio = ($evento->getHoraInicio() == "") ? null : $evento->getHoraInicio();
                $horaFin = ($evento->getHoraFin() == "") ? null : $evento->getHoraFin();

                $stmt->bindValue(':idEvento', $evento->getIdEvento(), PDO::PARAM_STR);
                $stmt->bindValue(':titulo', $evento->getTitulo(), PDO::PARAM_STR);
                $stmt->bindValue(':descripcion', $evento->getDescripcion(), PDO::PARAM_STR);
                $stmt->bindValue(':fechaInicio', $evento->getFechaInicio(), PDO::PARAM_STR);
                $stmt->bindValue(':fechaFin', $evento->getFechaFin(), PDO::PARAM_STR);
                $stmt->bindValue(':horaInicio', $horaInicio, PDO::PARAM_STR | PDO::PARAM_NULL);
                $stmt->bindValue(':horaFin', $horaFin, PDO::PARAM_STR | PDO::PARAM_NULL);
                $stmt->bindValue(':zonaHoraria', $evento->getZonaHoraria(), PDO::PARAM_STR);
                $stmt->bindValue(':localizacion', $evento->getLocalizacion(), PDO::PARAM_STR);
                $stmt->bindValue(':recordatorioMinutosEmail', $evento->getRecordatorioMinutosEmail(), PDO::PARAM_INT);
                $stmt->bindValue(':recordatorioMinutosPopUp', $evento->getRecordatorioMinutosPopUp(), PDO::PARAM_INT);
                $stmt->bindValue(':colorId', $evento->getcolorId(), PDO::PARAM_INT);
                $stmt->bindValue(':accion', $evento->getAccion(), PDO::PARAM_STR);

                $stmt->execute();
                $idUnicoEvento = $this->conexion->lastInsertId();

            } catch (PDOException $ex) {
                $this->logger->error('Error al comprobar un evento:', $ex->getMessage());

            }
        }

        //Como el evento no tiene cambios lo insertamos
        if ($evento->getEstado() || $evento->getEstado() == null || $evento->getEstado() == self::ESTADO_EVENTO_ACTIVO) {
            $activo = true;
        } else {
            $activo = false;
        }

        try {
            $stmt = $this->conexion->prepare('INSERT INTO "eventos_calendarios" ("evento", "calendario", "idEventoCalendarioPlataforma","activo") VALUES (:evento, :calendario, :idEventoCalendarioPlataforma,:activo)');
            $stmt->bindValue(':evento', $evento->getIdEvento(), PDO::PARAM_STR);
            $stmt->bindValue(':calendario', $calendario->getCalendario(), PDO::PARAM_STR);
            $stmt->bindValue(':idEventoCalendarioPlataforma', self::generarIdEvento($evento, $calendario), PDO::PARAM_STR);
            $stmt->bindValue(':activo', $activo, PDO::PARAM_BOOL);
            $ejecutado = $stmt->execute();

            $id = $this->conexion->lastInsertId();
            $this->logger->debug('Se ha insertado correctamente id: ' . $id);
        } catch (PDOException $ex) {
            $this->logger->error('Error al comprobar un evento:', $ex->getMessage());

        }

    }

    /**
     * @inheritDoc
     */
    public function modificarEvento(Evento $evento, Calendario $calendario)
    {
        $idEventoCalendarioPlataforma = self::generarIdEvento($evento, $calendario);

        $stmt = $this->conexion->prepare('SELECT "evento","calendario" FROM "eventos_calendarios" WHERE "idEventoCalendarioPlataforma" = :idEventoCalendarioPlataforma ;');
        $stmt->bindValue(':idEventoCalendarioPlataforma', $idEventoCalendarioPlataforma);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            $idBDEvento = $resultado['evento'];
            $idCalendario = $resultado['calendario'];
        }

        if ($idBDEvento != null) {

            $evento->setEstado(true);

            try {
                $stmt = $this->conexion->prepare('UPDATE "eventos"
                                              SET "titulo" = :titulo, "descripcion" = :descripcion,"fechaInicio" = :fechaInicio, "fechaFin" = :fechaFin,"horaInicio" = :horaInicio,"horaFin" = :horaFin,
                                                    "zonaHoraria" = :zonaHoraria,"localizacion" = :localizacion,"recordatorioMinutosEmail" = :recordatorioMinutosEmail,"recordatorioMinutosPopUp" = :recordatorioMinutosPopUp,
                                                    "colorId" = :colorId , "accion" = :accion
                                              WHERE "idEvento" = :idEvento');
                $stmt->bindValue(':idEvento', $evento->getIdEvento(), PDO::PARAM_STR);
                $stmt->bindValue(':titulo', $evento->getTitulo(), PDO::PARAM_STR);
                $stmt->bindValue(':descripcion', $evento->getDescripcion(), PDO::PARAM_STR);
                $stmt->bindValue(':fechaInicio', $evento->getFechaInicio(), PDO::PARAM_STR);
                $stmt->bindValue(':fechaFin', $evento->getFechaFin(), PDO::PARAM_STR);
                $stmt->bindValue(':horaInicio', $evento->getHoraInicio(), PDO::PARAM_STR);
                $stmt->bindValue(':horaFin', $evento->getHoraFin(), PDO::PARAM_STR);
                $stmt->bindValue(':zonaHoraria', $evento->getZonaHoraria(), PDO::PARAM_STR);
                $stmt->bindValue(':localizacion', $evento->getLocalizacion(), PDO::PARAM_STR);
                $stmt->bindValue(':recordatorioMinutosEmail', $evento->getRecordatorioMinutosEmail(), PDO::PARAM_INT);
                $stmt->bindValue(':recordatorioMinutosPopUp', $evento->getRecordatorioMinutosPopUp(), PDO::PARAM_INT);
                $stmt->bindValue(':colorId', $evento->getColorId(), PDO::PARAM_INT);
                $stmt->bindValue(':accion', $evento->getAccion(), PDO::PARAM_STR);

                $stmt->execute();

                $this->logger->debug('Evento actualizado:' . $evento->getIdEvento());
            } catch (PDOException $ex) {
                $this->logger->error('Error al actualizar el evento:', $ex->getMessage());
            }

        }

        if ($idCalendario != null) {
            try {
                $stmt = $this->conexion->prepare('UPDATE "eventos_calendarios"
                                              SET "activo" = :estado 
                                              WHERE "idEventoCalendarioPlataforma" = :idEventoCalendarioPlataforma');
                $stmt->bindValue(':estado', $evento->getEstado(), PDO::PARAM_INT);
                $stmt->bindValue(':idEventoCalendarioPlataforma', $idEventoCalendarioPlataforma, PDO::PARAM_STR);

                $stmt->execute();
                $this->logger->debug('Evento actualizado:' . $evento->getIdEvento());
            } catch (PDOException $ex) {
                $this->logger->error('Error al actualizar el evento:', $ex->getMessage());

            }

        }
    }

    /**
     * @inheritDoc
     */
    public function eliminarEvento(Evento $evento, Calendario $calendario)
    {
        $idEventoCalendarioPlataforma = self::generarIdEvento($evento, $calendario);

        try {
            $stmt = $this->conexion->prepare('UPDATE "eventos_calendarios"
                                              SET "activo" = :estado 
                                              WHERE "idEventoCalendarioPlataforma" = :idEventoCalendarioPlataforma');
            $stmt->bindValue(':estado', false, PDO::PARAM_INT);
            $stmt->bindValue(':idEventoCalendarioPlataforma', $idEventoCalendarioPlataforma, PDO::PARAM_STR);

            $stmt->execute();
            $this->logger->debug('Evento eliminado:' . $idEventoCalendarioPlataforma);
        } catch (PDOException $ex) {
            $this->logger->error('Error al actualizar el evento:', $ex->getMessage());

        }
    }

    /**
     * @inheritDoc
     */
    public function comprobarEvento(Evento $evento, Calendario $calendario)
    {
        try {
            $comprobacion_evento = self::EXISTE_IGUAL;
            $idEventoCalendarioPlataforma = self::generarIdEvento($evento, $calendario);
            //Comprobamos si el evento ha sido insertado en el calendario
            $stmt = $this->conexion->prepare('SELECT "evento","calendario","activo" FROM "eventos_calendarios" WHERE "idEventoCalendarioPlataforma" = :idEventoCalendarioPlataforma;');
            $stmt->bindValue(':idEventoCalendarioPlataforma', $idEventoCalendarioPlataforma);
            $stmt->execute();
            $resultado_evento_calendario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($resultado_evento_calendario) {
                //En caso de que exista comprobamos los datos del evento
                if ($resultado_evento_calendario['activo']) {
                    $evento->setEstado(self::ESTADO_EVENTO_ACTIVO);
                } else {
                    $evento->setEstado(self::ESTADO_EVENTO_ELIMINADO);
                }
                $comprobacion_evento = self::_comprobacionEventoTabla($evento);
            } else {
                $comprobacion_evento = self::NO_EXISTE;
            }


            return $comprobacion_evento;
        } catch (PDOException $ex) {
            $this->logger->error('Error al comprobar un evento:', $ex->getMessage());
            die();
        }
    }

    /**
     * @param Evento $evento
     * @return array
     */
    private function _comprobacionEventoTabla(Evento $evento)
    {
        $datos_evento = array(
            'existen_cambios' => Sincronizar::EXISTE_IGUAL,
            'cadena_cambios' => '',
        );


        $stmt = $this->conexion->prepare('SELECT "id","idEvento","titulo", "descripcion","fechaInicio", "fechaFin","horaInicio","horaFin","zonaHoraria","localizacion","recordatorioMinutosEmail","recordatorioMinutosPopUp","colorId" 
                                            FROM eventos e
                                            WHERE "idEvento" = :idEvento;');
        $stmt->bindValue(':idEvento', $evento->getIdEvento());
        $stmt->execute();
        $resultado_evento = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado_evento) {
            $idUnicoEvento = $resultado_evento['id'];
            //Comparamos los eventos
            if ($evento->getTitulo() != $resultado_evento['titulo']) {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS;
                $datos_evento['cadena_cambios'] .= "titulo, ";
            }
            if ($evento->getDescripcion() != $resultado_evento['descripcion']) {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS;
                $datos_evento['cadena_cambios'] .= "descripción, ";
            }
            if ($evento->getZonaHoraria() != $resultado_evento['zonaHoraria']) {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS;
                $datos_evento['cadena_cambios'] .= "zona horaria, ";
            }
            if ($evento->getLocalizacion() != $resultado_evento['localizacion']) {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS;
                $datos_evento['cadena_cambios'] .= "localización, ";
            }
            if ($evento->getFechaInicio() != $resultado_evento['fechaInicio']) {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS;
                $datos_evento['cadena_cambios'] .= "fecha inicio, ";
            }
            if ($evento->getFechaFin() != $resultado_evento['fechaFin']) {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS;
                $datos_evento['cadena_cambios'] .= "fecha fin, ";
            }
            if ($evento->getHoraInicio() != $resultado_evento['horaInicio']) {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS;
                $datos_evento['cadena_cambios'] .= "hora inicio, ";
            }
            if ($evento->getHoraFin() != $resultado_evento['horaFin']) {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS;
                $datos_evento['cadena_cambios'] .= "hora fin, ";
            }
            if ($evento->getRecordatorioMinutosEmail() != $resultado_evento['recordatorioMinutosEmail']) {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS;
                $datos_evento['cadena_cambios'] .= "recordatorio mail ";
            }
            if ($evento->getRecordatorioMinutosPopUp() != $resultado_evento['recordatorioMinutosPopUp']) {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS;
                $datos_evento['cadena_cambios'] .= "recordatorio popup, ";
            }
            if ($evento->getColorId() != $resultado_evento['colorId']) {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS;
                $datos_evento['cadena_cambios'] .= "color, ";
            }

            if ($evento->getEstado() == self::ESTADO_EVENTO_ELIMINADO) {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS_ESTADO_ELIMINADO;
                $this->logger->debug('El evento se encuentra eliminado');
                $datos_evento['cadena_cambios'] .= "estado ";
            }
            if ($datos_evento['cadena_cambios'] != "") {
//                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_CAMBIOS;
                $this->logger->debug('Los siguientes campos no coinciden: ' . $datos_evento['cadena_cambios']);
            } else {
                $datos_evento['existen_cambios'] = Sincronizar::EXISTE_IGUAL;
                $this->logger->debug('El evento es igual');
            }
        } else {
            $datos_evento['existen_cambios'] = Sincronizar::NO_EXISTE;
            $this->logger->debug('El evento no existe');
        }


        return $datos_evento['existen_cambios'];

    }

    /**
     * @inheritDoc
     */
    public function comprobarCalendario(Calendario $calendario)
    {
        //Buscamos si hay alguna referencia de ese calendario en la tabla de eventos_calendarios
        $stmt = $this->conexion->prepare('SELECT "id" FROM "eventos_calendarios" WHERE "calendario" = :calendario;');
        $stmt->bindValue(':calendario', $calendario->getCalendario());
        $stmt->execute();
        $resultado_calendario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado_calendario) {
            return $resultado_calendario['id'];
        } else {
            return 0;
        }
    }

    /**
     * @inheritDoc
     */
    public function obtenerEventos($options)
    {
        $datos_evento_calendario = array();

        if (!isset($opciones['fechas']['inicio'])) {
            //Si no existe la fecha de incio cogemos la de hace 6 meses
            $fecha_hoy = date("Y-m-d", strtotime("now - 6 month"));
            $fecha_inicio_busqueda = $fecha_hoy;
        } else {
            $fecha_inicio_busqueda = $opciones['fechas']['inicio'];
        }

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

        $stmt = $this->conexion->prepare('SELECT "id","idEvento","titulo", "descripcion","fechaInicio", "fechaFin","horaInicio","horaFin","zonaHoraria","localizacion","recordatorioMinutosEmail","recordatorioMinutosPopUp","colorId" ,"accion" 
                                            FROM eventos e
                                            WHERE "fechaInicio" >= :fechaInicio AND "fechaFin" <= :fechaFin;');
        $stmt->bindValue(':fechaInicio', $fecha_inicio_busqueda);
        $stmt->bindValue(':fechaFin', $fecha_fin_busqueda);
        $stmt->execute();
        $datos_eventos_encontrados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $array_eventos = array();
        foreach ($datos_eventos_encontrados as $evento) {
            $evento_encontrado = new EventoDB();
            $evento_encontrado->setIdEvento($evento['idEvento']);
            $evento_encontrado->setTitulo($evento['titulo']);
            $evento_encontrado->setLocalizacion($evento['localizacion']);
            $evento_encontrado->setDescripcion($evento['descripcion']);
            $evento_encontrado->setZonaHoraria($evento['zonaHoraria']);
            $evento_encontrado->setFechaInicio($evento['fechaInicio']);
            $evento_encontrado->setFechaFin($evento['fechaFin']);
            $evento_encontrado->setHoraInicio($evento['horaInicio']);
            $evento_encontrado->setHoraFin($evento['horaFin']);
            $evento_encontrado->setColorId($evento['colorId']);
            $evento_encontrado->setRecordatorioMinutosEmail($evento['recordatorioMinutosEmail']);
            $evento_encontrado->setRecordatorioMinutosPopUp($evento['recordatorioMinutosPopUp']);
            if ($evento['accion'] == null) {
                $evento_encontrado->setAccion(self::ACCION_INSERTAR);
            } else {
                $evento_encontrado->setAccion($evento['accion']);
            }

            $array_eventos[$evento['idEvento']] = $evento_encontrado;
        }

        if (count($array_eventos) > 0) {
            foreach ($array_eventos as $idEvento => $datos_evento) {
                $stmt = $this->conexion->prepare('SELECT "id","idEventoCalendarioPlataforma","evento","calendario","activo" FROM "eventos_calendarios" WHERE "evento" = :evento;');
                $stmt->bindValue(':evento', $idEvento);
                $stmt->execute();
                $resultado_eventos_calendarios_encontrados = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($resultado_eventos_calendarios_encontrados as $evento_calendario_encontrado) {
                    if ($evento_calendario_encontrado['activo']) {
                        $estado = self::ESTADO_EVENTO_ACTIVO;
                    } else {
                        $estado = self::ESTADO_EVENTO_ELIMINADO;
                    }
                    $array_eventos[$idEvento]->setEstado($estado);
                    $calendario = new CalendarioDB();
                    $calendario->setCalendario($evento_calendario_encontrado['calendario']);

                    $evento_calendario = new EventoCalendarioDB();
                    $evento_calendario->setCalendario($calendario);
                    $evento_calendario->setIdEventoCalendario($evento_calendario_encontrado['idEventoCalendarioPlataforma']);
                    $evento_calendario->setEvento($array_eventos[$idEvento]);

                    $datos_evento_calendario[] = $evento_calendario;
                }

            }
        } else {
            $this->logger->info('No se han encontrado eventos en ese rango de fechas');
        }

        return $datos_evento_calendario;

    }

}