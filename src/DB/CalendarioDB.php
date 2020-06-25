<?php declare(strict_types=1);


namespace SincronizarEvento\DB;


use SincronizarEvento\Calendario;
use SincronizarEvento\Evento;

/**
 * Class CalendarioDB
 * @package SincronizarEvento\DB
 *
 * Clase que implentará la interfaz calendario para que ser utulizada por ficheros DB.
 *
 */
class CalendarioDB implements Calendario
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