<?php declare(strict_types=1);

namespace SincronizarEvento;


require "autoload.php";

use Google_Client;
use Google_Service_Calendar;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use SincronizarEvento\CSV\SincronizarCSV;
use SincronizarEvento\GSUITE\SincronizarGSUITE;


$logger = new Logger('Eventos de GSUITE a CSV');
$logger->pushHandler(new StreamHandler(__DIR__.'/var/log/eventosGsuiteCsv.log'));
$logger->debug('Inicio de la ejecución traspaso de tipo GSuite a tipo CSV');

$cliente = new Google_Client();
$cliente->setApplicationName("TFG Eventos");
$cliente->setAuthConfig('libreria-eventos-5a7500e35a0b.json');
$cliente->setScopes([
    Google_Service_Calendar::CALENDAR
]);

$origen = new SincronizarGSUITE($cliente, $logger);
$destino = new SincronizarCSV($logger);

$fechas = array(
    'inicio' => '2020-04-15',
    'fin' => '2020-07-07',
);
$nombre_ficheros = array(
    'nombre_fichero_eventos' => 'insert_eventos',
    'nombre_fichero_eventos_calendario' => 'insert_eventos_calendario',
);
$sincronizar = new SincronizarEventos($origen, $destino, $logger);
$sincronizar->sincronizar(['calendario' => 'ruben.gutierrez@sauki.es', 'fechas' => $fechas, 'nombre_ficheros' => $nombre_ficheros]);


