<?php declare(strict_types=1);

namespace SincronizarEvento;

/**
 * Interface EventoCalendario
 * @package SincronizarEvento
 *
 * Interfaz que será la relacion entre un evento y un calendario.
 */
interface EventoCalendario
{
    public function getEvento();

    public function setEvento(Evento $evento);

    public function getCalendario();

    public function setCalendario(Calendario $calendario);

    public function getIdEventoCalendario();

    public function setIdEventoCalendario(string $cidEventoCalendario);


}