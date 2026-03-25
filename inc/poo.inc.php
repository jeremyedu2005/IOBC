<?php

if(defined('CHARGE_AUTOLOAD'))
{
    set_autoload();
}
else
{
    die("EUH"); // Arrêt en erreur, en mode silence("euh")
}

function set_autoload()
{
    function my_autoloader ($classname)
    {
        $filename = './class/' . $classname . '.class.php'; // ← ajouter class/
        if (file_exists($filename))
        {
            include_once($filename);
        }
        else
        {
            die(" OUPS:Erreur fichier inconnu : $filename");
        }
    }
    spl_autoload_register('my_autoloader');
}
?> 



