<?php

namespace Collection;

/**
* Collection de commande
*/
class CommandeCollection extends Collection {
    /** @var RegEx de test de la Commande */
    const regexp_validation = ";^(?'test'{Commande});";
    
    /**
    * Ajoute une Commande a la collection
    * @param $obj \Commande\Commande Commande à ajouter a la collection
    * @param $key mixed clef d'insertion dans la collection
    */
    public function addCommande($obj, $key = null) {
        if (null == $key) {
            $this->objet[] = $obj;
        }
        else {
            $this->objet[$key] = $obj;
        }
    }
    
    /**
    * Retire la Commande de la collection
    * @param $key miexd clef d'insertion dans la collection
    */
    public function deleteCommande($key) {
        if ($this->keyExists($key)) {
            unset($this->objet[$key]);
        } else throw new \Exception('Pas de commande a cet index');
    }
    
    /**
    * Retourne la Commande qui correspond a la recherche
    * @param $key miexd clef d'insertion dans la collection
    * @param $nom string nom de la Commande
    * @param $url string url de la Commande
    * @param $method string methode d'appel de la Commande
    * @return \Commande\Commande
    */
    public function getCommande($key = null, $nom = null, $all = null) {
        try {
            if (!is_null($key)) {
                return $this->_findByKey($key);
            } else if (!is_null($nom)) {
                return $this->_findByName($nom);
            } else if(!is_null($all)) {
                return $this->_getAllCommande();
            }
        } catch(\Exception $e) {
            throw $e;
        }
        return null;
    }
    
    /**
    * Retourne la Commande qui correspond a l'url et la method demandé
    * @param $url string url de la Commande
    * @param $method string methode d'appel de la Commande
    * @retrun \Commande\Commande
    */
    private function _getAllCommande(){
        if(is_array($this->objet)){
            return $this->objet;
        }
        throw new \Exception('Pas de commande chargées');
    }
    
    /**
    * Retourne la commande par clef d'insertion
    * @param $key miexd clef d'insertion dans la collection
    * @retrun \Commande\Commande
    */
    private function _findByKey($key){
        if ($this->keyExists($key)) {
            return $this->objet[$key];
        } else throw new \Exception('Pas de commande a cet index');
    }
    
    /**
    * Retourne la Commande par son nom
    * @param $nom string nom de la Commande
    * @retrun \Commande\Commande
    */
    private function _findByName($nom){
        if(is_array($this->objet)){
            foreach($this->objet as $Commande) {
                if($Commande == $nom) {
                    return $Commande;
                }
            }
        }
        throw new \Exception('Pas de commande avec ce nom', 1);
    }
}