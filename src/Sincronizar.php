<?php declare(strict_types=1);

namespace SincronizarEvento;


use Exception;

interface Sincronizar
{
    const PREFIJO_ID_EVENTO = 'TFGRUBEN';
    const EXISTE_IGUAL = 1;
    const EXISTE_CAMBIOS = 2;
    const EXISTE_CAMBIOS_ESTADO_ELIMINADO = 3;
    const NO_EXISTE = 0;
    const ACCION_COMPROBAR = 'comprobar';
    const ACCION_INSERTAR = 'insertar';
    const ACCION_ELIMINAR = 'eliminar';
    const ACCION_ACTUALIZAR = 'actualizar';
    const ESTADO_EVENTO_ACTIVO = 'activo';
    const ESTADO_EVENTO_ELIMINADO = 'eliminado';
    const RECORDATORIO_TIPO_EMAIL = 'email';
    const RECORDATORIO_TIPO_POPUP = 'popup';

    /**
     * @param Evento $evento
     * @param Calendario $calendarios []
     * @return Evento
     */
    public function aniadirEvento(Evento $evento, Calendario $calendarios);

    /**
     * @param Evento $evento
     * @param Calendario $calendarios
     * @return Evento
     */
    public function modificarEvento(Evento $evento, Calendario $calendarios);

    /**
     * @param Evento $evento
     * @param Calendario $calendarios
     * @return bool|Exception
     */
    public function eliminarEvento(Evento $evento, Calendario $calendarios);

    /**
     * @param Evento $evento
     * @param Calendario $calendario
     * @return int
     */
    public function comprobarEvento(Evento $evento, Calendario $calendario);

    /**
     * @param Calendario $calendario
     * @return bool
     */
    public function comprobarCalendario(Calendario $calendario);

    /**
     * @param array $options
     * @return int
     */
    public function obtenerEventos($options);

}