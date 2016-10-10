<?php
namespace Entity;

/**
 * Classe de modélisation des objets de la base de donnée 
 * @version 0.1
 * @package Entity\Entity
 * @author Antonin DUBOIS
 * @author Damien Viaud
 */
class Entity {
	 
	/**
	 * function __get($attribut)
	 * @param string $attribut  Nom de l'attribut auquel on veut accéder
	 * @return mixed|exception
	 */
	public function __get($attribut){
		if(isset($this->$attribut)){
			$aJoin = $this->_populate($attribut);
			if(null !== $aJoin){
				return $aJoin;
			} else {
				return $this->$attribut;
			}
		} else null;
	}

	protected function _populate($attribut){
		$aEntity = \Annotation\DatabaseAnnotation::getJointure($this, $attribut);
		if(null !== $aEntity){
			$manager = '\\Manager\\'.ucwords($aEntity['entite']).'Manager';
			return (new $manager)->getBy(array($aEntity['champ'] => $this->$attribut));
		} else return null;
	}
	
	/**
	 * function __set($attribut, $valeur)
	 * @param string $attribut Nom de l'attribut que l'on veut mettre à jour
	 * @param mixed $valeur Valeur que l'on veut mettre à jour
	 */	
	public function __set($attribut, $valeur){
		$this->$attribut = $valeur;
	}

	/**
	*
	*/
	public function __isset($attribut){
		return isset($this->$attribut);
	}

	/**
	 * Destructeur de la classe
	 */	
	public function __destruct(){
		foreach ($this as $key => $value) {
			unset($this->$key);
		}
	}
}