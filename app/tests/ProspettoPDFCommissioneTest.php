<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../app/src/classes/ProspettoPDFCommissione.php');

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

    public function testCreazioneProspettoPDFCommissione()
    {
        $prospetto = new ProspettoPDFCommissione($this->pdf, $this->matricola_list, $this->cdl, $this->dataLaurea);
        $this->assertInstanceOf(ProspettoPDFCommissione::class, $prospetto);
    }
}
