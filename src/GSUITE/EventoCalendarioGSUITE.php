<?php declare(strict_types=1);


namespace SincronizarEvento\GSUITE;


use SincronizarEvento\Calendario;
use SincronizarEvento\Evento;
use SincronizarEvento\EventoCalendario;

/**
 * Class EventoCalendarioGSuite
 * @package SincronizarEvento\GSuite
 *
 * Clase que implementará la interfaz EventoCalendario para los ficheros GSuite.
 *
 */

class EventoCalendarioGSUITE implements EventoCalendario
{

    private $evento;
    private $calendario;
    private $idEventoCalendario;


    /**
     * EventoCalendarioGSUITE constructor.
     */
    public function __construct()
    {

    }


    public function getEvento()
    {
        return $this->evento;
    }

    public function setEvento(Evento $evento)
    {
        $this->evento = $evento;

        return $this->evento;
    }

    public function getCalendario()
    {
        return $this->calendario;
    }

    public function setCalendario(Calendario $calendario)
    {
        $this->calendario = $calendario;
        return $this;
    }

    public function getIdEventoCalendario()
    {
        return $this->idEventoCalendario;
    }

    public function setIdEventoCalendario(string $idEventoCalendario)
    {
        $this->idEventoCalendario = $idEventoCalendario;
        return $this;
    }

}