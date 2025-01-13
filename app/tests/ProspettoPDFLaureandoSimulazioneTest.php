<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../app/src/classes/ProspettoPDFLaureandoSimulazione.php');

class ProspettoPDFLaureandoSimulazioneTest extends TestCase
{
    private $pdf;
    private $matricola;
    private $cdl;
    private $dataLaurea;

    protected function setUp(): void
    {
        $this->pdf = $this->createMock(\FPDF::class);
        $this->matricola = 123456;
        $this->cdl = "T. Ing. Informatica";
        $this->dataLaurea = "2023-12-31";
        error_reporting(E_ALL);
    }

    public function testCreazioneProspettoPDFLaureandoSimulazione()
    {
        $prospetto = new ProspettoPDFLaureandoSimulazione($this->pdf, $this->matricola, $this->cdl, $this->dataLaurea);
        $this->assertInstanceOf(ProspettoPDFLaureandoSimulazione::class, $prospetto);
    }

    public function testGeneraProspettoSimulazione()
    {
        $this->pdf->expects($this->once())
                  ->method('AddPage');
        
        $this->pdf->expects($this->atLeastOnce())
                  ->method('Cell');
        
        $this->pdf->expects($this->atLeastOnce())
                  ->method('SetFontSize');

        $prospetto = new ProspettoPDFLaureandoSimulazione($this->pdf, $this->matricola, $this->cdl, $this->dataLaurea);
        $prospetto->GeneraProspettoSimulazione();
    }

    public function testGenerazioneEffettivaFileSimulazione()
    {
        $pdf_reale = new FPDF();
        $outputDir = __DIR__ . '/output_test';
        $filename = $outputDir . DIRECTORY_SEPARATOR . 'test_simulazione';
        
        $prospetto = new ProspettoPDFLaureandoSimulazione($pdf_reale, $this->matricola, $this->cdl, $this->dataLaurea);
        $prospetto->GeneraProspettoSimulazione();
        $prospetto->salvaProspetto($filename);
        
        $this->assertFileExists($filename . '.pdf');

        // Pulizia dopo il test
        if (file_exists($filename . '.pdf')){
            unlink($filename . '.pdf');
        }
    }
}
