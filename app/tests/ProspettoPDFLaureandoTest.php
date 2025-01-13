<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../app/src/classes/ProspettoPDFLaureando.php');

/**
 * Insieme di test per la classe ProspettoPDFLaureando.
 */
class ProspettoPDFLaureandoTest extends TestCase
{
    private $pdf;
    private $matricola;
    private $cdl;
    private $dataLaurea;

    /**
     *  Inizializza i dati di test (viene eseguita prima di ogni test)
     */
    protected function setUp(): void
    {
        $this->pdf = $this->createMock(\FPDF::class);
        $this->matricola = 345678;
        $this->cdl = "T. Ing. Informatica";
        $this->dataLaurea = "2023-12-31";
    }

    /**
     * Effettua la creazione di un oggetto prospetto ProspettoPDFLaureando
     */
    public function testCreazioneProspettoPDFLaureando()
    {
        $prospetto = new ProspettoPDFLaureando($this->pdf, $this->matricola, $this->cdl, $this->dataLaurea);
        $this->assertInstanceOf(ProspettoPDFLaureando::class, $prospetto);
    }

    /**
     * Effettua la generazione di un ProspettoPDFLaureando
     */
    public function testGeneraProspetto()
    {
        $this->pdf->expects($this->once())
                  ->method('AddPage');
        
        $this->pdf->expects($this->atLeastOnce())
                  ->method('Cell');

        $prospetto = new ProspettoPDFLaureando($this->pdf, $this->matricola, $this->cdl, $this->dataLaurea);
        $prospetto->generaProspetto();
    }

    /**
     * Effettua il salvataggio di un ProspettoPDFLaureando
     */
    public function testSalvaProspetto()
    {
        $outputDir = __DIR__ . '/output_test';
        $filename = $outputDir . DIRECTORY_SEPARATOR . 'test';
        
        $this->pdf->expects($this->once())
                  ->method('Output')
                  ->with('F', $filename . '.pdf');

        $prospetto = new ProspettoPDFLaureando($this->pdf, $this->matricola, $this->cdl, $this->dataLaurea);
        $prospetto->salvaProspetto($filename);
    }

    /**
     * Effettua la generazione e il salvataggio di un ProspettoPDFLaureando
     * 
     * Nota: il test non usa un mock, verifica la presenza del file generato e lo elimina subito dopo
     */
    public function testGenerazioneEffettivaFile()
    {
        // Usa una vera istanza di FPDF invece del mock
        $pdf_reale = new FPDF();
        $outputDir = __DIR__ . '/output_test';
        $filename = $outputDir . DIRECTORY_SEPARATOR . 'test';
        
        $prospetto = new ProspettoPDFLaureando($pdf_reale, $this->matricola, $this->cdl, $this->dataLaurea);
        $prospetto->generaProspetto(); // Prima generiamo il contenuto
        $prospetto->salvaProspetto($filename); // Poi salviamo il file
        
        $this->assertFileExists($filename . '.pdf');
        
        // Pulizia dopo il test
        if (file_exists($filename . '.pdf')){
            unlink($filename . '.pdf');
        }
    }
}
