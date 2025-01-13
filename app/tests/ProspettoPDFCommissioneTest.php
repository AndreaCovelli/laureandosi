<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../app/src/classes/ProspettoPDFCommissione.php');

/**
 * Insieme di test per la classe ProspettoPDFLaureando.
 */
class ProspettoPDFCommissioneTest extends TestCase
{
    private $pdf;
    private $cdl;
    private $dataLaurea;
    private $matricola_list;
    private $output_dir;

    /**
     *  Inizializza i dati di test (viene eseguita prima di ogni test)
     */
    protected function setUp(): void
    {
        $this->pdf = $this->createMock(\FPDF::class);
        $matricola = 456789;
        // create a list of matricola
        $this->matricola_list = array($matricola);
        $this->cdl = "M. Ing. Telecomunicazioni";
        $this->dataLaurea = "2023-12-31";
        $this->output_dir = __DIR__ . '/output_test';
        error_reporting(E_ALL);
    }

    /**
     * Effettua la creazione di un oggetto prospetto ProspettoPDFCommissione
     */
    public function testCreazioneProspettoPDFCommissione()
    {
        $prospetto = new ProspettoPDFCommissione($this->pdf, $this->matricola_list, $this->cdl, $this->dataLaurea);
        $this->assertInstanceOf(ProspettoPDFCommissione::class, $prospetto);
    }

    /**
     * Testa il metodo getOutputDir per verificare che generi il percorso corretto
     */
    public function testGetOutputDir()
    {
        $prospetto = new ProspettoPDFCommissione($this->pdf, $this->matricola_list, $this->cdl, $this->dataLaurea);
        $output_dir = $prospetto->getOutputDir();
        
        // Verifichiamo che il percorso contenga il CdL e la data formattati correttamente
        $safe_cdl = str_replace(' ', '_', $this->cdl);
        $safe_date = str_replace('-', '_', $this->dataLaurea);
        
        $this->assertStringContainsString($safe_cdl, $output_dir);
        $this->assertStringContainsString($safe_date, $output_dir);
    }
}
