<?php

require(realpath(dirname(__FILE__)) . "/../../lib/fpdf184/fpdf.php");
require(realpath(dirname(__FILE__)) . "/Prospetto.php");
require(realpath(dirname(__FILE__)) . "/CarrieraLaureando.php");
require(realpath(dirname(__FILE__)) . "/CarrieraLaureandoInformatica.php");

class ProspettoPDFLaureando extends Prospetto{
    protected $matricola;
    protected $carriera_laureando;

    public function __construct(\FPDF $pdf, int $matricola, string $cdl, string $dataLaurea){
        parent::__construct($pdf);
        $this->matricola = $matricola;

        if ($cdl != "T. Ing. Informatica" && $cdl != "INGEGNERIA INFORMATICA (IFO-L)") {
            $this->carriera_laureando = new CarrieraLaureando($matricola, $cdl, $dataLaurea);
        } else {
            $this->carriera_laureando = new CarrieraLaureandoInformatica($matricola, $cdl, $dataLaurea);
        }
    }

    public function generaProspetto(): void{
        $this->pdf->SetFont('Arial', '', 12);
        $this->pdf->AddPage();

        // Intestazione
        $this->pdf->Cell(0, 5, $this->carriera_laureando->getCdL(), 0, 1, 'C');
        $this->pdf->Cell(0, 5, 'CARRIERA E SIMULAZIONE DEL VOTO DI LAUREA', 0, 1, 'C');

        // Aggiungo le varie sezioni
        $this->dati_anagrafici();
        $this->lista_esami();
        $this->statistiche();
    }

    /**
     * Aggiunge i dati anagrafici del laureando
     * @return void
     */
    private function dati_anagrafici(): void{
        $this->pdf->SetFontSize(10);
        //$this->pdf->SetFont('Arial');

        $is_informatico = is_a($this->carriera_laureando, CarrieraLaureandoInformatica::class);

        $this->pdf->Rect($this->pdf->GetX(), $this->pdf->GetY(), $this->pdf->GetPageWidth() - 20, 5 * (5 + $is_informatico));
        $this->pdf->Cell(60, 5, 'Matricola:', 0, 0);
        $this->pdf->Cell(0, 5, $this->carriera_laureando->getMatricola(), 0, 1);
        $this->pdf->Cell(60, 5, 'Nome:', 0, 0);
        $this->pdf->Cell(0, 5, $this->carriera_laureando->getNome(), 0, 1);
        $this->pdf->Cell(60, 5, 'Cognome:', 0, 0);
        $this->pdf->Cell(0, 5, $this->carriera_laureando->getCognome(), 0, 1);
        $this->pdf->Cell(60, 5, 'Email:', 0, 0);
        $this->pdf->Cell(0, 5, $this->carriera_laureando->getEmail(), 0, 1);
        $this->pdf->Cell(60, 5, 'Data:', 0, 0);
        $this->pdf->Cell(0, 5, $this->carriera_laureando->getDataLaurea(), 0, 1);

        if($is_informatico){
            $this->pdf->Cell(60, 5, 'Bonus:', 0, 0);
            $this->pdf->Cell(0, 5, $this->carriera_laureando->getBonus() ? 'SI' : 'No', 0, 1);
        }

        //$this->pdf->Ln(1.5);
    }

    /**
     * Aggiunge la lista di esami della carriera del laureando
     * @return void
     */
    private function lista_esami(): void{
        $is_informatico = is_a($this->carriera_laureando, CarrieraLaureandoInformatica::class);

        $this->pdf->SetFontSize(10);

        $this->pdf->Cell($this->pdf->GetPageWidth() - 10 * (5 + $is_informatico), 5, 'ESAME', 1, 0, 'C');
        $this->pdf->Cell(10, 5, 'CFU', 1, 0, 'C');
        $this->pdf->Cell(10, 5, 'VOT', 1, 0, 'C');
        $this->pdf->Cell(10, 5, 'MED', 1, 0, 'C');
        if ($is_informatico) {
            $this->pdf->Cell(10, 5, 'INF', 1, 0, 'C');
        }
        $this->pdf->Ln();

        $this->pdf->SetFontSize(8);

        foreach($this->carriera_laureando->getEsami() as $esame){
            if(!$esame->isCurricolare())
                continue;

            $this->pdf->Cell($this->pdf->GetPageWidth() - 10 * (5 + $is_informatico), 5, $esame->getNome(), 1, 0, 'L');
            $this->pdf->Cell(10, 5, $esame->getCfu(), 1, 0, 'C');
            $this->pdf->Cell(10, 5, $esame->getVoto(), 1, 0, 'C');
            $this->pdf->Cell(10, 5, $esame->isInAvg() ? 'X' : '', 1, 0, 'C');
            if ($is_informatico) {
                $this->pdf->Cell(10, 5, $esame->isInformatico() ? 'X' : '', 1, 0, 'C');
            }
            $this->pdf->Ln();
        }
        $this->pdf->Ln(3.5);
    }

    /**
     * Aggiunge le statistiche relative alla carriera del laureando
     * @return void
     */
    private function statistiche(): void{
        //$string = file_get_contents(realpath(dirname(__FILE__))."/../config_files/degree_formulas.json");
        //$parametri = json_decode($string, true);

        $gestioneParametri = GestioneParametri::getInstance();
        $parametri = $gestioneParametri->RestituisciParametriCdl();

        $is_informatico = is_a($this->carriera_laureando, CarrieraLaureandoInformatica::class);

        $this->pdf->SetFontSize(10);

        $this->pdf->Rect($this->pdf->GetX(), $this->pdf->GetY(), $this->pdf->GetPageWidth() - 20, 20 + 10 * $is_informatico);

        $this->pdf->Cell(80, 5, 'Media Pesata (M):', 0, 0);
        $this->pdf->Cell(0, 5, round($this->carriera_laureando->getMediaPonderata(), 3), 0, 1);
        $this->pdf->Cell(80, 5, 'Crediti che fanno media (CFU):', 0, 0);
        $this->pdf->Cell(0, 5, $this->carriera_laureando->getCfuMedia(), 0, 1);
        $this->pdf->Cell(80, 5, 'Crediti curriculari conseguiti:', 0, 0);
        $cfu_totali_corso = $parametri["degree_programs"][$this->carriera_laureando->getCdL()]["required_cfu"];        $this->pdf->Cell(0, 5, $this->carriera_laureando->getCfuTotali() . '/' . $cfu_totali_corso, 0, 1);
        if ($is_informatico) {
            $this->pdf->Cell(80, 5, 'Voto di tesi (T):', 0, 0);
            $this->pdf->Cell(0, 5, 0, 0, 1);
        }
        $this->pdf->Cell(80, 5, 'Formula calcolo voto di laurea:', 0, 0);
        $this->pdf->Cell(0, 5, $this->carriera_laureando->getFormulaVotoLaurea(), 0, 1);
        if ($is_informatico) {
            $this->pdf->Cell(80, 5, 'Media pesata esami INF:', 0, 0);
            $this->pdf->Cell(0, 5, round($this->carriera_laureando->getMediaEsamiInformatica(), 3), 0, 1);
        }
    }
}