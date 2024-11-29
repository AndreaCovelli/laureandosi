<?php

class GestioneCarrieraStudente{

    private static string $data_path;

    private static GestioneCarrieraStudente $instance;

    public static function getInstance(): GestioneCarrieraStudente
    {
        if (!isset(self::$instance)) {
            self::$data_path = join(DIRECTORY_SEPARATOR, array(dirname(__FILE__, 2), 'data'));
            self::$instance = new GestioneCarrieraStudente();
        }
        return self::$instance;
    }
    /**
     * Restituisce l'anagrafica di uno studente
     * @param string $matricola
     * @return array
     */
    public function RestituisciAnagraficaStudente($matricola): array {
        //$string = file_get_contents(realpath(dirname(__FILE__))."/../data/".$matricola."_anagrafica.json");
        $string = file_get_contents(self::$data_path . "/" . $matricola."_anagrafica.json");
        $anagrafica_matricola = json_decode($string, true);
        return $anagrafica_matricola;
    }

    /**
     * Restituisce la carriera di uno studente
     * @param string $matricola
     * @return array
     */
    public function RestituisciEsamiStudente($matricola): array {
        //$string = file_get_contents(realpath(dirname(__FILE__))."/../data/".$matricola."_esami.json");
        $string = file_get_contents(self::$data_path . "/" . $matricola."_esami.json");
        $carriera_matricola = json_decode($string, true);
        return $carriera_matricola;
    }
}
