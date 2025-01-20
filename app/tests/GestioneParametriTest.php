<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../app/src/classes/GestioneParametri.php');

/**
 * Insieme di test per la classe GestioneParametri.
 */
class GestioneParametriTest extends TestCase 
{
    private GestioneParametri $gestioneParametri;
    
    /**
     * Inizializza i dati di test (viene eseguita prima di ogni test).
     */
    protected function setUp(): void
    {
        $this->gestioneParametri = GestioneParametri::getInstance();
    }

    /**
     * Testa il singleton della classe GestioneParametri.
     */
    public function testSingleton()
    {
        $instance1 = GestioneParametri::getInstance();
        $instance2 = GestioneParametri::getInstance();
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Testa il metodo getParametriCdl.
     */
    public function testgetParametriCdl()
    {
        $parametri = $this->gestioneParametri->getParametriCdl();
        
        // Verifica che sia un array
        $this->assertIsArray($parametri);
        
        // Verifica la struttura per un corso specifico
        $this->assertArrayHasKey('degree_programs', $parametri);
        $this->assertArrayHasKey('T. Ing. Informatica', $parametri['degree_programs']);
        
        // Verifica i campi necessari
        $corso = $parametri['degree_programs']['T. Ing. Informatica'];
        $this->assertArrayHasKey('formula', $corso);
        $this->assertArrayHasKey('required_cfu', $corso);
        $this->assertArrayHasKey('parameters', $corso);
    }

    /**
     * Testa il metodo getParametriEsamiInformatici.
     */
    public function testgetParametriEsamiInformatici()
    {
        $esamiInf = $this->gestioneParametri->getParametriEsamiInformatici();
        
        // Verifica che sia un array
        $this->assertIsArray($esamiInf);
        
        // Verifica la presenza di esami informatici
        $this->assertArrayHasKey('T. Ing. Informatica', $esamiInf);
        $this->assertIsArray($esamiInf['T. Ing. Informatica']);
        $this->assertContains('FONDAMENTI DI PROGRAMMAZIONE', $esamiInf['T. Ing. Informatica']);
    }

    /**
     * Testa il metodo getFiltroEsami.
     */
    public function testgetFiltroEsami()
    {
        $filtri = $this->gestioneParametri->getFiltroEsami();
        
        // Verifica che sia un array
        $this->assertIsArray($filtri);
        
        // Verifica la struttura per un corso
        $this->assertArrayHasKey('T. Ing. Informatica', $filtri);
        $this->assertArrayHasKey('*', $filtri['T. Ing. Informatica']);
        
        // Verifica i filtri generali
        $filtriGenerali = $filtri['T. Ing. Informatica']['*'];
        $this->assertArrayHasKey('esami-non-avg', $filtriGenerali);
        $this->assertArrayHasKey('esami-non-cdl', $filtriGenerali);
    }

    /**
     * Testa il metodo isCorsoSupportato.
     */
    public function testCorsoSupportato() {
        $gestore = GestioneParametri::getInstance();
        $this->assertTrue($gestore->isCorsoSupportato("T. Ing. Informatica"));
        $this->assertFalse($gestore->isCorsoSupportato("M. Cybersecurity"));
    }

    /**
     * Testa il metodo getCorsiSupportati.
     */
    public function testGetCorsiSupportati() {
        $gestore = GestioneParametri::getInstance();
        $corsi = $gestore->getCorsiSupportati();
        $this->assertContains("T. Ing. Informatica", $corsi);
        $this->assertNotContains("M. Cybersecurity", $corsi);
    }
}