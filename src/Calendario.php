<?php declare(strict_types=1);

namespace SincronizarEvento;

interface Calendario
{
    public function getCalendario();

    public function setCalendario(string $calendario);
}