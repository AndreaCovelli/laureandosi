<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../app/src/classes/CarrieraLaureando.php');
require_once(__DIR__ . '/../../app/src/classes/GestioneCarrieraLaureando.php');

/**
 * Insieme di test per la classe CarrieraLaureando.
 */
class CarrieraLaureandoTest extends TestCase 
{
    private CarrieraLaureando $carriera;
    
    /**
     * Inizializza i dati di test (viene eseguita prima di ogni test)
     */
    protected function setUp(): void
    {
        $matricola = 123456;
        $CdL = "T. Ing. Informatica";
        $this->carriera = new CarrieraLaureando($matricola, $CdL, "2023-10-01");
    }

    /**
     * Testa la creazione di un oggetto CarrieraLaureando.
     */
    public function testCreazioneCarrieraLaureando()
    {
        $this->assertInstanceOf(CarrieraLaureando::class, $this->carriera);
    }

    /**
     * Testa l'anagrafica del laureando.
     */
    public function testAnagraficaLaurendo()
    {
        $this->assertEquals(123456, $this->carriera->getMatricola());
        $this->assertEquals("GIANLUIGI", $this->carriera->getNome());
        $this->assertEquals("DONNARUMMA", $this->carriera->getCognome());
        $this->assertEquals("nome.cognome@studenti.unipi.it", $this->carriera->getEmail());
        $this->assertEquals("T. Ing. Informatica", $this->carriera->getCdL());
    }

    /**
     * Testa il calcolo della media ponderata.
     */
    public function testMediaPonderata()
    {
        $media = $this->carriera->getMediaPonderata();
        $expectedMedia = 23.655; // Valore calcolato manualmente dai dati di test
        $this->assertEquals($expectedMedia, round($media, 3));
    }

    /**
     * Testa il metodo getFormulaVotoLaurea.
     */
    public function testFormulaVotoLaurea() 
    {
        $formula = $this->carriera->getFormulaVotoLaurea();
        $this->assertIsString($formula);
        $this->assertNotEmpty($formula);
    }

    /**
     * Testa il metodo getEsami.
     */
    public function testEsami()
    {
        $esami = $this->carriera->getEsami();
        $this->assertIsArray($esami);
        $this->assertNotEmpty($esami);
        $this->assertInstanceOf('EsameLaureando', $esami[0]);
    }

    /**
     * Testa il metodo elaboraEsami.
     */
    public function testElaboraEsami()
    {
        $carriera = [
            'Esami' => [
                'Esame' => [
                    [
                        'MATRICOLA' => 123456,
                        'DES' => 'Test Exam',
                        'VOTO' => '30',
                        'PESO' => 6,
                        'DATA_ESAME' => '2020-01-01',
                        'PIANO' => 'Y'
                    ]
                ]
            ]
        ];
        
        $esami = $this->carriera->elaboraEsami($carriera);
        $this->assertIsArray($esami);
        $this->assertCount(1, $esami);
        $this->assertInstanceOf('EsameLaureando', $esami[0]);
    }

    /**
     * Testa il calcolo dei CFU totali.
     */
    public function testCFUTotali()
    {
        $cfuTotali = $this->carriera->getCfuTotali();
        $expectedCfuTotali = 177; // Valore calcolato manualmente dai dati di test
        $this->assertEquals($expectedCfuTotali, $cfuTotali);
    }

    /**
     * Testa il calcolo dei CFU in media.
     */
    public function testCFUMedia()
    {
        $cfuMedia = $this->carriera->getCfuMedia();
        $expectedCfuMedia = 174; // Valore calcolato manualmente dai dati di test che hanno isInAvg true
        $this->assertEquals($expectedCfuMedia, $cfuMedia);
    }
    
    /**
     * Testa la lista degli esami in media.
     */
    public function testListaEsamiInMedia()
    {
        $esami = $this->carriera->getEsami();
        
        // Creiamo un array con gli esami che dovrebbero fare media
        $esamiInMedia = array_filter($esami, function($esame) {
            return $esame->isCurricolare();
        });

        // Verifichiamo che ci siano esami in media
        $this->assertNotEmpty($esamiInMedia);

        // Verifichiamo che ogni esame in media abbia i dati corretti
        foreach ($esamiInMedia as $esame) {
            $this->assertIsString($esame->getNome());
            $this->assertIsInt($esame->getCfu());
            $this->assertNotNull($esame->getVoto());
            $this->assertTrue($esame->isCurricolare());
        }

        // Verifichiamo la corrispondenza con i CFU totali
        $cfuTotali = array_reduce($esamiInMedia, function($sum, $esame) {
            return $sum + $esame->getCfu();
        }, 0);
        
        $this->assertEquals($cfuTotali, $this->carriera->getCfuTotali());
    }
}