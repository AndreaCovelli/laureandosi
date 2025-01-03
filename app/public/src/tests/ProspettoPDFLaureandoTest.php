<?php

use PHPUnit\Framework\TestCase;
require_once(realpath(dirname(__FILE__)) . '\..\Classes\ProspettoPDFLaureando.php');

class ProspettoPDFLaureandoTest extends TestCase
{
    private $pdf;
    private $matricola;
    private $cdl;
    private $dataLaurea;

    protected function setUp(): void
    {
        $this->pdf = $this->createMock(\FPDF::class);
        $this->matricola = 345678;
        $this->cdl = "T. Ing. Informatica";
        $this->dataLaurea = "2023-12-31";
    }

    public function testCreazioneProspettoPDFLaureando()
    {
        $prospetto = new ProspettoPDFLaureando($this->pdf, $this->matricola, $this->cdl, $this->dataLaurea);
        $this->assertInstanceOf(ProspettoPDFLaureando::class, $prospetto);
    }

    public function testGeneraProspetto()
    {
        $this->pdf->expects($this->once())
                  ->method('AddPage');
        
        $this->pdf->expects($this->atLeastOnce())
                  ->method('Cell');

        $prospetto = new ProspettoPDFLaureando($this->pdf, $this->matricola, $this->cdl, $this->dataLaurea);
        $prospetto->generaProspetto();
    }

    public function testSalvaProspetto()
    {
        $outputDir = realpath(dirname(__FILE__)) . '\..\output';
        // print_r($outputDir);
        $filename = $outputDir . DIRECTORY_SEPARATOR . 'test';
        
        $this->pdf->expects($this->once())
                  ->method('Output')
                  ->with('F', $filename . '.pdf');

        $prospetto = new ProspettoPDFLaureando($this->pdf, $this->matricola, $this->cdl, $this->dataLaurea);
        $prospetto->salvaProspetto($filename);
    }

    public function testGenerazioneEffettivaFile()
    {
        // Usa una vera istanza di FPDF invece del mock
        $pdf_reale = new FPDF();
        $outputDir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'output';
        $filename = $outputDir . DIRECTORY_SEPARATOR . 'test';
        
        $prospetto = new ProspettoPDFLaureando($pdf_reale, $this->matricola, $this->cdl, $this->dataLaurea);
        $prospetto->generaProspetto(); // Prima generiamo il contenuto
        $prospetto->salvaProspetto($filename); // Poi salviamo il file
        
        $this->assertFileExists($filename . '.pdf');
        
        // Pulizia dopo il test
        //if (file_exists($filename . '.pdf')) {
        //    unlink($filename . '.pdf');
        //}
    }
}
