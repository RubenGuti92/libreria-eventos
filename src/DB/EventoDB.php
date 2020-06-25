<?php declare(strict_types=1);


namespace SincronizarEvento\DB;


use SincronizarEvento\Evento;

/**
 * Class EventoDB
 * @package SincronizarEvento\DB
 *
 * Clase que implementará la interfaz Evento para los ficheros DB.
 *
 */
class EventoDB implements Evento
{

    private $idEvento;
    private $titulo;
    private $descripcion;
    private $zonaHoraria;
    private $localizacion;
    private $fechaInicio;
    private $horaInicio;
    private $fechaFin;
    private $horaFin;
    private $recordatorioMinutosEmail;
    private $recordatorioMinutosPopUp;
    private $colorId;
    private $usado;
    private $todoElDia;


    /**
     * EventoDB constructor.
     */
    public function __construct()
    {
        $this->setZonaHoraria('Europe/Madrid');
        $this->setUsado(false);
        $this->setTodoElDia(false);
        $this->setColorId(1);
    }



    public function getIdEvento()
    {
        return $this->idEvento;
    }

    public function setIdEvento($idEvento)
    {
        $this->idEvento = $idEvento;

        return $this->idEvento;
    }

    public function getTitulo()
    {
        return $this->titulo;
    }

    public function setTitulo($titulo)
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getDescripcion()
    {
        return $this->descripcion;
    }

    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getZonaHoraria()
    {
        return $this->zonaHoraria;
    }

    public function setZonaHoraria($zonaHoraria)
    {
        $this->zonaHoraria = $zonaHoraria;
        return $this;
    }

    public function getLocalizacion()
    {
        return $this->localizacion;
    }

    public function setLocalizacion($localizacion)
    {
        $this->localizacion = $localizacion;
        return $this;
    }

    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;
        return $this;
    }

    public function getHoraInicio()
    {
        return $this->horaInicio;
    }

    public function setHoraInicio($horaInicio)
    {
        $this->horaInicio = $horaInicio;
        return $this;
    }

    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;
        return $this;
    }

    public function getHoraFin()
    {
        return $this->horaFin;
    }

    public function setHoraFin($horaFin)
    {
        $this->horaFin = $horaFin;
        return $this;
    }

    public function getRecordatorioMinutosPopUp()
    {
        return $this->recordatorioMinutosPopUp;

    }

    public function setRecordatorioMinutosPopUp($minutos)
    {
        $this->recordatorioMinutosPopUp = $minutos;
        return $this;
    }

    public function getRecordatorioMinutosEmail()
    {
        return $this->recordatorioMinutosEmail;

    }

    public function setRecordatorioMinutosEmail($minutos)
    {
        $this->recordatorioMinutosEmail = $minutos;
        return $this;
    }

    public function getColorId()
    {
        return $this->colorId;
    }

    public function setColorId($colorId)
    {
        $this->colorId = $colorId;
        return $this;
    }

    public function isUsado()
    {
        return $this->usado;
    }

    public function setUsado($usado)
    {
        $this->usado = $usado;
        return $this;
    }

    public function isTodoElDia()
    {
        return $this->todoElDia;
    }

    public function setTodoElDia($todoElDia)
    {
        $this->todoElDia = $todoElDia;
        return $this;
    }

    public function getAccion()
    {
        return $this->accion;
    }

    public function setAccion($accion)
    {
        $this->accion = $accion;
        return $this;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function setEstado($estado)
    {
        $this->estado = $estado;
        return $this;
    }
}