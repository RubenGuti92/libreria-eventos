<?php declare(strict_types=1);


namespace SincronizarEvento\CSV;


use SincronizarEvento\Calendario;
use SincronizarEvento\Evento;

/**
 * Class CalendarioCSV
 * @package SincronizarEvento\CSV
 *
 * Clase que implentará la interfaz calendario para que ser utulizada por ficheros CSV.
 *
 */
class CalendarioCSV implements Calendario
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