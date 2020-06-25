<?php declare(strict_types=1);

namespace SincronizarEvento;


use Exception;

/**
 * Interface Sincronizar
 * @package SincronizarEvento
 *
 * Interfaz sincronizar que determinara las constantes y los métodos a implementar en cada clase
 *
 */
interface Sincronizar
{
    const PREFIJO_ID_EVENTO = 'TFGRUBEN';// Prefijo que utilizaremos para la generación del id único
    const EXISTE_IGUAL = 1; // Constante que determina que son iguales
    const EXISTE_CAMBIOS = 2;// Constante que determina que existen cambios
    const EXISTE_CAMBIOS_ESTADO_ELIMINADO = 3; // Constante que determina que el estado es eliminado
    const NO_EXISTE = 0;// Constante que determina que no existe
    const ACCION_COMPROBAR = 'comprobar'; // Constante para determinar la acción decomprobar
    const ACCION_INSERTAR = 'insertar';// Constante para determinar la acción deinsertar
    const ACCION_ELIMINAR = 'eliminar';// Constante para determinar la acción deeliminar
    const ACCION_ACTUALIZAR = 'actualizar';// Constante para determinar la acción deactualizar
    const ESTADO_EVENTO_ACTIVO = 'activo';// Constante para determinar la acción deactivo
    const ESTADO_EVENTO_ELIMINADO = 'eliminado';// Constante para determinar la acción de eliminado
    const RECORDATORIO_TIPO_EMAIL = 'email';// Constante para determinar que el tipo de recordatorio es email
    const RECORDATORIO_TIPO_POPUP = 'popup';// Constante para determinar que el tipo de recordatorio es popup

    /**
     * Metodo que permitirá añadir un evento a un calendario
     *
     * @param Evento $evento
     * @param Calendario $calendarios []
     * @return Evento
     */
    public function aniadirEvento(Evento $evento, Calendario $calendarios);

    /**
     * Método que permitirá modificar un evento de un calendario
     *
     * @param Evento $evento
     * @param Calendario $calendarios
     * @return Evento
     */
    public function modificarEvento(Evento $evento, Calendario $calendarios);

    /**
     * Método que permitirá eliminar un evento de un calendario
     *
     * @param Evento $evento
     * @param Calendario $calendarios
     * @return bool|Exception
     */
    public function eliminarEvento(Evento $evento, Calendario $calendarios);

    /**
     * Método que comprobará un evento de un calendario
     *
     * @param Evento $evento
     * @param Calendario $calendario
     * @return int
     */
    public function comprobarEvento(Evento $evento, Calendario $calendario);

    /**
     * Evento que comprobará si existe un calendario
     *
     * @param Calendario $calendario
     * @return bool
     */
    public function comprobarCalendario(Calendario $calendario);

    /**
     * Método que devolvera los eventos
     *
     * @param array $options
     * @return array
     */
    public function obtenerEventos($options);

}