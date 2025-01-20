<?php

use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../app/src/classes/GestioneCarrieraLaureando.php');

/**
 * Insieme di test per la classe GestioneCarrieraLaureando.
 */
class GestioneCarrieraLaureandoTest extends TestCase 
{
    private $gestioneCarriera;

    /**
     * Inizializza i dati di test (viene eseguita prima di ogni test)
     */
    protected function setUp(): void
    {
        $this->gestioneCarriera = GestioneCarrieraLaureando::getInstance();
    }

    /**
     * Testa il singleton della classe GestioneCarrieraLaureando.
     */
    public function testSingleton()
    {
        $instance1 = GestioneCarrieraLaureando::getInstance();
        $instance2 = GestioneCarrieraLaureando::getInstance();
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Testa il metodo getAnagraficaLaureando.
     */
    public function testgetAnagraficaLaureando()
    {
        // Esegue il metodo
        $carriera = $this->gestioneCarriera->getAnagraficaLaureando(123456);

        // Verifica che il risultato sia un array
        $this->assertIsArray($carriera);

        // Verifica la struttura del JSON
        $this->assertArrayHasKey('Entries', $carriera);
        $this->assertArrayHasKey('Entry', $carriera['Entries']);
        
        // Verifica i campi dell'Entry
        $entry = $carriera['Entries']['Entry'];
        $this->assertArrayHasKey('nome', $entry);
        $this->assertArrayHasKey('cognome', $entry);
        $this->assertArrayHasKey('cod_fis', $entry);
        $this->assertArrayHasKey('data_nascita', $entry);
        $this->assertArrayHasKey('email_ate', $entry);

        // Verifica il tipo dei dati
        $this->assertIsString($entry['nome']);
        $this->assertIsString($entry['cognome']);
        $this->assertIsString($entry['cod_fis']);
        $this->assertIsString($entry['data_nascita']);
        $this->assertIsString($entry['email_ate']);
    }

    /**
     * Testa il metodo getEsamiLaureando.
     */
    public function testgetEsamiLaureando()
    {
        // Esegue il metodo
        $esami = $this->gestioneCarriera->getEsamiLaureando(123456);

        // Verifica che il risultato sia un array
        $this->assertIsArray($esami);
        
        // Verifica la struttura del JSON
        $this->assertArrayHasKey('Esami', $esami);
        $this->assertArrayHasKey('Esame', $esami['Esami']);
        $this->assertIsArray($esami['Esami']['Esame']);
        $this->assertNotEmpty($esami['Esami']['Esame']);
        // print_r($esami['Esami']['Esame']);

        // Verifica la struttura del primo esame
        $primoEsame = $esami['Esami']['Esame'][0];
        
        $campiAttesi = [
            'MATRICOLA', 'DES_CAT', 'NOME', 'COD_FIS', 'CORSO',
            'COD', 'DES', 'DATA_ESAME', 'VOTO', 'PESO', 'GIUDIZIO'
        ];
        
        foreach ($campiAttesi as $campo) {
            $this->assertArrayHasKey($campo, $primoEsame);
        }

        // Verifica alcuni valori specifici
        $this->assertEquals(123456, $primoEsame['MATRICOLA']);
        $this->assertEquals('INGEGNERIA INFORMATICA (IFO-L)', $primoEsame['CORSO']);
        
        // Verifica il formato dei voti (numero seguito da spazio o null)
        if ($primoEsame['VOTO'] !== null) {
            $this->assertMatchesRegularExpression('/^([1-2][0-9]|30)\s$/', $primoEsame['VOTO']);
        }

        // Verifica presenza di esami con giudizio
        $esamiGiudizio = array_filter($esami['Esami']['Esame'], function($esame) {
            return isset($esame['GIUDIZIO']) && $esame['GIUDIZIO'] === 'Idoneo';
        });
        $this->assertNotEmpty($esamiGiudizio);
    }
}