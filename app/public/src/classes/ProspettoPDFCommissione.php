<?php
require_once(realpath(dirname(__FILE__)) . '/ProspettoPDFLaureandoSimulazione.php');
require_once(realpath(dirname(__FILE__)) . '/ProspettoPDFLaureando.php');

class ProspettoPDFCommissione extends Prospetto{
    private array $lista_matricole;
    private array $lista_laureandi;
    private string $cdl;
    private string $data_laurea;

    public function __construct(\FPDF $pdf, array $lista_matricole, string $cdl, string $data_laurea){
        parent::__construct($pdf);
        $this->lista_matricole = $lista_matricole;
        $this->cdl = $cdl;
        $this->data_laurea = $data_laurea;
        $this->lista_laureandi = [];
        
        // Converti le matricole in oggetti CarrieraLaureando
        foreach($this->lista_matricole as $matricola) {
            $this->lista_laureandi[] = new CarrieraLaureando($matricola, $this->cdl, $this->data_laurea);
        }
    }

    public function getOutputDir(): string {
        $base_dir = dirname(dirname(__DIR__)) . '/output';
        $safe_cdl = str_replace(' ', '_', $this->cdl);
        $safe_date = str_replace('-', '_', $this->data_laurea);
        return $base_dir . '/' . $safe_cdl . '/' . $safe_date;
    }

    public function generaProspetto(): void{
        // Crea directory se non esiste
        $output_dir = $this->getOutputDir();
        if (!file_exists($output_dir)) {
            mkdir($output_dir, 0777, true);
        }

        // Aggiungi la lista dei laureandi
        $this->add_lista_laureandi();

        // Genera prospetto commissione
        foreach($this->lista_matricole as $laureando){
            $prospetto = new ProspettoPDFLaureandoSimulazione($this->pdf, $laureando, $this->cdl, $this->data_laurea);
            $prospetto->GeneraProspettoSimulazione();
        }
        $this->salvaProspetto($output_dir . '/prospetto_commissione');

        // Genera prospetti individuali
        foreach($this->lista_matricole as $laureando) {
            $pdf_laureando = new FPDF();
            $prospetto_laureando = new ProspettoPDFLaureando($pdf_laureando, $laureando, $this->cdl, $this->data_laurea);
            $prospetto_laureando->generaProspetto();
            $prospetto_laureando->salvaProspetto($output_dir . '/prospetto_laureando_' . $laureando);
        }
    }
    
    public function inviaProspettiLaureandi(): bool {
        $email_manager = GestioneInvioEmail::getInstance();
        return $email_manager->inviaEmailConProspetti($this->lista_laureandi, $this->getOutputDir());
    }

    private function add_lista_laureandi() : void{
        $this->pdf->SetFont('Arial', '', 10);
        $this->pdf->AddPage();
        $this->pdf->Cell(0, 5, $this->lista_laureandi[0]->getCdl(), 0, 1, 'C');
        $this->pdf->Ln(3);
        $this->pdf->Cell(0, 5, 'LAUREANDOSI 2 - Progettazione: mario.cimino@unipi.it, Amministrazione: rose.rossiello@unipi.it', 0, 1, 'C');
        $this->pdf->Ln(3);
        $this->pdf->Cell(0, 5, 'LISTA LAUREANDI', 0, 1, 'C');
        $this->pdf->Cell(47, 6, 'COGNOME', 1, 0, 'C');
        $this->pdf->Cell(47, 6, 'NOME', 1, 0, 'C');
        $this->pdf->Cell(47, 6, 'CDL', 1, 0, 'C');
        $this->pdf->Cell(47, 6, 'VOTO LAUREA', 1, 0, 'C');

        foreach($this->lista_laureandi as $laureando){
            $this->pdf->Ln();
            $this->pdf->Cell(47, 6, $laureando->getCognome(), 1, 0, 'C');
            $this->pdf->Cell(47, 6, $laureando->getNome(), 1, 0, 'C');
            $this->pdf->Cell(47, 6, $laureando->getCdl(), 1, 0, 'C');
            $this->pdf->Cell(47, 6, '   /110', 1, 0, 'C');
        }

        $this->pdf->Ln();
    }
}