<?php
/**
 * Gestisce i parametri di configurazione
 * 
 * Questa classe è implementata come Singleton per garantire una gestione 
 * coerente dei parametri in tutta l'applicazione e per evitare che più istanze 
 * carichino ripetutamente gli stessi file di configurazione
 */
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
    public function getParametriCdl(): array {
        // $string = file_get_contents(realpath(dirname(__FILE__))."/../config_files/parametri_voto_laurea.json");
        $string = file_get_contents(self::$path . DIRECTORY_SEPARATOR ."parametri_voto_laurea.json");
        return json_decode($string, true);
    }

    /**
     * Restituisce i parametri per il calcolo della media degli esami informatici
     * @return array
     */
    public function getParametriEsamiInformatici(): array {
        // $string = file_get_contents(realpath(dirname(__FILE__))."esami_informatici.json");
        $string = file_get_contents(self::$path . DIRECTORY_SEPARATOR ."esami_informatici.json");
        return json_decode($string, true);
    }

    /**
     * Restituisce il filtro degli esami
     * @return array
     */
    public function getFiltroEsami(): array {
        // $string = file_get_contents(realpath(dirname(__FILE__))."/../config_files/filtro_esami.json");
        $string = file_get_contents(self::$path . DIRECTORY_SEPARATOR ."filtro_esami.json");
        return json_decode($string, true);
    }

    /**
     * Verifica se un corso di laurea è supportato dal sistema
     * @param string $corso Il codice del corso di laurea
     * @return bool
     */
    public function isCorsoSupportato(string $corso): bool {
        $parametri = $this->getParametriCdl();
        return isset($parametri['degree_programs'][$corso]);
    }

    /**
     * Restituisce l'array dei corsi di laurea supportati dal sistema
     * @return array
     */
    public function getCorsiSupportati(): array {
        $parametri = $this->getParametriCdl();
        return array_keys($parametri['degree_programs']);
    }
}