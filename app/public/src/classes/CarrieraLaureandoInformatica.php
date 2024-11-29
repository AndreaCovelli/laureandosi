<?php
class CarrieraLaureandoInformatica extends CarrieraLaureando
{
    private float $mediaEsamiInformatica;
    private bool $bonus;

    /**
     * Constructor for CarrieraLaureandoInformatica
     */
    public function __construct(int $matricola, string $CdL, string $dataLaurea) {
        parent::__construct($matricola, $CdL, $dataLaurea);
        $this->mediaEsamiInformatica = $this->RestituisciMediaEsamiInformatici();
        $this->bonus = $this->CalcolaBonus(); // Verifica se lo studente ha diritto al bonus
        if($this->bonus){
            $this->rimuoviEsamePiuBasso(); // Rimuove l'esame con voto più basso
            $this->RestituisciMediaPonderata(); // Aggiorna la media ponderata
        }
    }

    /**
     * Calculates and returns Computer Engineering specific weighted average
     * @return float
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
    
        return $sommaVoti / $sommaCFU; // Arrotonda alla seconda cifra decimale
        //return round($sommaVoti / $sommaCFU, 2); // Arrotonda alla seconda cifra decimale
    }

    /**
     * Calculates if bonus points apply
     * @return bool
     */
    public function CalcolaBonus(): bool {
        // Implementation for calculating bonus eligibility
        $fine_bonus = date('Y-m-d', strtotime("+4 years", strtotime($this->dataImmatricolazione . "-05-31")));
    
        // Confronta la data di laurea con la data di fine bonus
        if (strtotime($this->dataLaurea) <= strtotime($fine_bonus)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Removes the lowest grade exam
     * @return void
     */
    public function rimuoviEsamePiuBasso(): void {
        $esami = $this->esame;
        $votoMinimo = 34; // Inizializzato a 31 poiché i voti sono da 18 a 33 (30 e lode = 33 a ingegneria)
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
     * Getter for bonus
     * @return bool
     */
    public function getBonus(): bool {
        return $this->bonus;
    }

    /**
     * Getter for dataLaurea
     * @return string
     */
    public function getDataLaurea(): string {
        return $this->dataLaurea;
    }

    /**
     * Getter for mediaEsamiInformatica
     * @return float
     */
    public function getMediaEsamiInformatica(): float {
        return $this->mediaEsamiInformatica;
    }
}