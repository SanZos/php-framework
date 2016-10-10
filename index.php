<?php
//ini_set("error_reporting", 0);
set_time_limit(0);
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: x-requested-with, Content-Type');

if(!isset($_GET['type'])) $_GET['type'] = 'json';
switch (strtolower($_GET['type'])){
    case 'html':
        header('Content-Type: text/html; charset=utf-8');
    break;
    case 'excel':
        if(!isset($_GET['filename'])) $_GET['filename'] = 'Flux'.(new \DateTime())->format(Ymd).'.xlsx';
        header('Content-Disposition: attachment;filename="'.$_GET['filename'].'"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    break;
    case 'json':
    default:
        header('Content-Type: application/json; charset=utf-8');
    break;
}
require_once 'Autoload.php';

$uri = strtolower($_SERVER['REQUEST_URI']);

try{
    $rc = new \Collection\RouteCollection();
    \Register\RouteRegister::autoRegister($rc);

    $route = $rc->getRoute(null, null, $uri, $_SERVER['REQUEST_METHOD']);
    \Outils\Logger::debugLog($route, true, 64);
} catch (\Exception $e){
    \Outils\Logger::debugLog($e, true, 64);
    echo '{ "erreur":"'.$e->getMessage().'" }';
    exit;
}
if(is_a($route, "Route\\Route")){
    if(is_callable($route->cible)){
        try{
            echo call_user_func($route->cible, $route->parsedParameters);
        } catch (\Exception $e){
            \Outils\Logger::debugLog($e, true, \Outils\Logger::chargement_route); 
            if('html' === $_GET['type']){
                do {
                    xdebug_print_function_stack($e->getMessage());
                } while($e = $e->getPrevious());
            } else if('json' === $_GET['type']) {                
                echo '{ "erreur":"'.$e->getMessage().'" }';
            }
        }
    }
} else echo '{ "erreur":"Aucune Route trouvé" }';

exit;