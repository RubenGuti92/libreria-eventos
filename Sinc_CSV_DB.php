<?php declare(strict_types=1);

namespace SincronizarEvento;


require "autoload.php";

use Google_Client;
use Google_Service_Calendar;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use PDO;
use PDOException;
use SincronizarEvento\CSV\SincronizarCSV;
use SincronizarEvento\DB\SincronizarDB;
use SincronizarEvento\GSUITE\SincronizarGSUITE;


$logger = new Logger('EventosGsuite');
$logger->pushHandler(new StreamHandler('eventosDB.log'));
$logger->info('Inicio de la ejecución');

$ficheros = array(
    'eventos' => 'insert',
    'eventosCalendarios' => 'evento_calendario',
);
try {
    $conexion = new PDO('pgsql:host=localhost port=5432 dbname=tfgruben', 'postgres', 'password_for_postgres');
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

} catch (PDOException $ex) {
    $ex->getMessage();
}

$origen = new SincronizarCSV($logger);
$destino = new SincronizarDB($conexion, $logger);

$sincronizar = new SincronizarEventos($origen, $destino, $logger);
$sincronizar->sincronizar($ficheros);


