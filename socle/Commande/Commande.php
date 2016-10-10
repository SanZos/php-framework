<?php

namespace Commande;

/**
* Classe de gestion des commandes
* @Commande\Param(short="h", long="help", description="Affiche cette aide", type="flag", obligatoire="non")
*/
class Commande {
    
    /** @var nom de la route */
    protected $nom;
    /** Array des options courte -(une lettre) disponible pour une commande */
    protected $shortopts;
    /** Array des options longue --(un mot) disponible pour une commande */
    protected $longopts;
    /** String descriptions d'une options disponible pour une commande */
    protected $description;
    /** String type de paramètre attendu d'un option pour une commande */
    protected $type;
    /** String attendu obligatoirement pour une option pour une commande */
    protected $obligatoire;
    
    /** Array options passer a la ligne de commande */
    protected $option;
    
    public function __construct($load = false){
        if(true === $load){
            $this->_loadAnnotation();
            $this->_optionCommandeParser();
        }
    }
    
    /**
    * Récupération des annotations pour construire les paramètres de la ligne de commande
    */
    protected function _loadAnnotation(){
        $annotation = new \Annotation\CommandeAnnotation();
        $annotation->parseCommande($this, false);
        $this->shortopts = $annotation->getAnnotationForShort();
        $this->longopts = $annotation->getAnnotationForLong();
        $this->description = $annotation->getAnnotationForDesc();
        $this->type = $annotation->getAnnotationForType();
        $this->obligatoire = $annotation->getAnnotationForObli();
        $this->descriptionCommande = $annotation->getAnnotationForDescription();
        $this->signature = $annotation->getAnnotationForSignature();
    }

    protected function _parseOpt($options, $long = false){
        foreach ($options as $key => $value) {
            $opts .= $value;
            if('valeur' == $this->type[$key]) {
                $opts .= ':';
                if('non' == $this->obligatoire[$key]) {
                    $opts .= ':';
                }
            }
            if(true === $long) {
                $longopts[$key] = $opts;
                $opts = '';
            }
        }
        if(true === $long) 
            return $longopts;
        else
            return $opts;
    }
    
    /**
    * Construire des paramètres de la ligne de commande
    */
    protected function _optionCommandeParser(){
        $shortopts = $this->_parseOpt($this->shortopts);
        $longopts = $this->_parseOpt($this->longopts, true);
        $this->option = \Outils\OptionReader::getopt($shortopts, $longopts);
    }
    
    /**
    * Stub de la méthode traitement qui lance lan ligne de commande
    */
    public function traitement(){
        if(is_array($this->option)){
            foreach ($this->option as $key => $value) {
                switch ($key) {
                    case 'h':
                    case 'help':
                        $this->printHelp();
                        exit(0);
                        break;
                    default:
                        // Lancement du traitement par défault pour chaque commande, ici on ne fait rien car il s'agit d'un stub
                        break;
                }
            }
        }
    }

    public function printHelp(){
        if(null !== $this->signature && null !== $this->descriptionCommande) 
            echo $this->signature.' : '.$this->descriptionCommande."\r\n";
        for($i = 0 ; $i < count($this->shortopts) ; $i++){
            echo '-'.$this->shortopts[$i].', --'.$this->longopts[$i].', '.$this->description[$i].', '.$this->type[$i].', '.$this->obligatoire[$i]."\r\n";
        }
        exit;
    }
}