<?php declare(strict_types=1);


namespace SincronizarEvento\GSUITE;


use SincronizarEvento\Calendario;

class CalendarioGSUITE implements Calendario
{
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