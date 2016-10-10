<?php

namespace Annotation;


/**
* Classe de gestion des annotations pour la gestion de l'autoregister
*/
class RegisterAnnotation extends Annotation {
    const masque_route = "/@Register\\\\Route\((?'type'autoload|order=(?'ordre'\d*))\)/";
    const masque_commande = "/@Register\\\\Commande\((?'type'autoload|order=(?'ordre'\d*))\)/";
    
    public function parseRegisterRoute($controller, \Annotation\RouteAnnotation &$ra){
        $this->RefClass = new \ReflectionClass($controller);
        $loadInformation = $this->_parse('route');
        if('autoload' === $loadInformation){
            $ra->parseRoute($this->RefClass->newInstanceArgs());
        }
    }
    
    public function parseRegisterCommande($controller, \Annotation\CommandeAnnotation &$ca){
        $this->RefClass = new \ReflectionClass($controller);

        $loadInformation = $this->_parse('commande');
        
        if('autoload' === $loadInformation){
            $ca->parseCommande($this->RefClass->newInstanceArgs());
        }
    }
    
    private function _parse($type){
        if('route' == $type) {
            $regex = static::masque_route;
        } else if ('commande' == $type) {
            $regex = static::masque_commande;
        }
        if(\Outils\Regex::match($regex, $this->RefClass->getDocComment(), $matche)){
            return strtolower($matche['type']);
        } else return '';
    }
}