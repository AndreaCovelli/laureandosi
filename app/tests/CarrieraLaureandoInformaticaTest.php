<?php
use PHPUnit\Framework\TestCase;

require_once(__DIR__ . '/../../app/src/classes/CarrieraLaureando.php');
require_once(__DIR__ . '/../../app/src/classes/CarrieraLaureandoInformatica.php');
require_once(__DIR__ . '/../../app/src/classes/GestioneCarrieraLaureando.php');

/**
 * Insieme di test per la classe CarrieraLaureandoInformatica.
 */
class CarrieraLaureandoInformaticaTest extends TestCase
{
    private CarrieraLaureandoInformatica $carriera;
    
    /**
     * Inizializza i dati di test (viene eseguita prima di ogni test)
     */
    protected function setUp(): void
    {
        $matricola = 123456;
        $CdL = "T. Ing. Informatica";
        $dataLaurea = "2023-10-01";
        $this->carriera = new CarrieraLaureandoInformatica($matricola, $CdL, $dataLaurea);
    }

    /**
     * Testa il metodo RestituisciMediaEsamiInformatici.
     */
    public function testRestituisciMediaEsamiInformatici()
    {
        $media = $this->carriera->RestituisciMediaEsamiInformatici();
        
        // Calcolata manualmente dal file JSON
        $expectedMedia = 23.67;
        
        $this->assertEquals($expectedMedia, round($media, 2));
    }

    /**
     * Testa il metodo testCalcolaBonusInCorso.
     */
    public function testCalcolaBonusInCorso()
    {
        // Laureando immatricolato nel 2016, si laurea nel 2020 entro il 31 maggio (in corso)
        $carrieraInCorso = new CarrieraLaureandoInformatica(123456, "T. Ing. Informatica", "2020-05-22");
        $this->assertTrue($carrieraInCorso->getBonus());
    }

    /**
     * Testa il metodo testCalcolaBonusFuoriCorso.
     */
    public function testCalcolaBonusFuoriCorso()
    {
        // Laureando immatricolato nel 2016, si laurea nel 2023 (fuori corso)
        $carrieraFuoriCorso = new CarrieraLaureandoInformatica(123456, "T. Ing. Informatica", "2023-10-01");
        $this->assertFalse($carrieraFuoriCorso->getBonus());
    }

    /**
     * Testa il metodo testCalcolaBonusInCorso.
     */
    public function testRimozioneEsamePiuBasso()
    {
        // Crea una carriera con bonus
        $carriera = new CarrieraLaureandoInformatica(123456, "T. Ing. Informatica", "2020-05-22");
        
        // Verifica che il bonus sia attivo
        $this->assertTrue($carriera->getBonus());
        
        // Recupera gli esami
        $esami = $carriera->getEsami();
        
        // Conta quanti esami sono inclusi nella media prima della rimozione
        $countPrima = 0;
        foreach ($esami as $esame) {
            if ($esame->isInAvg()) {
                $countPrima++;
            }
        }

        // Rimuove l'esame più basso
        $carriera->escludiEsamePiuBassoDallaMedia();
        
        // Conta quanti esami sono inclusi nella media dopo la rimozione
        $countDopo = 0;
        foreach ($esami as $esame) {
            if ($esame->isInAvg()) {
                $countDopo++;
            }
        }
        
        // Verifica che sia stato rimosso esattamente un esame
        $this->assertEquals($countPrima - 1, $countDopo);
    }

    /**
     * Testa il bonus sulla media ponderata.
     */
    public function testMediaPonderataDopoBonusEsame()
    {
        // Crea una carriera con bonus
        $carriera = new CarrieraLaureandoInformatica(123456, "T. Ing. Informatica", "2020-05-22");
        
        // La media ponderata dovrebbe essere maggiore dopo la rimozione dell'esame più basso
        $mediaOriginale = $carriera->getMediaPonderata();
        
        // Verifica che la media sia aumentata
        $this->assertGreaterThan($mediaOriginale, $carriera->RestituisciMediaPonderata());
    }
}