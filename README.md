# Libreria para la gestión de eventos

_Librería que nos permitira gestionar unos eventos de forma automática y masiva._

_Con esta libreria podremos gestionar eventos en un fichero CSV, en una Base de datos y en GSuite_

_Como veremos más adelante nos permite realizar taras de importación y exportación de cada uno de los sistemas nombrados anteriormente_

## Comenzando 🚀

_Estas instrucciones te permitirán obtener una copia del proyecto en funcionamiento en tu máquina local para propósitos de desarrollo y pruebas._

### Instalación 🔧

_Debes tener instalado:_
* [PHP](https://www.php.net/) - El lenguaje utilizado
* [COMPOSER](https://getcomposer.org/) - Manejador de dependencias
* [POSTGRESQL](https://www.postgresql.org/) - Base de datos utilizada

## Ejemplo de implantación ⚙️

_Ejemplo sincronizar CSV a Base de datos_

```
$logger = new Logger('EventosGsuite');
$logger->pushHandler(new StreamHandler('eventosDB.log'));
$logger->info('Inicio de la ejecución');

$ficheros = array(
    'eventos' => 'insert',
    'eventosCalendarios' => 'evento_calendario',
);
try {
    $conexion = new PDO('pgsql:host=localhost port=PUERTOBD dbname=NOMBREBD', 'postgres', 'PASSBD');
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

} catch (PDOException $ex) {
    $ex->getMessage();
}

$origen = new SincronizarCSV($logger);
$destino = new SincronizarDB($conexion, $logger);

$sincronizar = new SincronizarEventos($origen, $destino, $logger);
$sincronizar->sincronizar($ficheros);

```

_Ejemplo sincronizar CSV a GSuite_

```
$logger = new Logger('EventosGsuite');
$logger->pushHandler(new StreamHandler('eventosgsuite.log'));
$logger->info('Inicio de la ejecución');

$ficheros = array(
    'eventos' => 'insert',
    'eventosCalendarios' => 'evento_calendario',
);
$cliente = new Google_Client();
$cliente->setApplicationName("TFG Eventos");
$cliente->setAuthConfig('fichero.json');
$cliente->setScopes([
    Google_Service_Calendar::CALENDAR
]);

$origen = new SincronizarCSV($logger);
$destino = new SincronizarGSUITE($cliente,$logger);

$sincronizar = new SincronizarEventos($origen, $destino, $logger);
$sincronizar->sincronizar($ficheros);


```

_Ejemplo sincronizar Base de datos a CSV_

```
$logger = new Logger('EventosGsuite');
$logger->pushHandler(new StreamHandler('eventosDB.log'));
$logger->info('Inicio de la ejecución');

$ficheros = array(
    'eventos' => 'insert',
    'eventosCalendarios' => 'evento_calendario',
);
try {
    $conexion = new PDO('pgsql:host=localhost port=PUERTOBD dbname=NOMBREBD', 'postgres', 'PASSBD');
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

} catch (PDOException $ex) {
    $ex->getMessage();
}

$origen = new SincronizarDB($conexion, $logger);
$destino = new SincronizarCSV($logger);

$sincronizar = new SincronizarEventos($origen, $destino, $logger);
$sincronizar->sincronizar($ficheros);


```
_Ejemplo sincronizar GSuite a CSV_

```
$logger = new Logger('Eventos de GSUITE a CSV');
$logger->pushHandler(new StreamHandler(__DIR__.'/var/log/eventosGsuiteCsv.log'));
$logger->debug('Inicio de la ejecución traspaso de tipo GSuite a tipo CSV');

$cliente = new Google_Client();
$cliente->setApplicationName("TFG Eventos");
$cliente->setAuthConfig('fichero.json');
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
    'nombre_fichero_eventos' => date('Y-m-d').'insert_eventos',
    'nombre_fichero_eventos_calendario' => date('Y-m-d').'insert_eventos_calendario',
);
$sincronizar = new SincronizarEventos($origen, $destino, $logger);
$sincronizar->sincronizar($opciones);


```
_Ejemplo sincronizar GSuite a Base de datos_

```
$logger = new Logger('Eventos de GSUITE a CSV');
$logger->pushHandler(new StreamHandler(__DIR__.'/var/log/eventosGsuiteCsv.log'));
$logger->debug('Inicio de la ejecución traspaso de tipo GSuite a tipo CSV');

$cliente = new Google_Client();
$cliente->setApplicationName("TFG Eventos");
$cliente->setAuthConfig('fichero.json');
$cliente->setScopes([
    Google_Service_Calendar::CALENDAR
]);

try {
    $conexion = new PDO('pgsql:host=localhost port=PUERTOBD dbname=NOMBREBD', 'postgres', 'PASSBD');
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

} catch (PDOException $ex) {
    $ex->getMessage();
}

$origen = new SincronizarGSUITE($cliente, $logger);
$destino = new SincronizarDB($conexion,$logger);

$fechas = array(
    'inicio' => '2020-04-15',
    'fin' => '2020-07-07',
);
$sincronizar = new SincronizarEventos($origen, $destino, $logger);
$sincronizar->sincronizar($opciones);


```

## Autores ✒️

* **Rubén Gutiérrez Pérez** - *Trabajo Final de Grado* - [contacto](ruben.gutierrez.perez@alumnos.ui1.es)


## Expresiones de Gratitud 🎁

* Comenta a otros sobre este proyecto 📢
* Invita una cerveza 🍺 o un café ☕ a alguien del equipo. 
* Da las gracias públicamente 🤓.
* etc.

---
⌨️ con ❤️ por [RubénGutierrez](https://github.com/RubenGuti92) 😊
