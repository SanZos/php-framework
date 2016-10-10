<?php

namespace Outils;

/**
* Classe de gestion des logs
*/
class Logger {
    /** constante de selection de log */
    const route = 1;
    const manager = 2;
    const controlleur = 4;
    const entity = 8;
    const annotation = 16;
    const database = 32;
    const chargement_route = 64;
    const chargement_commande = 128;
    
    /**
    * Affichage des logs quand la propriété GET['debug'] est défini et que le niveau défini correspond au log
    * @param $info mixed Information a afficher
    * @param $trace bool Affichage de la trace ou non
    * @param $level int Niveau d'affichage du log
    */
    public static function debugLog($info, $trace = false, $level = false){
        if(isset($_GET['debug']) && (($_GET['debug'] & $level) || !$level)){
            $backtrace = debug_backtrace();
            var_dump($backtrace[0]['file']." : ".$backtrace[0]['line'],$info);
            if($trace) {
                var_dump($backtrace);
            }
        }
    }
}