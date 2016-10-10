<?php

namespace Collection;

/**
* Collection de route
*/
abstract class Collection {
    /** @var array d'objet */
    protected $objet;
    
    /**
    * Constructeur de la collection
    * @param $array collection de route déjà existantes
    */
    public function __construct($array = null) {
        $this->objet = $array;
    }
    
    /**
    * Retourne la liste des id des objet
    * @retrun id
    */
    public function keys() {
        return array_keys($this->objet);
    }
    
    /**
    * Retourne le nombre de route dans la collection
    * @return int
    */
    public function length() {
        return count($this->objet);
    }
    
    /**
    * Verifie l'existance d'une route
    * @param $key miexd clef d'insertion dans la collection
    * @return bool
    */
    public function keyExists($key) {
        return isset($this->objet[$key]);
    }
}