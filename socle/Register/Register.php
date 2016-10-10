<?php

namespace Register;

abstract class Register {
    const match = "/^class (?'class'\w*)/m";
    protected static $type;

    public static function autoRegister(\Collection\Collection &$c) {
        $fonction = "parseRegister".static::$type;
        $class = "\\Annotation\\".static::$type."Annotation";
        $config = \Config\Config::getRegisterConfig();
        $annotation = new \Annotation\RegisterAnnotation();
        $typeAnnotation = new $class($c);

        if(!is_array($config->{static::$type})) return;
        foreach ($config->{static::$type} as $registerType => $active) {
            if('true' == $active){
                $config = \Config\Config::getRootConfig();
                if(!is_array($config->dir)) return;
                foreach($config->dir as $dir){

                    // Vérification de l'existance du répertoire avant l'itération
                    if(!is_dir($_SERVER['DOCUMENT_ROOT'].$dir.$registerType)) continue;
                    $dir = new \DirectoryIterator($_SERVER['DOCUMENT_ROOT'].$dir.$registerType);
                    foreach ($dir as $fileinfo) {
                        if (!$fileinfo->isDot() && !$fileinfo->isDir()) {
                            if(\Outils\Regex::Match(self::match,file_get_contents($fileinfo->getPathname()), $match)){
                                $annotation->$fonction('\\'.$registerType.'\\'.$match['class'], $typeAnnotation);
                            }
                        }
                    }
                }
            }
        }
    }
}