<?php
require_once(realpath(dirname(__FILE__)) . '/ProspettoPDFLaureandoSimulazione.php');
require_once(realpath(dirname(__FILE__)) . '/ProspettoPDFLaureando.php');

/**
 * Classe ProspettoPDFCommissione
 * 
 * Questa classe gestisce la generazione del prospetto PDF per la commissione di laurea
 * 
 * Il prospetto generato include nella prima pagina una lista riepilogativa di tutti i laureandi
 * e nelle pagine successive sono presenti i prospetti individuali con simulazioni del voto di laurea
 * 
 * La classe usa ProspettoPDFLaureandoSimulazione per generare i prospetti individuali con simulazione
 * da aggiungere al prospetto della commissione
 * 
 * La classe inoltre usa ProspettoPDFLaureando per generare i prospetti individuali senza simulazione
 * da inviare ai singoli laureandi (a ciascun laureando il proprio prospetto)
 */
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

    /**
     * Calcola l'anno accademico a partire dalla data di laurea
     * L'anno accademico inizia a settembre e finisce ad agosto dell'anno successivo
     * Per esempio: settembre 2024 - agosto 2025 è l'anno accademico 2024/2025
     * 
     * @return string Anno accademico
     */
    public function calcolaAnnoAccademico(): string {
        $data = new DateTime($this->data_laurea);
        $anno = (int)$data->format('Y');
        $mese = (int)$data->format('n');
        
        // Se il mese è da settembre a dicembre, l'anno accademico inizia nell'anno corrente
        // Se il mese è da gennaio ad agosto, l'anno accademico è iniziato l'anno precedente
        $annoInizio = ($mese >= 9) ? $anno : $anno - 1;
        $annoFine = $annoInizio + 1;
        
        return $annoInizio . '-' . $annoFine;
    }

    /**
     * Restituisce la directory di output per i prospetti
     * @return string Percorso della directory di output
     */
    public function getOutputDir(): string {
        $base_dir = dirname(dirname(__DIR__)) . '/public/output';
        $safe_cdl = str_replace('. ', '_', $this->cdl);
        $safe_date = str_replace('-', '_', $this->data_laurea);
        $anno_accademico = $this->calcolaAnnoAccademico();

        return $base_dir . '/' . $safe_cdl . '/' . $anno_accademico . '/' . $safe_date;
    }

    /**
     * Genera prospetto per la commissione e i prospetti per i singoli laureandi
     * @return void
     */
    public function generaProspetto(): void {
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
            $prospetto->generaProspettoSimulazione();
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

    /**
     * Invia via email ai laureandi i prospetti generati
     * 
     * Utilizza la classe GestioneInvioEmail per inviare a ogni laureando
     * il proprio prospetto individuale in formato PDF.
     *
     * @return bool True se tutti gli invii hanno successo, False altrimenti
     */
    public function inviaProspettiLaureandi(): bool {
        $email_manager = GestioneInvioEmail::getInstance();
        return $email_manager->inviaEmailConProspetti($this->lista_laureandi, $this->getOutputDir());
    }

    /**
     * Aggiunge la lista dei laureandi al prospetto
     * Genera una tabella contenente in ogni riga:
     * - Cognome e nome di ogni laureando
     * - Corso di laurea
     * - Spazio per il voto di laurea
     *
     * @return void
     */
    private function add_lista_laureandi() : void {
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