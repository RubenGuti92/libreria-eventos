<?php declare(strict_types=1);

namespace SincronizarEvento;


require "autoload.php";

use Google_Client;
use Google_Service_Calendar;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SincronizarEvento\CSV\SincronizarCSV;
use SincronizarEvento\GSUITE\SincronizarGSUITE;


$logger = new Logger('EventosGsuite');
$logger->pushHandler(new StreamHandler('eventosgsuite.log'));
$logger->info('Inicio de la ejecución');

$ficheros = array(
    'eventos' => 'insert',
    'eventosCalendarios' => 'evento_calendario',
);
$cliente = new Google_Client();
$cliente->setApplicationName("TFG Eventos");
$cliente->setAuthConfig('libreria-eventos-5a7500e35a0b.json');
$cliente->setScopes([
    Google_Service_Calendar::CALENDAR
]);

$origen = new SincronizarCSV($logger);
$destino = new SincronizarGSUITE($cliente,$logger);

$sincronizar = new SincronizarEventos($origen, $destino, $logger);
$sincronizar->sincronizar($ficheros);


