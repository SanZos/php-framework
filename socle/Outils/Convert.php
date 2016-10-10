<?php
namespace Outils;

class Convert {

    public static function Octal($valeur)
    {
        $unit = ["o","Ko","Mo","Go","To"];
        $i = 0;
        do
        {
            $detail[$i]= $valeur%1024;
            $valeur/=1024;
            $i++;
        } while ($valeur>=1024&& $i<5);
        $detail[$i]=$valeur;
        if($detail[$i]!=0)
        {
            $str = round($detail[$i])." ".$unit[$i]." ";
        } else {
            $str = round($detail[$i-1])." ".$unit[$i-1]." ";
        }
        return $str;
    }
} 