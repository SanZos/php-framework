<?php

namespace Annotation;

/**
* Classe de gestion des annotations pour la gestion du routing
*/
class RouteAnnotation extends Annotation {
    /** RegEx de récupération des information d'une route de base du controlleur */
    const masque_classe = ";@Route\\\\param\(base_url='(?'base_url'[\w/]*)'(,?methods='(?'methods'[\w|]*)')?\);";
    /** RegEx de récupération des information de la route d'une méthone du controlleur */
    const masque_method = ";@Route\\\\param\((nom='(?'nom'\w*)')?,?(url='(?'url'[\w:/\[\]]*)')?,?(methods='(?'methods'[\w|]*)')?\);";
    
    /** \Collection\RouteCollection Tableau des annotations extraite de l'objet */
    private $route_collection;
    
    /**
    * Constructeur
    * @param $c_route \Collection\RouteCollection Collection de route ou l'on ajoute la nouvelle route.
    */
    public function __construct(\Collection\RouteCollection &$c_route){
        $this->route_collection = $c_route;
    }
    
    /**
    * Récuppération de la route dans le controlleur
    * @param $controller \Controlleur\Controlleur Controlleur d'ou l'ont doit extaires les annoataions.
    */
    public function parseRoute(\Controlleur\Controlleur $controller){
        $this->RefClass = new \ReflectionClass($controller);
        $base = $this->_base_route();
        $this->_route($base);
    }
    
    /**
    * Retourne la route de base du controlleur
    * @return String Route de base du controlleur
    */
    private function _base_route(){
        if(\Outils\Regex::match(static::masque_classe, $this->RefClass->getDocComment(), $matche)){
            return strtolower($matche['base_url']);
        } else return '';
    }
    
    /**
    * Ajoute la route a la collection
    * @param $base String Route de base du controlleur
    * @return String Route de la méthode
    */
    private function _route($base){
        foreach($this->RefClass->getMethods(\ReflectionMethod::IS_PUBLIC) as $methods){
            if(\Outils\Regex::match(static::masque_method, $methods->getDocComment(), $matche)){
                $route = new \Route\Route($matche['nom'], $base.$matche['url'], $matche['methods'], "\\".$this->RefClass->getName()."::".$methods->getName());
                $this->route_collection->addRoute($route);
            }
        }
    }
    
}