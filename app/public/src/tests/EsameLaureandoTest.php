<?php
use PHPUnit\Framework\TestCase;
require_once(realpath(dirname(__FILE__)) . '\..\Classes\EsameLaureando.php');
require_once(realpath(dirname(__FILE__)) . '\..\Classes\GestioneParametri.php');
require_once(realpath(dirname(__FILE__)) . '\..\Classes\EsameLaureando.php');

class EsameLaureandoTest extends TestCase 
{
    private EsameLaureando $esame;
    
    protected function setUp(): void
    {
        $this->esame = new EsameLaureando(
            123456,                    // matricola
            "ANALISI MATEMATICA I",    // nome
            "30 e lode",              // voto
            12,                       // cfu
            "2020-01-15",            // data
            true,                     // curricolare
            true                      // isInAvg
        );
    }

    public function testCostruttoreEConversione30eLode()
    {
        $this->assertEquals(123456, $this->esame->getMatricola());
        $this->assertEquals("ANALISI MATEMATICA I", $this->esame->getNome());
        $this->assertEquals("33", $this->esame->getVoto()); // 30 e lode viene convertito in 33
        $this->assertEquals(12, $this->esame->getCfu());
        $this->assertEquals("2020-01-15", $this->esame->getData());
        $this->assertTrue($this->esame->isCurricolare());
        $this->assertTrue($this->esame->isInAvg());
    }

    public function testSetInAvg()
    {
        $this->esame->setInAvg(false);
        $this->assertFalse($this->esame->isInAvg());
    }

    public function testIsInformatico()
    {
        // Per default un esame non è informatico
        $this->assertFalse($this->esame->isInformatico());
        
        // Creiamo un esame informatico
        $esameinf = new EsameLaureando(
            123456,
            "FONDAMENTI DI PROGRAMMAZIONE",
            "28",
            9,
            "2020-01-15",
            true,
            true
        );
        
        // Verifichiamo che sia riconosciuto come informatico
        $this->assertTrue($esameinf->isInformatico());
    }
}