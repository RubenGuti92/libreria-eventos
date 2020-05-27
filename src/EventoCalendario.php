<?php declare(strict_types=1);

namespace SincronizarEvento;

interface EventoCalendario
{
    public function getEvento();

    public function setEvento(Evento $evento);

    public function getCalendario();

    public function setCalendario(Calendario $calendario);

    public function getIdEventoCalendario();

    public function setIdEventoCalendario(string $cidEventoCalendario);


}