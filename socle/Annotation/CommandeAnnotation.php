<?php

namespace Annotation;

/**
* Classe de gestion des annotations pour la gestion des commandes
*/
class CommandeAnnotation extends Annotation {
    /** RegEx de récupération des informations d'un champ */
    const masque_signature = "/@Commande\\\\Signature\(nom=\"(?'nom'(\w*|))\"\)/iu";
    const masque_param = "/@Commande\\\\Param\(short=\"(?'short'(\w|))\"[, ]*(long=\"(?'long'\w*)\")?[, ]*(description=\"(?'desc'[\w'\/. -]*)\")?[, ]*(type=\"(?'type'(|valeur|flag))\")?[, ]*(obligatoire=\"(?'obli'(|oui|non))\")?\)/iu";
    const masque_desc = "/@Commande\\\\Description\(\"(?'desc'[\w' -]*)\"\)/iu";
    
    public function __construct(\Collection\CommandeCollection &$c_commande = null){
        $this->commande_collection = $c_commande;
    }
    
    /**
    * Récuppération de la commande dans la commande
    * @param $controller \Commande\Commande Commande d'ou l'ont doit extaires les annoataions.
    */
    public function parseCommande(\Commande\Commande $commande, $addCollection = true){
        $this->RefClass = new \ReflectionClass($commande);
        $this->_parse($addCollection);
    }
    
    /**
    * Récupération des annotations pour la construction des paramètres des commandes
    */
    private function _parse($addCollection){
        if(false !== $this->RefClass->getParentClass()) {
            if(\Outils\Regex::matchAll(static::masque_param, $this->RefClass->getParentClass()->getDocComment(), $matche)){
                $parentAnnotation = $matche;
            }
        }
        if(\Outils\Regex::matchAll(static::masque_signature, $this->RefClass->getDocComment(), $matche)){
            $annotationSign = $matche['nom'][0];
        }
        if(\Outils\Regex::matchAll(static::masque_param, $this->RefClass->getDocComment(), $matche)){
            $annotation = $matche;
        }
        if(\Outils\Regex::matchAll(static::masque_desc, $this->RefClass->getDocComment(), $matche)){
            $annotationDesc = $matche['desc'][0];
        }
        if(is_array($parentAnnotation) && is_array($annotation)){
            $this->annotation = array_merge_recursive($parentAnnotation, $annotation);
        } else if(is_array($parentAnnotation)) {
            $this->annotation = $parentAnnotation;
        } else if(is_array($annotation)) {
            $this->annotation = $annotation;
        }
        $this->annotation['description'] = $annotationDesc;
        $this->annotation['signature'] = $annotationSign;
        if(true == $addCollection) {
            $this->commande_collection->addCommande($this->RefClass->name, $annotationSign);
        }
    }
}