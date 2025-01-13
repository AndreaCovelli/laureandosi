<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once(dirname(dirname(__DIR__)) . '/lib/PHPMailer/src/PHPMailer.php');
require_once(dirname(dirname(__DIR__)) . '/lib/PHPMailer/src/SMTP.php');
require_once(dirname(dirname(__DIR__)) . '/lib/PHPMailer/src/Exception.php');

/**
 * Gestisce l'invio di email contenenti i prospetti di laurea agli studenti
 * 
 * Questa classe implementa il pattern Singleton per garantire un'unica istanza
 * di gestione delle email nell'applicazione. Si occupa di:
 * - Creare email personalizzate per ciascun laureando
 * - Allegare i prospetti PDF generati
 * - Gestire l'invio sia singolo che multiplo delle email
 * - Configurare la connessione SMTP con il server di posta
 */
class GestioneInvioEmail{
    private const SMTP_HOST = 'mixer.unipi.it';
    private const SMTP_PORT = 25;
    private const SENDER_EMAIL = 'no-reply-laureandosi@ing.unipi.it';
    private const SENDER_NAME = 'Laureandosi';

    private static ?GestioneInvioEmail $instance = null;
    
    public function __construct() {
    }

    public static function getInstance(): GestioneInvioEmail {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Crea email con prospetto di laurea in allegato
     * @param CarrieraLaureando $carriera
     * @param string $pdf_path
     * @return PHPMailer
     */
    private function creaEmail($carriera, $pdf_path): PHPMailer{
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->SMTPSecure = "tls";
        $mail->Host = self::SMTP_HOST;
        $mail->SMTPAuth = false;
        $mail->Port = self::SMTP_PORT;
        $mail->setFrom(self::SENDER_EMAIL, self::SENDER_NAME);

        $mail->CharSet = 'UTF-8';
        $mail->setLanguage('it');
        
        // In 'development' si usa l'indirizzo email di test
        $myAddr = 'a.covelli1@studenti.unipi.it';
        $mail->addAddress($myAddr, $carriera->getNome() . ' ' . $carriera->getCognome());
        
        // In 'production' si dovrebbe usare la riga sottostante
        // $mail->addAddress($carriera->getEmail(), $carriera->getNome() . ' ' . $carriera->getCognome());
        
        $mail->addAttachment($pdf_path, 'Prospetto di laurea.pdf');

        $cdl = $carriera->getCdL();

        $mail->Subject = 'Appello di laurea in ' . $cdl . ' - indicatori per il voto di laurea';
        $mail->isHTML(true);
        $mail->Body = 'Gentile ' . $carriera->getNome() . ' ' . $carriera->getCognome() . ',<br>'
            . '<p>Allego un prospetto contenente: la sua carriera, gli indicatori e la formula che la commissione adopererà per determinare il voto di laurea.</p>'
            . '<p>La prego di prendere visione dei dati relativi agli esami. In caso di dubbi scrivere a: <a href="mailto:vittoria.dattilo@unipi.it">vittoria.dattilo@unipi.it</a></p>'
            . '<p><strong>Alcune spiegazioni:</strong></p>'
            . '<ul>'
            . '<li>gli esami che non hanno un voto in trentesimi, hanno voto nominale zero al posto di giudizio o idoneità, in quanto non contribuiscono al calcolo della media ma solo al numero di crediti curriculari;</li>'
            . '<li>gli esami che non fanno media (pur contribuendo ai crediti curriculari) non hanno la spunta nella colonna MED;</li>'
            . '<li>il voto di tesi (T) appare nominalmente a zero in quanto verrà determinato in sede di laurea, e va da 18 a 30</li>'
            . '</ul>'
            . '<p>Cordiali saluti,<br>'
            . 'Unità Didattica DII</p>';

        return $mail;
    }

    /**
     * Invia email con prospetto di laurea in allegato
     * 
     * Gestisce l'effettivo invio dell'email attraverso il server SMTP configurato
     * 
     * In caso di errore, non viene sollevata un'eccezione ma viene restituito false
     * @param PHPMailer $mail
     * @return bool true se l'invio ha successo, false altrimenti
     */
    private function inviaEmail($mail): bool{
        try {
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Invia il prospetto a un singolo laureando
     * 
     * Metodo di utility che combina la creazione e la chiamata a inviaEmail
     * in un'unica operazione
     * 
     * @param CarrieraLaureando $carriera
     * @param string $pdf_path
     * @return bool true se la creaione e l'invio hanno successo, false altrimenti
     */
    public function inviaEmailConProspetto($carriera, $pdf_path): bool {
        $mail = $this->creaEmail($carriera, $pdf_path);
        return $this->inviaEmail($mail);
    }

    /**
     * Invia email con prospetto di laurea in allegato
     * @param array $carriere
     * @param string $pdf_path
     * @return bool true se tutti gli invii hanno successo, false se almeno uno fallisce
     */
    public function inviaEmailConProspetti($carriere, $pdf_path): bool {
        $mails = [];
        foreach ($carriere as $carriera){
            $pdf_path_tosend = '';
            $pdf_path_tosend = $pdf_path . DIRECTORY_SEPARATOR . "prospetto_laureando_" . $carriera->getMatricola() . '.pdf';
            $mails[] = $this->creaEmail($carriera, $pdf_path_tosend);
        }
        $success = true;
        foreach ($mails as $mail){
            $success = $success && $this->inviaEmail($mail); // Se almeno una mail fallisce, ritorna false
        }
        return $success;
    }
}