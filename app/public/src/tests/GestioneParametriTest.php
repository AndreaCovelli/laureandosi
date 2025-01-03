<?php
use PHPUnit\Framework\TestCase;
require_once(realpath(dirname(__FILE__)) . '\..\Classes\GestioneParametri.php');

class GestioneParametriTest extends TestCase 
{
    private GestioneParametri $gestioneParametri;
    
    protected function setUp(): void
    {
        $this->gestioneParametri = GestioneParametri::getInstance();
    }

    public function testSingleton()
    {
        $instance1 = GestioneParametri::getInstance();
        $instance2 = GestioneParametri::getInstance();
        $this->assertSame($instance1, $instance2);
    }

    public function testRestituisciParametriCdl()
    {
        $parametri = $this->gestioneParametri->RestituisciParametriCdl();
        
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

    public function testRestituisciParametriEsamiInformatici()
    {
        $esamiInf = $this->gestioneParametri->RestituisciParametriEsamiInformatici();
        
        // Verifica che sia un array
        $this->assertIsArray($esamiInf);
        
        // Verifica la presenza di esami informatici
        $this->assertArrayHasKey('T. Ing. Informatica', $esamiInf);
        $this->assertIsArray($esamiInf['T. Ing. Informatica']);
        $this->assertContains('FONDAMENTI DI PROGRAMMAZIONE', $esamiInf['T. Ing. Informatica']);
    }

    public function testRestituisciFiltroEsami()
    {
        $filtri = $this->gestioneParametri->RestituisciFiltroEsami();
        
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
}