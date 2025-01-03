<?php

use PHPUnit\Framework\TestCase;
require_once(realpath(dirname(__FILE__)) . '\..\Classes\GestioneCarrieraLaureando.php');

class GestioneCarrieraLaureandoTest extends TestCase 
{
    private $gestioneCarriera;

    protected function setUp(): void
    {
        $this->gestioneCarriera = GestioneCarrieraLaureando::getInstance();
    }

    public function testRestituisciAnagraficaLaureando()
    {
        // Esegue il metodo
        $carriera = $this->gestioneCarriera->RestituisciAnagraficaLaureando(123456);

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
        // display the entry
        // print_r($entry);

        // Verifica il tipo dei dati
        $this->assertIsString($entry['nome']);
        $this->assertIsString($entry['cognome']);
        $this->assertIsString($entry['cod_fis']);
        $this->assertIsString($entry['data_nascita']);
        $this->assertIsString($entry['email_ate']);
    }

    public function testRestituisciEsamiLaureando()
    {
        // Esegue il metodo
        $esami = $this->gestioneCarriera->RestituisciEsamiLaureando(123456);

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