<?php

namespace Controlleur;

/**
* Classe controlleur
*/
class Controlleur {
    
    /**
    * Récupération des données passer en PUT et DELETE
    * @return array|string
    */
    protected static function _getJsonData(){
        $data = file_get_contents('php://input');
        $json = json_decode($data);
        if(empty($json)){
            parse_str($data, $a);
            return $a;
        } else return $json;
    }
    
    /**
    * Variabilise et renvoie le JSON pour affichage au client
    * @param $fichier fichier JSON a variabilisé
    * @param $info Donnée a intégrer au JSON
    * @param $followup variabilisé toutes l'arborésence
    * @return string Le JSON variabilisé demandé
    */
    protected static function _serve($fichier, $info = null, $followup = true, $racine = '/'){
        \Outils\Logger::debugLog($info, false, \Outils\Logger::controlleur);
        
        static::_replaceChamps($replace, $fichier, $info, $racine);
        static::_replaceFonctions($replace, $followup, $info);
        
        return $replace;
    }
    
    /**
    * Remplacement des champs du JSON pour la variabilisation
    * @param $replace Contenue du fichier JSON
    * @param $fichier fichier JSON a variabilisé
    * @param $info Donnée a intégrer au JSON
    */
    protected static function _replaceChamps(&$replace, $fichier, $info = null, $racine = '/'){
        $replace = file_get_contents(__DIR__."/../../view$racine".$fichier);
        preg_match_all("/.*{{(?'replace'\w*)}}.*/", $replace, $match);
        foreach($match['replace'] as &$parameter){
            if(is_a($info, "\\Entity\\Entity")){
                if('int' == \Annotation\DatabaseAnnotation::getTypeChamp($info, $parameter)){
                    if(is_null($info->$parameter)) {
                        $replace = str_replace('{{'.$parameter.'}}', '""', $replace);
                    } else {
                        $replace = str_replace('{{'.$parameter.'}}', $info->$parameter, $replace);
                    }
                } else {
                    $replace = str_replace('{{'.$parameter.'}}', '"'.$info->$parameter.'"', $replace);
                }
            } else if(null !== $info) {
                if(is_numeric($info[$parameter])){
                    $replace = str_replace('{{'.$parameter.'}}', $info[$parameter], $replace);
                } else {
                    $replace = str_replace('{{'.$parameter.'}}', '"'.$info[$parameter].'"', $replace);
                }
            } else {
                $replace = str_replace('{{'.$parameter.'}}', '""', $replace);
            }
        }
    }
    
    /**
    * Remplacement des fonction du JSON pour la variabilisation
    * @param $replace Contenue du fichier JSON
    * @param $followup variabilisé toutes l'arborésence
    * @param $info Donnée a intégrer au JSON
    */
    protected static function _replaceFonctions(&$replace, $followup, &$info){
        try {
            \Outils\Logger::debugLog($replace, false, \Outils\Logger::controlleur);
            \Outils\Logger::debugLog($followup, false, \Outils\Logger::controlleur);
            \Outils\Logger::debugLog($info, false, \Outils\Logger::controlleur);
            
            \Outils\Regex::matchAll("/.*{%(?'function'[\w\(\) ,\.]*)%}.*/", $replace, $matches);
            \Outils\Logger::debugLog($matches, false, \Outils\Logger::controlleur);
            foreach($matches['function'] as &$function){
                if(\Outils\Regex::match("/.*include \((?'entity'[\w]+).(?'ext'[\w]+)\).*/", $function, $match)){
                    if($followup){
                        if(isset($match['entity'])) {
                            if(is_array($info)){
                                foreach($info as $entity){
                                    static::_byEntity($entity, $match, $function, $replace);
                                }
                            } else if (is_a($info, '\\Entity\\Entity')) {
                                static::_byEntity($info, $match, $function, $replace);
                            } else {
                                static::_byEntity($info, $match, $function, $replace);
                            }
                        }
                    } else {
                        $replace = str_replace("{%$function%}", "", $replace);
                    }
                } 
            } 
        } catch (\Exception $e){
            throw new \Exception("Impossible de chargé la suite de noeud.", 0, $e);
        }
    }
    
    /**
    * Récupération de la suite des JSON a interpreté
    * @param $info entité a vérifié
    * @param $match Nom de l'entité suivante
    * @param $function fonction a appliqué
    * @param $replace Contenue du fichier JSON
    */
    protected static function _byEntity(&$info, &$match, $function, &$replace){
        $entityName = ucwords($match['entity'], " \t\r\n\f\v_");
        if($entityName === 'Fluxapplication') $entityName = 'FluxApplication';
        if($entityName === 'Listecommentaire') $entityName = 'ListeCommentaire';
        
        \Outils\Logger::debugLog($info, false, \Outils\Logger::controlleur);
        \Outils\Logger::debugLog(($info->id !== null), false, \Outils\Logger::controlleur);
        
        if(is_a($info, '\\Entity\\Entity') && null === $info->id){
            $contraite = 'new';
        } else {
            $contraite = static::_getNextRelation($info, $entityName);
        }
        \Outils\Logger::debugLog('Contrainte suivante :', false, \Outils\Logger::controlleur);
        \Outils\Logger::debugLog($contraite, false, \Outils\Logger::controlleur);
        
        $manager = '\\Manager\\'.$entityName.'Manager';
        $controlleur = '\\Controlleur\\'.$entityName.'Controlleur';

        if(is_a($contraite, '\\Entity\\Entity')){
            $nextEntity = $contraite;
        } else if('new' == $contraite) {
            $nextEntity = (new $manager())->create();
        } else if(!empty($contraite)) {
            $nextEntity = (new $manager())->getBy($contraite);
        } else {
            $nextEntity = (new $manager())->getAll();
        }
        \Outils\Logger::debugLog($nextEntity, false, \Outils\Logger::controlleur);
        
        static::_callOther(new $controlleur(), $nextEntity, $placeholder);
        $replace = str_replace("{%$function%}", $placeholder, $replace);
    }
    
    /**
    * Récupération de la suite des JSON a interpreté
    * @param $entity entité en cours
    * @param $nextEntity Nom de l'entité suivante
    * @return Contrainte entre les entité ($entity et $nextEntity)
    */
    protected static function _getNextRelation($entity, $nextEntity){
        if(empty($entity)) return '';
        if(!is_a($entity, '\\Entity\\Entity') && is_callable($entity)) {
            $entity = new $entity();
        } else if (!is_a($entity, '\\Entity\\Entity')) {
            return '';
        }

        $nextEntity = strtolower($nextEntity);
        $annotations = new \Annotation\DatabaseAnnotation($entity);
        $contraite = '';
        \Outils\Logger::debugLog($annotations, false, \Outils\Logger::controlleur);
        foreach($annotations->getAnnotationFor() as $champ => $annotation){
            if(isset($annotation['join'])) {
                foreach($annotation['join'] as $jointure){
                    if($jointure['entite'] == $nextEntity){
                        if(isset($jointure['type']) && 'externe' == $jointure['type']){
                            $contraite = [$jointure['champ'] => $entity->$champ];
                        } else {
                            $contraite = $entity->$nextEntity;
                        }
                    }
                }
            }
        }
        return $contraite;
    }
    
    /**
    * Appel du controlleur suivant pour la suite de fonction
    * @param $control Controlleur suivant a appeler
    * @param $info entité a intégrer au JSON
    * @param $stream JSON global
    */
    protected static function _callOther(\Controlleur\Controlleur $control, $info, &$stream){
        if(is_array($info)){
            foreach($info as &$entity){
                $stream .= $control->View($entity->id).',';
            }
            \Outils\Regex::replace('/,$/', '', $stream);
            
            \Outils\Logger::debugLog($stream, false, \Outils\Logger::controlleur);
        } else if (is_a($info, "\\Entity\\Entity")){
            if(null !== $info->id) {
                $stream = $control->View($info->id);
            } else {
                $stream = $control->Nouveaux();
            }
        } else if (empty($info)){
            \Outils\Logger::debugLog($stream, false, \Outils\Logger::controlleur);
            $stream = $control->Nouveaux();
        }
    }
}