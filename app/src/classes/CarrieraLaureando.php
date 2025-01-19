<?php

require_once(realpath(dirname(__FILE__)) . '/GestioneCarrieraLaureando.php');
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
     * Costruttore per CarrieraLaureando
     */
    public function __construct(int $matricola, string $cdL, string $dataLaurea) {
        // Recupero i dati da GestioneParametri per la formula di calcolo del voto di laurea
        $gestioneParametri = GestioneParametri::getInstance();

        // Verifica se il corso di laurea è supportato dal sistema
        if (!$gestioneParametri->isCorsoSupportato($cdL)) {
            throw new InvalidArgumentException("Corso di laurea non supportato: " . $cdL);
        }

        $this->formulaVotoLaurea = $gestioneParametri->RestituisciParametriCdl()["degree_programs"][$cdL]["formula"];

        // Recupero i dati da GestioneCarrieraLaureando per l'anagrafica e la carriera del laureando
        $gestioneCarriera = GestioneCarrieraLaureando::getInstance();
        $anagrafica = $gestioneCarriera->RestituisciAnagraficaLaureando($matricola);
        $carriera = $gestioneCarriera->RestituisciEsamiLaureando($matricola);
        
        $this->matricola = $matricola;
        $this->dataLaurea = $dataLaurea;
        $this->nome = $anagrafica["Entries"]["Entry"]["nome"];
        $this->cognome = $anagrafica["Entries"]["Entry"]["cognome"];
        $this->cdL = $cdL;
        $this->email = $anagrafica["Entries"]["Entry"]["email_ate"];

        $this->dataImmatricolazione = $carriera["Esami"]["Esame"][0]["ANNO_IMM"];

        $this->esame = $this->elaboraEsami($carriera);
    
        $this->mediaPonderata = $this->RestituisciMediaPonderata();
        $this->calcolaCFU();
    }

    /**
     * Elabora gli esami del laureando
     * @return array
     */
    public function elaboraEsami($carriera): array {
        // Inizializza l'array di esami
        $esami = array();

        // Recupero i filtri degli esami
        $gestioneParametri = GestioneParametri::getInstance();
        $filtroEsami = $gestioneParametri->RestituisciFiltroEsami();
        
        // Ottieni i filtri per il corso di laurea del laureando
        $filtriCdL = $filtroEsami[$this->cdL]['*'];
        $esamiNonAvg = $filtriCdL['esami-non-avg'];
        $esamiNonCdl = $filtriCdL['esami-non-cdl'];

        // Controlla se ci sono filtri specifici per la matricola del laureando
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
            
            // Aggiungi l'oggetto EsameLaureando appena creato all'array
            array_push($esami, $esameObj);
        }
        
        return $esami;
    }

    /**
     * Calcola e restituisce la media ponderata per CFU
     * @return float
     */
    public function RestituisciMediaPonderata(): float {
        $esami = $this->esame;
        $sommaVoti = 0;
        $sommaCFU = 0;

        foreach ($esami as $esame) {
            $voto = intval($esame->getVoto()); // Si assume intero, dati i controlli effettuati in EsameLaureando
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

        return $sommaVoti / $sommaCFU; // Non arrotondare
    }

    /**
     * Calcola i CFU totali (curricolari) e i CFU che fanno media
     * @return void
     */
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

    /**
     * Getter per la matricola del laureando
     * @return int
     */
    public function getMatricola(): int {
        return $this->matricola;
    }
    
    /**
     * Getter per il nome del laureando
     * @return string
     */
    public function getNome(): string {
        return $this->nome;
    }
        
    /**
     * Getter per il cognome del laureando
     * @return string
     */
    public function getCognome(): string {
        return $this->cognome;
    }
    
    /**
     * Getter per il corso di laurea del laureando
     * @return string
     */
    public function getCdL(): string {
        return $this->cdL;
    }
    
    /**
     * Getter per l'email del laureando
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }

    /**
     * Getter per la data di immatricolazione del laureando
     * @return string
     */
    public function getDataLaurea(): string {
        return $this->dataLaurea;
    }
    
    /**
     * Getter per la formula di calcolo del voto di laurea
     * @return string
     */
    public function getFormulaVotoLaurea(): string {
        return $this->formulaVotoLaurea;
    }
    
    /**
     * Getter per l'array di esami del laureando
     * @return array
     */
    public function getEsami(): array {
        return $this->esame;
    }
    
    /**
     * Getter per la media ponderata
     * @return float
     */
    public function getMediaPonderata(): float {
        return $this->mediaPonderata;
    }

    /**
     * Getter per il numero di CFU totali considerati per il calcolo della media ponderata
     * @return int
     */
    public function getCfuTotali(): int {
        return $this->cfuTotali;
    }
    
    /**
     * Getter per il numero di CFU che fanno media e considerati per il calcolo della media ponderata
     * @return int
     */
    public function getCfuMedia(): int {
        return $this->cfuMedia;
    }
}