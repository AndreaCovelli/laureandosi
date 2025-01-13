<?php
use PHPUnit\Framework\TestCase;
require_once(__DIR__ . '/../../app/src/classes/EsameLaureando.php');
require_once(__DIR__ . '/../../app/src/classes/GestioneParametri.php');

/**
 * Insieme di test per la classe EsameLaureando.
 */
class EsameLaureandoTest extends TestCase 
{
    private EsameLaureando $esame;
    
    /**
     * Inizializza i dati di test (viene eseguita prima di ogni test)
     */
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

    /**
     * Testa il costruttore e la conversione del voto da '30 e lode' a 33.
     */
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

    /**
     * Testa il metodo setInAvg.
     */
    public function testSetInAvg()
    {
        $this->esame->setInAvg(false);
        $this->assertFalse($this->esame->isInAvg());
    }

    /**
     * Testa il metodo isInformatico.
     */
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