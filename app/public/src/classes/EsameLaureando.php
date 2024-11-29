<?php
class EsameLaureando{
    private int $matricola;
    private string $nome;
    private string $voto;
    private int $cfu;
    private string $data;
    private bool $curricolare;
    private bool $isInAvg;
    private bool $informatico;

    public function __construct(int $matricola, string $nome, string $voto, int $cfu, string $data, bool $curricolare=false, bool $isInAvg=true){
        $this->matricola = $matricola;
        $this->nome = $nome;
        $this->voto = $voto;
        $this->cfu = $cfu;
        $this->data = $data;
        $this->curricolare = $curricolare;
        $this->isInAvg = $isInAvg;
        $this->informatico = false;
        $this->elabora_dati();
    }

    private function elabora_dati(){
        $gestioneParametri = GestioneParametri::getInstance();
        $esamiInformatici = $gestioneParametri->RestituisciParametriEsamiInformatici()['T. Ing. Informatica'];
        
        if ($this->nome !== "TEST DI VALUTAZIONE DI INGEGNERIA" && $this->nome !== 'null') {
            if ($this->voto === "30 e lode" || $this->voto === "30 e lode " || $this->voto === "30  e lode") {
                $this->voto = "33";
            }
            $this->voto = trim($this->voto);
        }
        
        // Check if exam is informatico
        if (in_array($this->nome, $esamiInformatici)) {
            $this->informatico = true;
        }
    }

    public function getMatricola(): int {
        return $this->matricola;
    }

    public function getNome(): string {
        return $this->nome;
    }

    public function getVoto(): string {
        return $this->voto;
    }

    public function getCfu(): int {
        return $this->cfu;
    }

    public function getData(): string {
        return $this->data;
    }

    public function isCurricolare(): bool {
        return $this->curricolare;
    }

    public function isInAvg(): bool {
        return $this->isInAvg;
    }

    public function setInAvg(bool $isInAvg): void {
        $this->isInAvg = $isInAvg;
    }

    public function isInformatico(): bool {
        return $this->informatico;
    }
}
