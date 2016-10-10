<?php
namespace Manager;

use Database\Database;

/**
* Classe de gestion des entitées
* @version 0.2.1
* @author Antonin DUBOIS
* @package Manager\Manager
*/
class Manager {
    /** Constante de requête */
    const select = 'select [fields] from [table] ';
    const insert = 'insert into [table] ([fields]) values ([values]) ';
    const update = 'update [table] set [fields] ';
    const delete = 'delete from [table] where ';
    const ijoin = 'inner join [jtable] on [jparams] ';
    
    /** Constante de jointure */
    const lojoin = 'left outer join [jtable] on [jparams] ';
    const rojoin = 'right outer join [jtable] on [jparams] ';
    
    /** Constante de discrimination de résultats */
    const where = 'where ';
    const and_where = 'and ';
    const or_where = 'or ';

    /** Constante de tri */
    const order = 'order by [ordre]';
    
    /** @var \Database\Database objet de gestion de la base de donnée */
    protected $db;
    /** @var String Nom de l'entité lié au manager */
    protected $entity;
    /** @var \Annotation\DatabaseAnnotation objet de gestion des annotations de base de donnée */
    protected $annotation;
    
    /**
    * Chargement de la base de donnée en paramétre
    */
    public function __construct(){
        try {
            $this->db = Database::getInstance();
            $this->entity = $this->_getEntity();
            $this->annotation = new \Annotation\DatabaseAnnotation(new $this->entity());
        } catch (\Exception $e){
            throw $e;
        }
    }
    
    /**
    * Destructeur de la classe
    */
    public function __destruct(){
        foreach ($this as $key => $value) {
            unset($this->$key);
        }
    }
    
    /**
    * Gestion des appels dynamique inéxistant
    * @param $name String Nom de la méthode a appeler
    * @param $arguments Array Liste d'arguements passer a la fonction inconnue
    */
    public function __call($name, $arguments = null) {
        if(preg_match('/^getBy(\w+)/i', $name, $matches)) {
            return $this->getBy(array(strtolower($matches[1]) => $arguments[0]));
        }
        throw new \Exception("La méthode demandé '$name' n'éxiste pas pour la classe '".get_class($this)."'.");
        
    }
    
    /**
    * Récupération de l'entité corréspondant au manager
    * @param $full boolean
    * @return string le nom de l'entité lié au manager
    * @throw pas d'entité lié au manager
    */
    protected function _getEntity($full = true){
        $entity = substr(explode('\\', get_class($this))[1], 0, -7);
        if(class_exists('Entity\\'.$entity) && $full) {
            return 'Entity\\'.$entity;
        } else if(class_exists('Entity\\'.$entity) && !$full) {
            return $entity;
        }
        throw new \Exception("L'entité demandé '$entity' n'éxiste pas.");
    }
    
    /**
    * Récupération des champs de base de données
    * @param $select boolean
    * @param $all boolean
    * @return array[] tabelau des champs de base de donné indexer sur le nom de l'attribut de l'entité
    */
    protected function _getFieldsDBName($select = true, $all = true){
        $annotation = $this->annotation->getAnnotationFor();
        if(!$this->_getTable($table)){
            $table = strtolower($this->_getEntity(false));
        }
        foreach($annotation as $nom => $info){
            if($nom == $this->_getEntity(false) || 'orderby' === $nom) {
                continue;
            } else if($select){
                $a_return[$nom] = $table.'.'.strtolower($info['name']).' as '.$nom;
            } else if ((!isset($info['options']) || (isset($info['options']) && 'auto_increment' != $info['options'])) || $all ){
                $a_return[$nom] = strtolower($info['name']);
            }
        }
        return $a_return;
    }
    
    /**
    * Met à jour la variable de table si il y a une annotation dans l'entité
    * @param $table pointeur de nom de la table si il est saisie dans les annotations
    * @return boolean
    */
    protected function _getTable(&$table){
        $table = $this->annotation->getAnnotationFor($this->_getEntity(false));
        if($table) {
            return true;
        }
        return false;
    }
    
    /**
    * Créer la requête SQL lié a la demande
    * @param $type string Type de requête @see const
    * @param $fields string Champs a selectionner ou a mettre a jour
    * @param $jtable string Tables de jointures
    * @param $jparams string paramaètres de jointures
    * @param $values string Valeurs a mettre a jour
    * @return string @see _valorise
    */
    protected function _createSql($type, $fields = null, $jtable = null, $jparams = null, $values = null){
        if(is_null($fields)){
            if('select' == $type) $fields = implode(', ',$this->_getFieldsDBName());
        }
        
        if(!$this->_getTable($table)){
            $table = strtolower($this->_getEntity(false));
        }
        
        $param = array('s' => array('[fields]', '[table]', '[jtable]', '[jparams]', '[values]'),'r' => array($fields, $table, $jtable, $jparams, $values));
        return $this->_valorise($type, $param);
    }
    
    /**
    * Valorise la requête avec les paramètre envoyer
    * @param $type string Type de requête @see const
    * @param $a_param array Tableau de valorisation
    * @return string La requête, valorisé avec les paramètres, a executé
    */
    protected function _valorise($type, &$a_param){
        return str_replace($a_param['s'], $a_param['r'], constant(get_class($this)."::$type"));
    }
    
    /**
    * Linéarise un tableau
    * @param $array array Tableau a linéarisé
    * @return string Tableau linéarisé
    */
    protected static function _linearize(array $array){
        return implode(', ', $array);
    }
    
    /**
    * Construit et exéctute la requête de récupération de tous les enregistrements
    */
    public function getAll($ordre = null){
        $orderBy = $this->annotation->getAnnotationForOrderBy();
        try {
            if(null !== $ordre) {
                $ordre = str_replace('[ordre]', $ordre, static::order);
            } else if(null != $orderBy) {
                $ordre = str_replace('[ordre]', implode(',', $orderBy), static::order);
            } else {
                $ordre = '';
            }
            $sql = $this->_createSql('select').$ordre;
            $statement = $this->db->prepare($sql);
            $statement->execute();
            return $statement->fetchAll(\PDO::FETCH_CLASS, $this->entity);
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }
    
    /**
    * Création de la requête de séléction.
    * @param $info array Tableau sous la forme (Attribut => Valeur)
    * @return array|\Entity\Entity Tableau de résultat ou l'entité si il n'y en a qu'une de séléctionné en base
    */
    public function getBy($info){
        if(!is_array($info)) throw new \Exception('La fonction appelée ne contient pas les bon paramètres');
        try {
            $returnNull = $info['keepEmpty'];
            unset($info['keepEmpty']);
            $orderBy = $this->annotation->getAnnotationForOrderBy();
            $sql = $this->_createSql('select');
            \Outils\Logger::debugLog($sql, false, \Outils\Logger::database);
            if(isset($info['order'])) {
                $ordre = str_replace('[ordre]', $info['order'], static::order);
                unset($info['order']);
            } else if(null != $orderBy) {
                $ordre = str_replace('[ordre]', implode(',', $orderBy), static::order);
            } else {
                $ordre = '';
            }
            $sql .= static::where;
            foreach($info as $attribut => &$value){
                $annotation = $this->annotation->getAnnotationFor($attribut);
                if(null === $annotation) $annotation['name'] = $attribut;
                $sql .= $annotation['name'].' = :'.$attribut.' '.static::and_where;
                if(is_a($value, '\\Entity\\Entity')){
                    $bind[':'.$attribut] = $value->id;
                } else { 
                    $bind[':'.$attribut] = $value;
                }
            }
            \Outils\Regex::replace('/'.static::and_where.'$/', '', $sql);
            $sql .= $ordre;

            $statement = $this->db->prepare($sql);
            $statement->execute($bind);
            
            \Outils\Logger::debugLog($bind, false, \Outils\Logger::database);
            \Outils\Logger::debugLog($statement, false, \Outils\Logger::database);
            $array = $statement->fetchAll(\PDO::FETCH_CLASS, $this->entity);
            if(1 == count($array)){
                $array = $array[0];
            } else if(empty($array) && isset($returnNull) && true === $returnNull) {
                $array = null;
            } else if(empty($array) && ('PUT' !== $_SERVER['REQUEST_METHOD'] && 'console.php' !== $_SERVER['PHP_SELF'])){
                throw new \Exception("Impossible de chargé l'entité {$this->entity} avec les valeurs ".var_export($info, true), 2);
            }
            return $array;
            
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
    }
    
    /**
    * Création d'un entité vide
    * @return \Entity\Entity
    */
    public function create(){
        return new $this->entity();
    }
    
    /**
    * Sauvegarde de l'entité manipulé
    * @param $entity \Entity\Entity Entité a sauvegarder
    * @return string La requête executé
    */
    public function save(\Entity\Entity &$entity){
        
        $fields = $this->_getFieldsDBName(false);
        $values = null;
        $bind = null;
        $exist = $this->getBy(array('id' => $entity->id, 'keepEmpty' => true));
        if(null !== $exist && 0 < count($exist)){
            $type = 'update';
        } else {
            $type = 'insert';
        }

        foreach ($fields as $name => $value) {
            if('insert' == $type){
                if('id' != $name){
                    $values[] = ':'.$name;
                    $bindValue = $entity->$name;
                    if(is_a($bindValue,'\\Entity\\Entity')){
                        $bindValue = $bindValue->id;
                    }
                    $bind[':'.$name] = $bindValue;
                } else {
                    unset($fields[$name]);
                }
            } else if('update' == $type){
                if('id' != $name){
                    $fields[$name] .= ' = :'.$name;
                    $bindValue = $entity->$name;
                    if(is_a($bindValue,'\\Entity\\Entity')){
                        $bindValue = $bindValue->id;
                    }
                    $bind[':'.$name] = $bindValue;
                } else {
                    unset($fields[$name]);
                }
            }
        }
        
        if(is_array($values)) {
            $values = static::_linearize($values);
        }
        
        $fields = static::_linearize($fields);
        
        $sql = $this->_createSql($type, $fields, null, null, $values);
        if('update' == $type){
            $annotation = $this->annotation->getAnnotationFor('id');
            $sql .= static::where.$annotation['name'].' = :id';
            $bind[':id'] = $entity->id;
        }

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute($bind);
            
            if('update' == $type){
                $insert_id = $entity->id;
            } else {
                $insert_id = $this->db->lastInsertId();
                $entity->id = $insert_id;
            }
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
        
        return $insert_id;
    }
    
    /**
    * Supprime de l'entité manipulé
    * @param $entity \Entity\Entity Entité a supprimé
    * @return string La requête executé
    */
    public function delete(\Entity\Entity $entity){
        if(is_null($entity->id)){
            throw new \Exception("L'entité n'éxiste pas en base de donnée.");
        }
        $sql = $this->_createSql('delete');
        $annotation = $this->annotation->getAnnotationFor('id');
        $sql .= $annotation['name'].' = :id';
        $bind[':id'] = $entity->id;
        try {
            $statement = $this->db->prepare($sql);
            $statement->execute($bind);
        } catch (\PDOException $e) {
            throw new \Exception($e->getMessage());
        }
        return $statement;
    }
    
    /**
    * Création d'une entité a partir du JSON en entré
    * @param $data JSON en entré
    * @return \Entity\Entity
    */
    public function loadFromJSON($data){
        $entity = new $this->entity();
        foreach($this->annotation->getAnnotationFor() as $nom => $info){
            if(!is_array($info)) continue;
            $entity->$nom = $data->$nom;
        }
        return $entity;
    }
}