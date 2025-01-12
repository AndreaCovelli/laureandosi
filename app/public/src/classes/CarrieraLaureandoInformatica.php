<?php
class CarrieraLaureandoInformatica extends CarrieraLaureando
{
    private float $mediaEsamiInformatica;
    private bool $bonus;

    /**
     * Costruttore per CarrieraLaureandoInformatica
     * @param int $matricola
     * @param string $CdL
     * @param string $dataLaurea
     * @return void
     */
    public function __construct(int $matricola, string $CdL, string $dataLaurea) {
        parent::__construct($matricola, $CdL, $dataLaurea);
        $this->mediaEsamiInformatica = $this->RestituisciMediaEsamiInformatici();
        $this->bonus = $this->CalcolaBonus(); // Verifica se il laureando ha diritto al bonus
        
        // Se il laureando ha diritto al bonus, rimuove l'esame con voto più basso
        if($this->bonus){
            $this->escludiEsamePiuBassoDallaMedia(); // Rimuove l'esame con voto più basso
            $this->RestituisciMediaPonderata(); // Aggiorna la media ponderata
        }
    }

    /**
     * Calcola la media ponderata degli esami informatici del corso di laurea.
     * 
     * Il calcolo viene effettuato considerando solo gli esami che:
     * Sono presenti nella lista degli esami informatici definita in esami_informatici.json e
     * sono inclusi nel calcolo della media (isInAvg = true)
     * 
     * In caso di "30 e lode", il voto viene convertito in 33 per il calcolo.
     * Se non ci sono esami informatici, viene restituita come media infomatici 0.
     * 
     * @return float La media ponderata degli esami informatici, non arrotondata
     */
    
     public function RestituisciMediaEsamiInformatici(): float {
        $esami = $this->esame;
        $sommaVoti = 0;
        $sommaCFU = 0;
    
        foreach ($esami as $esame) {
            if ($esame->isInformatico() && $esame->isInAvg()) {
                $voto = intval($esame->getVoto());
                $cfu = $esame->getCfu();
                $sommaVoti += ($voto * $cfu);
                $sommaCFU += $cfu;
            }
        }
    
        // Gestione divisione per zero
        if ($sommaCFU === 0) {
            return 0.0;
        }
    
        return $sommaVoti / $sommaCFU; // Restituisco la media ponderata non arrotondata
        // return round($sommaVoti / $sommaCFU, 2); // Arrotonda alla seconda cifra decimale
    }

    /**
     * Calcola se un laureando è idoneo per il bonus in base alla data di laurea.
     * 
     * Un laureando è idoneo per il bonus se si laurea
     * entro il 31 maggio del quarto anno dopo l'immatricolazione.
     * 
     * Il bonus consente di rimuovere il voto più basso dal calcolo della media,
     * migliorando la media e dunque il voto finale.
     * 
     * @return bool true se il laureando è idoneo per il bonus, false altrimenti
     */
    public function CalcolaBonus(): bool {
        $fine_bonus = date('Y-m-d', strtotime("+4 years", strtotime($this->dataImmatricolazione . "-05-31")));
    
        // Confronta la data di laurea con la data di fine bonus
        if (strtotime($this->dataLaurea) <= strtotime($fine_bonus)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Rimuove l'esame con voto più basso.
     * Se ci sono più esami con lo stesso voto, rimuove quello con più CFU.
     * Questa funzione va chiamata solo se il laureando ha diritto al bonus.
     * @return void
     */
    public function escludiEsamePiuBassoDallaMedia(): void {
        $esami = $this->esame;
        $votoMinimo = 34; // Inizializzato a 34 poiché i voti sono da 18 a 33 (30 e lode = 33 a ingegneria)
        $cfuMassimi = 0;
        $indiceDaRimuovere = -1;
    
        // Cerca l'esame con il voto più basso
        for ($i = 0; $i < count($esami); $i++) {
            if ($esami[$i]->isInAvg()) {
                $votoCorrente = intval($esami[$i]->getVoto());
                $cfuCorrente = $esami[$i]->getCfu();
                
                if ($votoCorrente < $votoMinimo) {
                    $votoMinimo = $votoCorrente;
                    $cfuMassimi = $cfuCorrente;
                    $indiceDaRimuovere = $i;
                } else if ($votoCorrente == $votoMinimo && $cfuCorrente > $cfuMassimi) {
                    // Se il voto è uguale, prende quello con più CFU
                    $cfuMassimi = $cfuCorrente;
                    $indiceDaRimuovere = $i;
                }
            }
        }
    
        // Se è stato trovato un esame da rimuovere
        if ($indiceDaRimuovere >= 0) {
            // Imposta l'esame come non conteggiabile nella media
            $this->esame[$indiceDaRimuovere]->setInAvg(false);
        }
    }
    
    /**
     * Getter per bonus
     * @return bool
     */
    public function getBonus(): bool {
        return $this->bonus;
    }

    /**
     * Getter per dataLaurea
     * @return string
     */
    public function getDataLaurea(): string {
        return $this->dataLaurea;
    }

    /**
     * Getter per mediaEsamiInformatica
     * @return float
     */
    public function getMediaEsamiInformatica(): float {
        return $this->mediaEsamiInformatica;
    }
}