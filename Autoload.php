<?php
/**
 * Gestion de l'autoload
 * @param Nom de la classe a rechercher
 */
function autoload($class_name) {
    $object_name =  explode('\\', $class_name);
    if(count($object_name) > 1){
	    $class_name = $object_name[1];
    } else {
	    $class_name = $object_name[0];
    }
	// config Autoload[]
	if(file_exists(__DIR__.'/socle/Config/Config.php')) {
		include_once(__DIR__.'/socle/Config/Config.php');
		$directorys = \Config\Config::getAutoloadConfig();
	} else {
		throw new \Exception('Pas de configuration trouvée');
	}
	//for each directory
	foreach($directorys->dir as $directory){
		//see if the file exsists
		if(file_exists(__DIR__.'/'.$directory.$class_name . '.php')) {
			include_once(__DIR__.'/'.$directory.$class_name . '.php');
			return;
		}            
	}
    return false;
}

spl_autoload_register ('autoload');