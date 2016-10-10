<?php

namespace Route;

/**
* Classe de modélisation des routes
*/
class Route {
    
    /** @var nom de la route */
    private $nom;
    /** @var url de la route */
    private $url;
    /** @var methode d'appel de la route */
    private $method;
    /** @var controleur et methode cible de la route */
    private $cible;
    /** @var paramètres a passer a la méthode $cible de la route */
    private $parsedParameters;
    
    /**
    * Constructeur de la route
    * @param $nom string self::nom
    * @param $url string self::url
    * @param $method string self::method
    * @param $cible string self::cible
    */
    public function __construct($nom = 'default', $url = null, $method = null, $cible = null){
        $this->nom = $nom;
        $this->url = $url;
        $this->method = $method;
        $this->cible = $cible;
    }
    
    /**
    * function __get($attribut)
    * @param string $attribut Nom de l'attribut auquel on veut accéder
    * @return mixed
    */
    public function __get($attribut){
        if(isset($this->$attribut)){
            return $this->$attribut;
        } else return null;
    }
    
    /**
    * Ajoute les paramètres de la route
    * @param $params mixef self::parsedParameters
    */
    public function setParameters($params){
        $this->parsedParameters = $params;
    }
}