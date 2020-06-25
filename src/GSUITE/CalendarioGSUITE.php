<?php declare(strict_types=1);


namespace SincronizarEvento\GSUITE;


use SincronizarEvento\Calendario;

/**
 * Class CalendarioGSuite
 * @package SincronizarEvento\GSuite
 *
 * Clase que implentará la interfaz calendario para que ser utulizada por ficheros GSuite.
 *
 */

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