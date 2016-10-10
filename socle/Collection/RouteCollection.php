<?php

namespace Collection;

/**
* Collection de route
*/
class RouteCollection extends Collection {
    /** @var RegEx de test de la route */
    const regexp_validation = ";^(?'test'{route})((?'replace'\[n(?'param':\w*)?\])|(?'numeric'\d*));";
    
    /**
    * Ajoute une route a la collection
    * @param $obj \Route\Route route à ajouter a la collection
    * @param $key mixed clef d'insertion dans la collection
    */
    public function addRoute($obj, $key = null) {
        if (null == $key) {
            $this->objet[] = $obj;
        } else {
            $this->objet[$key] = $obj;
        }
    }
    
    /**
    * Retire la route de la collection
    * @param $key miexd clef d'insertion dans la collection
    */
    public function deleteRoute($key) {
        if ($this->keyExists($key)) {
            unset($this->objet[$key]);
        } else throw new \Exception('Pas de route a cet index');
    }
    
    /**
    * Retourne la route qui correspond a la recherche
    * @param $key miexd clef d'insertion dans la collection
    * @param $nom string nom de la route
    * @param $url string url de la route
    * @param $method string methode d'appel de la route
    * @return \Route\Route
    */
    public function getRoute($key = null, $nom = null, $url = null, $method = 'GET') {
        if (!is_null($key)) {
            return $this->_findByKey($key);
        } else if (!is_null($nom)) {
            return $this->_findByName($nom);
        } else if(!is_null($url)) {
            return $this->_findByRoute($url, $method);
        }
        return null;
    }
    
    /**
    * Retourne la route qui correspond a l'url et la method demandé
    * @param $url string url de la route
    * @param $method string methode d'appel de la route
    * @retrun \Route\Route
    */
    private function _findByRoute($url, $method){
        $url = strtok($url,"?");
        \Outils\Logger::debugLog($url, false, \Outils\Logger::route);
        if(is_array($config->objet)) {
            foreach($this->objet as $route) {
                if($route->method == $method) {
                    $rurl = strtolower($route->url);
                    $rewrite_rule = str_replace("{route}", preg_replace("/(\[n(:\w*)?\]*)/","", $rurl), static::regexp_validation);
                    \Outils\Logger::debugLog($route->method, false, \Outils\Logger::route);
                    \Outils\Logger::debugLog($rewrite_rule, false, \Outils\Logger::route);
                    \Outils\Logger::debugLog($rurl, false, \Outils\Logger::route);
                    \Outils\Logger::debugLog($url, false, \Outils\Logger::route);
                    \Outils\Logger::debugLog(preg_replace("/(\[n(:\w*)?]*)/","", $rurl), false, \Outils\Logger::route);
                    if(strtolower($rurl) == $url || strtolower($rurl).'/' == $url || ('/' === substr($rurl, -1) && substr($rurl, 0, -1) == $url)) {
                        return $route;
                    } else {
                        \Outils\Regex::match($rewrite_rule, $url, $match);
                        \Outils\Logger::debugLog($route, false, \Outils\Logger::route);
                        \Outils\Logger::debugLog($match, false, \Outils\Logger::route);
                        if(isset($match['numeric']) && !empty($match['numeric'])) {
                            $route->setParameters($match['numeric']);
                            return $route;
                        }
                    }
                }
            }
        }
        throw new \Exception('Pas de route avec ce nom et/ou cette method');
    }
    
    /**
    * Retourne la route par clef d'insertion
    * @param $key miexd clef d'insertion dans la collection
    * @retrun \Route\Route
    */
    private function _findByKey($key){
        if ($this->keyExists($key)) {
            return $this->objet[$key];
        } else throw new \Exception('Pas de route a cet index');
    }
    
    /**
    * Retourne la route par son nom
    * @param $nom string nom de la route
    * @retrun \Route\Route
    */
    private function _findByName($nom){
        foreach($this->objet as $route) {
            if($route->nom == $nom) {
                return $route;
            }
        }
        throw new \Exception('Pas de route avec ce nom');
    }
}