<?php

require_once(realpath(dirname(__FILE__)) . '/GestioneCarrieraStudente.php');
require_once(realpath(dirname(__FILE__)) . '/EsameLaureando.php');
require_once(realpath(dirname(__FILE__)) . '/GestioneParametri.php');

class CarrieraLaureando
{
    protected int $matricola;
    protected string $nome;
    protected string $cognome;
    protected string $cdL;
    protected string $email;
    protected string $dataImmatricolazione;
    protected string $formulaVotoLaurea;
    protected array $esame;
    protected float $mediaPonderata;
    protected int $cfuTotali;
    protected int $cfuMedia;
    protected string $dataLaurea;

    /**
     * Constructor for CarrieraLaureando
     */
    public function __construct(int $matricola, string $cdL, string $dataLaurea) {
        //$string = file_get_contents("./config_files/degree_formulas.json");
        //$formuleVotoLaurea = json_decode($string, true);

        $gestioreParametri = GestioneParametri::getInstance();

        $formuleVotoLaurea = $gestioreParametri->RestituisciParametriCdl()["degree_programs"][$cdL]["formula"];

        // recupero i dati da GestioneCarrieraStudente
        //$gestioneCarriera = new GestioneCarrieraStudente();
        $gestioneCarriera = GestioneCarrieraStudente::getInstance();
        $anagrafica = $gestioneCarriera->RestituisciAnagraficaStudente($matricola);
        $carriera = $gestioneCarriera->RestituisciEsamiStudente($matricola);
        
        $this->matricola = $matricola;
        $this->dataLaurea = $dataLaurea;
        $this->nome = $anagrafica["Entries"]["Entry"]["nome"];
        $this->cognome = $anagrafica["Entries"]["Entry"]["cognome"];
        $this->cdL = $cdL;
        $this->email = $anagrafica["Entries"]["Entry"]["email_ate"];

        $this->dataImmatricolazione = $carriera["Esami"]["Esame"][0]["ANNO_IMM"];
        //$this->formulaVotoLaurea = $formuleVotoLaurea["degree_programs"][$this->cdL]["formula"];
        //$this->formulaVotoLaurea = $formuleVotoLaurea["degree_programs"][$this->cdL]["formula"];
        $this->formulaVotoLaurea = $formuleVotoLaurea;

        $this->esame = $this->elaboraEsami($carriera);
    
        $this->mediaPonderata = $this->RestituisciMediaPonderata();
        $this->calcolaCFU();
    }

    /**
     * Elaborates exams
     * @return array
     */
    public function elaboraEsami($carriera): array {
        $esami = array();
        $gestioneParametri = GestioneParametri::getInstance();
        $filtroEsami = $gestioneParametri->RestituisciFiltroEsami();
        
        // Get general filters for current degree program
        $filtriCdL = $filtroEsami[$this->cdL]['*'];
        $esamiNonAvg = $filtriCdL['esami-non-avg'];
        $esamiNonCdl = $filtriCdL['esami-non-cdl'];

        // Add specific filters for student if they exist
        if (isset($filtroEsami[$this->cdL][$this->matricola])) {
            $filtriSpecifici = $filtroEsami[$this->cdL][$this->matricola];
            $esamiNonAvg = array_merge($esamiNonAvg, $filtriSpecifici['esami-non-avg']);
            $esamiNonCdl = array_merge($esamiNonCdl, $filtriSpecifici['esami-non-cdl']);
        }

        foreach ($carriera['Esami']['Esame'] as $esame) {
            if ($esame['DES'] === null || is_array($esame['DES'])) {
                continue;
            }

            $voto = $esame['VOTO'] !== null ? (string)$esame['VOTO'] : '0';
            $nome_esame = trim($esame['DES']);
            
            $isInAvg = !in_array($nome_esame, $esamiNonAvg);
            $isCurricolare = !in_array($nome_esame, $esamiNonCdl);

            $esameObj = new EsameLaureando(
                $esame['MATRICOLA'],
                $nome_esame,
                $voto,
                $esame['PESO'],
                $esame['DATA_ESAME'],
                $isCurricolare,
                $isInAvg
            );
            
            array_push($esami, $esameObj);
        }
        
        return $esami;
    }

    /**
     * Calculates and returns weighted average
     * @return float
     */
    public function RestituisciMediaPonderata(): float {
        $esami = $this->esame;
        $sommaVoti = 0;
        $sommaCFU = 0;

        foreach ($esami as $esame) {
            $voto = intval($esame->getVoto()); // Si assume intero dati i controlli effettuati in EsameLaureando
            $cfu = $esame->getCfu();
            
            if ($esame->isInAvg()) {
                $sommaVoti += ($voto * $cfu);
                $sommaCFU += $cfu;
            }
        }

        // Gestione divisione per zero
        if ($sommaCFU === 0) {
            return 0.0;
        }

        return $sommaVoti / $sommaCFU; // Arrotonda alla seconda cifra decimale
        //return round($sommaVoti / $sommaCFU, 2); // Arrotonda alla seconda cifra decimale
    }

    private function calcolaCFU(): void {
        $cfuTotali = 0;
        $cfuMedia = 0;
        
        foreach ($this->esame as $esame) {
            if ($esame->isInAvg()) {
                $cfuMedia += $esame->getCfu();
            }
            if($esame->isCurricolare()) {
                $cfuTotali += $esame->getCfu();
            }
        }
        
        $this->cfuTotali = $cfuTotali;
        $this->cfuMedia = $cfuMedia;
    }

    public function getMatricola(): int {
        return $this->matricola;
    }
    
    public function getNome(): string {
        return $this->nome;
    }
    
    public function getCognome(): string {
        return $this->cognome;
    }
    
    public function getCdL(): string {
        return $this->cdL;
    }
    
    public function getEmail(): string {
        return $this->email;
    }

    public function getDataLaurea(): string {
        return $this->dataLaurea;
    }
    
    /**
     * Ritorna la data di immatricolazione
     * @return string
     */
    public function getFormulaVotoLaurea(): string {
        return $this->formulaVotoLaurea;
    }
    
    /**
     * Ritorna l'array di esami
     * @return array
     */
    public function getEsami(): array {
        return $this->esame;
    }
    
    /**
     * Ritorna la media ponderata
     * @return float
     */
    public function getMediaPonderata(): float {
        return $this->mediaPonderata;
    }

    /**
     * Ritorna il numero di CFU totali considerati per il calcolo della media ponderata
     * @return int
     */
    public function getCfuTotali(): int {
        return $this->cfuTotali;
    }
    
    /**
     * Ritorna il numero di CFU che fanno media considerati per il calcolo della media ponderata
     * @return int
     */
    public function getCfuMedia(): int {
        return $this->cfuMedia;
    }
}