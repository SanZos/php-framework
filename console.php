<?php
$_SERVER['DOCUMENT_ROOT'] = __DIR__;
require_once 'Autoload.php';

$cc = new \Collection\CommandeCollection();
\Register\CommandeRegister::autoRegister($cc);

try {
    $commande = $cc->getCommande(null, 'Commande\\'.$argv[1]);
    if(class_exists($commande)){
        $run = new $commande(true);
        $run->traitement();
    } else {
       (new \Commande\Commande)->printHelp();
    }
} catch (\Exception $e){
    echo $e->getMessage()."\r\n";
    if(1 === $e->getCode()){
        try{
            $commandes = $cc->getCommande(null, null, true);
            echo "Liste des commande disponible :\r\n";
            foreach($commandes as $commande){
                echo "- ".explode('\\', $commande)[1]." \r\n";
            }
        } catch (\Exception $e){
            echo $e->getMessage()."\r\n";
        }
    }
    /*
        (new \Commande\Commande(true))->printHelp();
    }*/
    exit;
}