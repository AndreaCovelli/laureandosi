<?php
use PHPUnit\Framework\TestCase;

require_once(realpath(dirname(__FILE__)) . '\..\Classes\CarrieraLaureando.php');
require_once(realpath(dirname(__FILE__)) . '\..\Classes\CarrieraLaureandoInformatica.php');
require_once(realpath(dirname(__FILE__)) . '\..\Classes\GestioneCarrieraStudente.php');

class CarrieraLaureandoInformaticaTest extends TestCase
{
    private CarrieraLaureandoInformatica $carriera;
    
    protected function setUp(): void
    {
        $matricola = 123456;
        $CdL = "T. Ing. Informatica";
        $dataLaurea = "2023-10-01";
        $this->carriera = new CarrieraLaureandoInformatica($matricola, $CdL, $dataLaurea);
    }

    public function testRestituisciMediaEsamiInformatici()
    {
        $media = $this->carriera->RestituisciMediaEsamiInformatici();
        
        // Calcolata manualmente dal file JSON
        $expectedMedia = 23.67;
        
        $this->assertEquals($expectedMedia, round($media, 2));
    }

    public function testCalcolaBonusInCorso()
    {
        // Studente immatricolato nel 2016, si laurea nel 2020 (in corso)
        $carrieraInCorso = new CarrieraLaureandoInformatica(123456, "T. Ing. Informatica", "2020-05-22");
        $this->assertTrue($carrieraInCorso->getBonus());
    }

    public function testCalcolaBonusFuoriCorso()
    {
        // Studente immatricolato nel 2016, si laurea nel 2023 (fuori corso)
        $carrieraFuoriCorso = new CarrieraLaureandoInformatica(123456, "T. Ing. Informatica", "2023-10-01");
        $this->assertFalse($carrieraFuoriCorso->getBonus());
    }

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
        $carriera->rimuoviEsamePiuBasso();
        
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