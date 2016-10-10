<?php
/**
 * Fichier de classe Config\Config.
 *
 * @version 0.1
 */
namespace Config;

/**
 * Classe de gestion de la configuration.
 *
 * @author Antonin DUBOIS
 */
class Config {
	/** @var String endroit du fichier de configuration. */
	private static $_config_filename = 'config.ini';

	public static function __callStatic($name, $arguments) {
		if(preg_match('/^get(\w+)config/i', $name, $matches)) {
            return self::call(ucfirst($matches[1]));
        }
	}
	
	private static function call($info){
		$files = self::getConfigFile();
		if(is_array($files)){
			$o_config = array();
			foreach($files as $file){
				$o_config = array_merge_recursive($o_config, parse_ini_file($_SERVER['DOCUMENT_ROOT'].$file, $info, INI_SCANNER_RAW));
			}
		} else $o_config = parse_ini_file($_SERVER['DOCUMENT_ROOT'].$files, $info, INI_SCANNER_RAW);
		return (object) $o_config[$info];
	}

	private static function getConfigFile(){
		$o_config = parse_ini_file($_SERVER['DOCUMENT_ROOT'].'/'.self::$_config_filename, 'Config', INI_SCANNER_RAW);
		return $o_config['Config']['load'];
	}

	/**
	 * Destructeur de la classe.
	 *
	 */	
	public function __destruct(){
		foreach ($this as $key => $value) {
			unset($this->$key);
		}
	}
}


