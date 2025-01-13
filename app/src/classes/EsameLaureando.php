<?php
class EsameLaureando{
    private int $matricola;
    private string $nome;
    private string $voto;
    private int $cfu;
    private string $data;
    /** @var bool Esame nel curriculum dello studente */
    private bool $curricolare;
    /** @var bool Esame che fa media per la laurea attuale*/
    private bool $isInAvg;
    /** @var bool Esame nella lista degli esami informatici */
    private bool $informatico;

    public function __construct(int $matricola, string $nome, string $voto, int $cfu, string $data, bool $curricolare=false, bool $isInAvg=true){
        $this->matricola = $matricola;
        $this->nome = $nome;
        $this->voto = $voto;
        $this->cfu = $cfu;
        $this->data = $data;
        $this->curricolare = $curricolare;
        $this->isInAvg = $isInAvg;
        $this->informatico = false; // Viene nel caso settato a true dopo, nella funzione elabora_dati
        $this->elabora_dati();
    }

    /**
     * Elabora i dati del singolo esame
     * @return void
     */
    private function elabora_dati(): void {
        $gestioneParametri = GestioneParametri::getInstance();

        // Recupero la lista degli esami informatici (per la T. Ing. Informatica)
        $esamiInformatici = $gestioneParametri->RestituisciParametriEsamiInformatici()['T. Ing. Informatica'];
        
        if ($this->nome !== "TEST DI VALUTAZIONE DI INGEGNERIA" && $this->nome !== 'null') {
            if ($this->voto === "30 e lode" || $this->voto === "30 e lode " || $this->voto === "30  e lode") {
                $this->voto = "33"; // La lode a ingegneria vale 33
            }
            $this->voto = trim($this->voto);
        }
        
        // Controlla che l'esame sia nella lista degli esami informatici
        if (in_array($this->nome, $esamiInformatici)) {
            $this->informatico = true;
        }
    }

    /**
     * Getter per matricola
     * @return int
     */
    public function getMatricola(): int {
        return $this->matricola;
    }

    /**
     * Getter per nome
     * @return string
     */
    public function getNome(): string {
        return $this->nome;
    }

    /**
     * Getter per voto
     * @return string
     */
    public function getVoto(): string {
        return $this->voto;
    }

    /**
     * Getter per cfu
     * @return int
     */
    public function getCfu(): int {
        return $this->cfu;
    }

    /**
     * Getter per data
     * @return string
     */
    public function getData(): string {
        return $this->data;
    }

    /**
     * Getter per curricolare
     * @return bool
     */
    public function isCurricolare(): bool {
        return $this->curricolare;
    }

    /**
     * Getter per isInAvg
     * @return bool
     */
    public function isInAvg(): bool {
        return $this->isInAvg;
    }

    /**
     * Setter per isInAvg
     * @param bool $isInAvg
     * @return void
     */
    public function setInAvg(bool $isInAvg): void {
        $this->isInAvg = $isInAvg;
    }

    /**
     * Getter per informatico
     * @return bool
     */
    public function isInformatico(): bool {
        return $this->informatico;
    }
}
