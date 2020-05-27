<?php declare(strict_types=1);

namespace SincronizarEvento;

interface Evento
{
    public function getIdEvento();

    public function setIdEvento($idEvento);

    public function getTitulo();

    public function setTitulo($nombre);

    public function getDescripcion();

    public function setDescripcion($descripcion);

    public function getZonaHoraria();

    public function setZonaHoraria($zonaHoraria);

    public function getLocalizacion();

    public function setLocalizacion($localizacion);

    public function getFechaInicio();

    public function setFechaInicio($fechaInicio);

    public function getHoraInicio();

    public function setHoraInicio($horaInicio);

    public function getFechaFin();

    public function setFechaFin($fechaFin);

    public function getHoraFin();

    public function setHoraFin($horaFin);

    public function getRecordatorioMinutosEmail();

    public function setRecordatorioMinutosEmail($minutos);

    public function getRecordatorioMinutosPopUp();

    public function setRecordatorioMinutosPopUp($minutos);

    public function getColorId();

    public function setColorId($colorId);

    public function isUsado();

    public function setUsado($usado);

    public function isTodoElDia();

    public function setTodoElDia($todoElDia);

    public function getAccion();

    public function setAccion($accion);

    public function getEstado();

    public function setEstado($estado);

}