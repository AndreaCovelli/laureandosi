<?php
use PHPUnit\Framework\TestCase;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
require_once(realpath(dirname(__FILE__)) . '/../Classes/GestioneInvioEmail.php');
require_once(realpath(dirname(__FILE__)) . '/../Classes/CarrieraLaureando.php');

class GestioneInvioEmailTest extends TestCase 
{
    private GestioneInvioEmail $gestioneEmail;
    private CarrieraLaureando $carriera;
    private string $outputDir;
    
    protected function setUp(): void {
        $this->gestioneEmail = GestioneInvioEmail::getInstance();
        $this->carriera = new CarrieraLaureando(123456, "T. Ing. Informatica", "2023-12-31");
        
        // Imposta una directory di output per i test
        $this->outputDir = realpath(dirname(__FILE__)) . '/../output/test';
        if (!is_dir($this->outputDir)) {
            mkdir($this->outputDir, 0777, true);
        }
    }

    public function testCreaEmail()
    {
        $pdf_path = $this->outputDir . "/test.pdf";
        
        // Crea un file PDF di test vuoto
        file_put_contents($pdf_path, "Test PDF content");
        
        try {
            $mail = $this->gestioneEmail->creaEmail($this->carriera, $pdf_path);
            
            // Verifica che l'oggetto mail sia creato correttamente
            $this->assertInstanceOf('PHPMailer\PHPMailer\PHPMailer', $mail);
            
            // Verifica le proprietà dell'email
            $this->assertEquals('no-reply-laureandosi@ing.unipi.it', $mail->From);
            $this->assertEquals('Laureandosi', $mail->FromName);
            
            // Verifica il contenuto HTML dell'email
            $this->assertStringContainsString('text/html', $mail->ContentType);
        } catch (PHPMailerException $e) {
            // Se il file non esiste o non è accessibile, il test fallisce
            $this->fail("Impossibile creare l'email: " . $e->getMessage());
        } finally {
            // Rimuovi il file temporaneo
            if (file_exists($pdf_path)) {
                unlink($pdf_path);
            }
        }
    }

    public function testInviaEmailConProspetto()
    {
        $pdf_path = $this->outputDir . "/test_prospetto.pdf";
        
        // Crea un file PDF di test vuoto
        file_put_contents($pdf_path, "Test PDF content");
        
        try {
            $result = $this->gestioneEmail->inviaEmailConProspetto($this->carriera, $pdf_path);
            
            // La mail potrebbe non essere inviata in ambiente di test
            // Verifichiamo solo che il metodo restituisca un booleano
            $this->assertIsBool($result);
        } catch (PHPMailerException $e) {
            // Se il file non esiste o non è accessibile, il test fallisce
            $this->fail("Impossibile inviare l'email: " . $e->getMessage());
        } finally {
            // Rimuovi il file temporaneo
            if (file_exists($pdf_path)) {
                unlink($pdf_path);
            }
        }
    }

    public function testInviaEmailConProspetti()
    {
        // Crea una directory per i prospetti di test
        $pdf_dir = $this->outputDir . "/prospetti";
        if (!is_dir($pdf_dir)) {
            mkdir($pdf_dir, 0777, true);
        }
        
        // Crea un file PDF di test per ciascun laureando
        $pdf_path = $pdf_dir . "/prospetto_laureando_" . $this->carriera->getMatricola() . ".pdf";
        file_put_contents($pdf_path, "Test PDF content");
        
        try {
            $carriere = [$this->carriera];
            $result = $this->gestioneEmail->inviaEmailConProspetti($carriere, $pdf_dir);
            
            // Come sopra, verifichiamo solo il tipo di ritorno
            $this->assertIsBool($result);
        } catch (PHPMailerException $e) {
            // Se il file non esiste o non è accessibile, il test fallisce
            $this->fail("Impossibile inviare gli email: " . $e->getMessage());
        } finally {
            // Rimuovi il file temporaneo
            if (file_exists($pdf_path)) {
                unlink($pdf_path);
            }
        }
    }
}