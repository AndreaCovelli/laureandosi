<?php

class GestioneParametri {
    private static ?GestioneParametri $instance = null;

    private static string $path;
    
    private function __construct() {}
    
    /**
     * Restituisce l'istanza della classe
     * @return GestioneParametri
     */
    public static function getInstance(): GestioneParametri {
        if (self::$instance === null) {
            self::$path = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__, 2), 'config_files'));
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Restituisce i parametri per il calcolo della media degli esami informatici
     * @return array
     */
    public function RestituisciParametriCdl(): array {
        //$string = file_get_contents(realpath(dirname(__FILE__))."/../config_files/degree_formulas.json");
        $string = file_get_contents(self::$path . DIRECTORY_SEPARATOR ."degree_formulas.json");
        return json_decode($string, true);
    }

    /**
     * Restituisce i parametri per il calcolo della media degli esami informatici
     * @return array
     */
    public function RestituisciParametriEsamiInformatici(): array {
        //$string = file_get_contents(realpath(dirname(__FILE__))."esami_informatici.json");
        $string = file_get_contents(self::$path . DIRECTORY_SEPARATOR ."esami_informatici.json");
        return json_decode($string, true);
    }

    /**
     * Restituisce il filtro degli esami
     * @return array
     */
    public function RestituisciFiltroEsami(): array {
        //$string = file_get_contents(realpath(dirname(__FILE__))."/../config_files/filtro_esami.json");
        $string = file_get_contents(self::$path . DIRECTORY_SEPARATOR ."filtro_esami.json");
        return json_decode($string, true);
    }
}