<?php

namespace Annotation;


/**
* Classe de gestion des annotations
*/
class Annotation {
    /** String Nom de l'objet de référence */
    protected $RefClass;
    
    /** Array Tableau des annotations extraite de l'objet */
    protected $annotation = array();
    
    /**
    * Gestion des appels dynamique inéxistant
    * @param $name String Nom de la méthode a appeler
    * @param $arguments Array Liste d'arguements passer a la fonction inconnue
    */
    public function __call($name, $arguments = null) {
        if(\Outils\Regex::match("/^getAnnotationFor(?'name'\w+)/i", $name, $matches)) {
            return $this->getAnnotationFor(strtolower($matches['name']));
        } else
            throw new \Exception("La méthode demandé '$name' n'éxiste pas pour la classe '".get_class($this)."'.");
    }
    
    /**
    * Rénvoie l'annotation demandé si aucune demande, renvoie toutes les annotations
    * @param $info null|string Nom de l'objet dont on veut l'annotation sinon on renvoie toutes les annotation
    * @return object|null l'objet d'annotation ou null si aucune annotation n'est trouvé
    */
    public function getAnnotationFor($info = null){
        if(is_null($info)){
            return (object) $this->annotation;
        } else {
            if(isset($this->annotation[$info])){
                return $this->annotation[$info];
            } else {
                return null;
            }
        }
    }
    
}