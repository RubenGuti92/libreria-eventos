<?php declare(strict_types=1);


namespace SincronizarEvento\DB;


use SincronizarEvento\Calendario;
use SincronizarEvento\Evento;

class CalendarioDB implements Calendario
{
    private $evento;
    private $calendario;

    public function getCalendario()
    {
        return $this->calendario;
    }

    public function setCalendario(string $calendario)
    {
        $this->calendario = $calendario;
        return $this->calendario;
    }

}