<?php
use PHPUnit\Framework\TestCase;

require_once(realpath(dirname(__FILE__)) . '\..\Classes\CarrieraLaureando.php');
require_once(realpath(dirname(__FILE__)) . '\..\Classes\GestioneCarrieraLaureando.php');

class CarrieraLaureandoTest extends TestCase 
{
    private CarrieraLaureando $carriera;
    
    protected function setUp(): void
    {
        $matricola = 123456;
        $CdL = "T. Ing. Informatica";
        $this->carriera = new CarrieraLaureando($matricola, $CdL, "2023-10-01");
    }

    public function testAnagraficaStudente()
    {
        $this->assertEquals(123456, $this->carriera->getMatricola());
        $this->assertEquals("GIANLUIGI", $this->carriera->getNome());
        $this->assertEquals("DONNARUMMA", $this->carriera->getCognome());
        $this->assertEquals("nome.cognome@studenti.unipi.it", $this->carriera->getEmail());
        $this->assertEquals("T. Ing. Informatica", $this->carriera->getCdL());
    }

    public function testMediaPonderata()
    {
        $media = $this->carriera->getMediaPonderata();
        $expectedMedia = 23.655; // Valore calcolato manualmente dai dati di test
        $this->assertEquals($expectedMedia, round($media, 3));
    }

    public function testFormulaVotoLaurea() 
    {
        $formula = $this->carriera->getFormulaVotoLaurea();
        $this->assertIsString($formula);
        $this->assertNotEmpty($formula);
    }

    public function testEsami()
    {
        $esami = $this->carriera->getEsami();
        $this->assertIsArray($esami);
        $this->assertNotEmpty($esami);
        $this->assertInstanceOf('EsameLaureando', $esami[0]);
    }

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
    public function testCFUTotali()
    {
        $cfuTotali = $this->carriera->getCfuTotali();
        $expectedCfuTotali = 177; // Valore calcolato manualmente dai dati di test
        $this->assertEquals($expectedCfuTotali, $cfuTotali);
    }

    public function testCFUMedia()
    {
        $cfuMedia = $this->carriera->getCfuMedia();
        $expectedCfuMedia = 174; // Valore calcolato manualmente dai dati di test che hanno isInAvg true
        $this->assertEquals($expectedCfuMedia, $cfuMedia);
    }
    
    public function testStampaEsamiInMedia()
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