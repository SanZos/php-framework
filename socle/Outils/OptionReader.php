<?php

namespace Outils;

class OptionReader {
    public static function getopt($shortopts = null, $longopts = null) {
        global $argv;
        if(null === $shortopts && null === $longopts) return null;

        \Outils\Regex::matchAll('/([a-zA-Z][:]{0,2})/', $shortopts, $tabShortopts, true);
        $tabShortopts = $tabShortopts[0];

        foreach($argv as $index => $argument) {
            if(0 === strpos($argument, '-')) {
                $argument = self::_sanitize($argument, $type);
                self::_setOption($argument, $type, $tabShortopts, $longopts, $optionSet, $argv[$index+1]);
            }
        }
        return $optionSet;
    }

    private static function _sanitize($arg, & $type){
        $type['longeur'] = substr_count($arg, '-');
        $type['option'] = substr_count($arg, ':');
        return str_replace(array('-', ':'), '', $arg);
    }

    private static function _setOption($argument, $type, $tabShortopts, $longopts, & $optionSet, & $nextParameter){
        if(1 === $type['longeur']) {
            if(true !== self::_parseShortOption($tabShortopts, $argument, $currentOption, $optionType)) return;
        } else {
            if(true !== self::_parseLongOption($longopts, $argument, $currentOption, $optionType)) return;
        }
        switch ($optionType['option']){
            case 1:
            case 2:
                if(1 < strlen($argument) && 1 === $type['longeur']) {
                    $optionSet[$currentOption] = substr($argument, 1);
                } else if('-' !== substr($nextParameter, 0, 1)){
                    $optionSet[$currentOption] = $nextParameter;
                }
                break;

            default:
                $optionSet[$currentOption] = false;
                break;
        }
        if(!isset($optionSet[$currentOption])){
            if(1 === $optionType['option']) {
                throw new \Exception('Le paramètre '.$currentOption.' nécessite une valeur');
            } else $optionSet[$currentOption] = false;
        }
    }

    private static function _parseShortOption($tabShortopts, $argument, & $currentOption, & $optionType){
        foreach($tabShortopts as $shortOptions){
            $currentOption = self::_sanitize($shortOptions, $optionType);
            if($currentOption === substr($argument, 0, 1)) {
                return true;
            }
        }
        return false;
    }
    
    private static function _parseLongOption($longopts, $argument, & $currentOption, & $optionType){
        foreach($longopts as $longopts){
            $currentOption = self::_sanitize($longopts, $optionType);
            if($currentOption === $argument) {
                return true;
            }
        }
        unset($optionType);
        return false;
    }
}