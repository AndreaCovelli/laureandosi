<?php

use PHPUnit\Framework\TestCase;
require_once(realpath(dirname(__FILE__)) . '\..\Classes\ProspettoPDFCommissione.php');

class ProspettoPDFCommissioneTest extends TestCase
{
    private $pdf;
    private $cdl;
    private $dataLaurea;
    private $matricola_list;

    protected function setUp(): void
    {
        $this->pdf = $this->createMock(\FPDF::class);
        $matricola1 = 456789;
        // create a list of matricola
        $this->matricola_list = array($matricola1);
        $this->cdl = "M. Ing. Telecomunicazioni";
        $this->dataLaurea = "2023-12-31";
        error_reporting(E_ALL);
    }

    public function testCreationProspettoPDFCommissione()
    {
        $prospetto = new ProspettoPDFCommissione($this->pdf, $this->matricola_list, $this->cdl, $this->dataLaurea);
        $this->assertInstanceOf(ProspettoPDFCommissione::class, $prospetto);
    }

    public function testGenerazioneEffettivaFileCommissione()
    {
        $pdf_reale = new FPDF();
        $outputDir = realpath(dirname(__FILE__)) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'output';
        $filename = $outputDir . DIRECTORY_SEPARATOR . 'test_commissione';
        
        $prospetto = new ProspettoPDFCommissione($pdf_reale, $this->matricola_list, $this->cdl, $this->dataLaurea);
        $prospetto->GeneraProspetto();
        $prospetto->salvaProspetto($filename);
        
        $this->assertFileExists($filename . '.pdf');
    }
}
