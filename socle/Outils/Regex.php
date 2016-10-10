<?php

namespace Outils;

/**
* Classe de gestion des expressions réguliaires PCRE
* @see http://php.net/manual/fr/ref.pcre.php
*/
class Regex {
    
    /**
    * Utilisation de preg_match_all
    * @see preg_match_all
    * @param $reg Expression réguliaire a tester
    * @param $chaine Chaine a analyser
    * @param $matches tableau de résltat de retour
    * @param $keepNum garder ou non les clef numérique
    * @return boolean Retourne si l'expression est présente dans la chaine
    */
    public static function matchAll($reg, $chaine, &$matches = array(), $keepNum = false){
        if(preg_match_all($reg, $chaine, $matches)){
            if(false === $keepNum) static::_dropNumericKeys($matches);
            return true;
        } else return false;
    }
    
    /**
    * Utilisation de preg_match
    * @see preg_match
    * @param $reg Expression réguliaire a tester
    * @param $chaine Chaine a analyser
    * @param $matches tableau de résltat de retour
    * @param $keepNum garder ou non les clef numérique
    * @return boolean Retourne si l'expression est présente dans la chaine
    */
    public static function match($reg, $chaine, &$matches = array(), $keepNum = false){
        if(preg_match($reg, $chaine, $matches)){
            if(false === $keepNum) static::_dropNumericKeys($matches);
            return true;
        } else return false;
    }
    
    /**
    * Utilisation de preg_replace
    * @see preg_replace
    * @param $reg Expression réguliaire a tester
    * @param $remplace Valeur de remplacement
    * @param $chaine Chaine a analyser
    */
    public static function replace($reg, $remplace, &$chaine){
        $chaine = preg_replace($reg, $remplace, $chaine);
    }
    
    /**
    * Supprime les clé numérique d'un tableau
    * @param array $array
    */
    private static function _dropNumericKeys(array &$array)
    {
        foreach ($array as $key => $value) {
            if (true === is_int($key)) {
                unset($array[$key]);
            }
        }
    }
}