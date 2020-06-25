<?php declare(strict_types=1);

namespace SincronizarEvento;
/**
 * Interface Calendario
 * @package SincronizarEvento
 *
 * Interfaz de calendario que permite que se implemente de la misma manera en todas las clases que implemente
 * en este caso solo tendrá la propiedad Calendario
 *
 */
interface Calendario
{
    public function getCalendario();

    public function setCalendario(string $calendario);
}