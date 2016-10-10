<?php

namespace Annotation;


/**
* Classe de gestion des annotations pour la gestion de l'ORM
*/
class DatabaseAnnotation extends Annotation {
    /** RegEx de récupération des informations d'un champ */
    const masque_all = "/@DB\\\\Field\(name='(?'name'\w*)'(,?options='(?'options'\w*)')?(,?type='(?'type'\w*)')?\)/";
    /** RegEx de récupération des informations d'une table */
    const masque_table = "/@DB\\\\Table\('(?'table'\w*)'\)/";
    /** RegEx de récupération des informations de jointure entre les entitées */
    const masque_jointure = "/@DB\\\\Join\(entity=\'(?'entite'\w*)\',field=\'(?'champ'\w*)\'(,type=\'(?'type'\w*)\')?\)/";
    /** RegEx de récupération des informations de trie entre les entitées */
    const masque_order = "/@DB\\\\Order\((?'ordre'\w*)\)/";
  
    /**
    * Constructeur
    * @param $entity \Entity\Entity entité d'ou l'ont doit extaires les annoataions.
    */
    public function __construct(\Entity\Entity $entity){
        $this->RefClass = new \ReflectionClass($entity);
        $this->_getTable();
        $this->_getFields();
        $this->_getJoin();
        $this->_getOrder();
    }

    /**
    * Récupréation des annotations de la classe pour récupérer la table
    */
    protected function _getTable(){
        if(\Outils\Regex::match(static::masque_table, $this->RefClass->getDocComment(), $matche)){
            $this->annotation[$this->RefClass->getShortName()] = $matche['table'];
        }
    }
    
    /**
    * Récupréation des annotations des attributs pour avoir les champs de la base de données
    */
    protected function _getFields(){
        foreach($this->RefClass->getProperties() as $propertie){
            if(\Outils\Regex::match(static::masque_all, $propertie->getDocComment(), $matche, \PREG_SET_ORDER)) {
                foreach($matche as $name => $value){
                    $this->annotation[$propertie->name][$name] = strtolower($value);
                }
            }
        }
    }
    
    /**
    * Récupréation des annotations des jointures des champs avec les entité
    */
    protected function _getJoin(){
        foreach($this->RefClass->getProperties() as $propertie){
            if(\Outils\Regex::matchAll(static::masque_jointure, $propertie->getDocComment(), $matche)) {
                foreach($matche as $name => $value){
                    foreach($value as $k => $v){
                        $this->annotation[$propertie->name]['join'][$k][$name] = strtolower($v);
                    }
                }
            }
        }
    }

    /**
    * Récupréation des annotations de d'ordre
    */
    protected function _getOrder(){
        foreach($this->RefClass->getProperties() as $propertie){
            if(\Outils\Regex::matchAll(static::masque_order, $propertie->getDocComment(), $matche)) {
                foreach($matche as $name => $value){
                    foreach($value as $k => $v){
                        $this->annotation['orderby'][$k] = $propertie->name;
                    }
                }
            }
        }
    }
    
    /**
    * Récupréation des annotations des attributs pour avoir le type des champs de la base de données
    * @param $objet Classe de l'objet
    * @param $parameter Paramétre de l'objet dont on veut avoir le type
    */
    public static function getTypeChamp($objet, $parameter){
        $propertie = new \ReflectionProperty($objet, $parameter);
        if(\Outils\Regex::match(static::masque_all, $propertie->getDocComment(), $matche)) {
            if(isset($matche['type'])) {
                return $matche['type'];
            } else return null;
        }
    }

    public static function getJointure($objet, $attribut){
        $propertie = new \ReflectionProperty($objet, $attribut);
        if(\Outils\Regex::match(static::masque_jointure, $propertie->getDocComment(), $matche)) {
            if(isset($matche['type']) && 'externe' !== $matche['type']) {
                $aJoin['entite'] = strtolower($matche['entite']);
                $aJoin['champ'] = strtolower($matche['champ']);
                return $aJoin;
            } else return null;
        }
    }
}
