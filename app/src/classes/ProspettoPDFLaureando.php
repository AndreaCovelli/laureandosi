<?php

require_once(dirname(dirname(__DIR__)) . '/lib/fpdf184/fpdf.php');
require_once(realpath(dirname(__FILE__)) . "/Prospetto.php");
require_once(realpath(dirname(__FILE__)) . "/CarrieraLaureando.php");
require_once(realpath(dirname(__FILE__)) . "/CarrieraLaureandoInformatica.php");

/**
 * Classe per la generazione del prospetto di un laureando in formato PDF
 */
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

    /**
     * Genera il prospetto
     * Il prospetto per il laureando è composto da tre sezioni:
     * - Dati anagrafici del laureando
     * - Lista degli esami sostenuti
     * - Statistiche sulla carriera
     * 
     * Le tre sezioni elencate sopra sono nello stesso ordine
     * in cui compaiono all'interno del prospetto in pdf
     * 
     * @return void
     */
    public function generaProspetto() {
        $this->pdf->SetFont('Arial', '', 12);
        $this->pdf->AddPage();

        // Intestazione
        $this->pdf->Cell(0, 5, $this->carriera_laureando->getCdL(), 0, 1, 'C');
        $this->pdf->Cell(0, 5, 'CARRIERA E SIMULAZIONE DEL VOTO DI LAUREA', 0, 1, 'C');

        // Aggiungo le tre sezioni del prospetto
        $this->dati_anagrafici();
        $this->lista_esami();
        $this->statistiche();
    }

    /**
     * Aggiunge al prospetto i dati anagrafici del laureando
     * @return void
     */
    private function dati_anagrafici(): void{
        $this->pdf->SetFontSize(10);
        // $this->pdf->SetFont('Arial');

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

        // $this->pdf->Ln(1.5);
    }

    /**
     * Aggiunge al prospetto la lista di esami della carriera del laureando
     * @return void
     */
    private function lista_esami(): void {
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
     * Aggiunge al prospetto le statistiche relative alla carriera del laureando
     * @return void
     */
    private function statistiche(): void {

        $gestioneParametri = GestioneParametri::getInstance();
        $parametri = $gestioneParametri->getParametriCdl();

        // Verifica se lo studente è di Ingegneria Informatica
        $is_informatico = is_a($this->carriera_laureando, CarrieraLaureandoInformatica::class);

        $this->pdf->SetFontSize(10);

        // Crea il riquadro contenente le statistiche
        $this->pdf->Rect($this->pdf->GetX(), $this->pdf->GetY(), $this->pdf->GetPageWidth() - 20, 20 + 10 * $is_informatico);

        $this->pdf->Cell(80, 5, 'Media Pesata (M):', 0, 0);
        $this->pdf->Cell(0, 5, round($this->carriera_laureando->getMediaPonderata(), 3), 0, 1);
        $this->pdf->Cell(80, 5, 'Crediti che fanno media (CFU):', 0, 0);
        $this->pdf->Cell(0, 5, $this->carriera_laureando->getCfuMedia(), 0, 1);
        $this->pdf->Cell(80, 5, 'Crediti curriculari conseguiti:', 0, 0);

        $cfu_totali_corso = $parametri["degree_programs"][$this->carriera_laureando->getCdL()]["required_cfu"];
        
        $this->pdf->Cell(0, 5, $this->carriera_laureando->getCfuTotali() . '/' . $cfu_totali_corso, 0, 1);

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