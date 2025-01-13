<?php

require_once(realpath(dirname(__FILE__)) . '\ProspettoPDFLaureando.php');
require_once(realpath(dirname(__FILE__)) . '\GestioneParametri.php');

/**
 * Classe per la generazione del prospetto di simulazione del voto di laurea
 * Viene utilizzata per aggiungere la simulazione del voto di laurea al prospetto laureando
 * 
 * Questa classe viene chiamata da ProspettoPDFCommissione per generare il prospetto di un laureando
 * con in più la simulazione del voto di laurea
 */
class ProspettoPDFLaureandoSimulazione extends ProspettoPDFLaureando{

    // Non ho messo il construttore perchè uso quello della classe padre
    // e lo ridefinisco solo in ProspettoPDFCommissione

    /**
     * Genera il prospetto con la simulazione del voto di laurea
     * @return void
     */
    public function GeneraProspettoSimulazione(): void {
        parent::generaProspetto(); // Chiamo il metodo della classe padre
        
        $this->aggiungiSimulazione(); // Aggiungo la simulazione
    }

    /**
     * Aggiungi la sezione della simulazione al prospetto
     * @return void
     */
    private function aggiungiSimulazione(): void {
        $gestoreParametri = GestioneParametri::getInstance();

        // Formula di calcolo del voto di laurea
        $formula = $this->carriera_laureando->getFormulaVotoLaurea();
        
        // Parametro per il voto tesi T
        $parameter_t = $gestoreParametri->RestituisciParametriCdl()['degree_programs'][$this->carriera_laureando->getCdL()]['parameters']['par-T'];
        // Parametro per il voto commissione C
        $parameter_c = $gestoreParametri->RestituisciParametriCdl()['degree_programs'][$this->carriera_laureando->getCdL()]['parameters']['par-C'];

        list($t_min,$t_max,$t_step) = array_values($parameter_t);
        list($c_min,$c_max,$c_step) = array_values($parameter_c);

        // Sostituisci le lettere nella formula con i valori effettivi
        $mediaPonderata = $this->carriera_laureando->getMediaPonderata();
        $cfuMedia = $this->carriera_laureando->getCfuMedia();
        $formula = str_replace(['M', 'CFU'], [$mediaPonderata, $cfuMedia], $formula);

        $this->pdf->SetFontSize(10);

        $this->pdf->Ln(3);

        $this->pdf->Cell(0, 5, 'SIMULAZIONE DI VOTO DI LAUREA', 1, 1, 'C');

        // Aggiungi simulazione per T o C
        if($t_min !== 0){
            $this->GestisciVotoSimulazione($formula, $t_min, $t_max, $t_step);
        } else if ($c_min !== 0){
            $this->GestisciVotoSimulazione($formula, $c_min, $c_max, $c_step);
        }
    }

    /**
     * Calcola la lista dei possibili voti di laurea
     * e li aggiunge al prospetto simulazione.
     * La funzione differenzia tra voto di tesi e voto di commissione
     * 
     * @param string $formula
     * @param int $min
     * @param int $max
     * @param int $step
     * @return void
     */
    private function GestisciVotoSimulazione($formula, $min, $max, $step): void {
        $parametro = '';
        $righe = null;
        $colonne = null;
        $width_col = null;

        // Determina il parametro da usare in base al valore minimo del range
        $gestoreParametri = GestioneParametri::getInstance();
        $parameters = $gestoreParametri->RestituisciParametriCdl()['degree_programs'][$this->carriera_laureando->getCdL()]['parameters'];
        $parameter_t = $parameters['par-T'];
        
        // Se min corrisponde al t_min allora stiamo calcolando per T, altrimenti per C
        $t_min = $parameter_t['min'];
        $isVotoTesi = ($min === $t_min);
        $parametro = $isVotoTesi ? 'T' : 'C';
        
        // Se stiamo calcolando per T, azzeriamo C nella formula e viceversa
        $formula = $isVotoTesi ? str_replace('C', '0', $formula) : str_replace('T', '0', $formula);

        // Calcola layout della tabella
        $colonne = ($max - $min) / $step > 7 ? 2 : 1;
        $righe = ceil(($max - $min + 1) / $step / $colonne);
        $width_col = ($this->pdf->GetPageWidth() - 20) / $colonne;
        
        // Intestazione tabella
        for ($i = 0; $i < $colonne; $i++) {
            $header = $isVotoTesi ? 'VOTO TESI (T)' : 'VOTO COMMISSIONE (C)';
            $this->pdf->Cell($width_col / 2, 5, $header, 1, 0, 'C');
            $this->pdf->Cell($width_col / 2, 5, 'VOTO DI LAUREA', 1, 0, 'C');
        }
        $this->pdf->Ln();

        // Genera righe della tabella
        for ($riga = 0; $riga < $righe; $riga++) {
            for ($col = 0; $col < $colonne; $col++) {
                $voto = $min + ($riga + $col * $righe) * $step;
                if ($voto <= $max) {
                    $this->pdf->Cell($width_col / 2, 5, $voto, 1, 0, 'C');
                    $formula_valutata = $formula;
                    $formula_valutata = str_replace($parametro, $voto, $formula_valutata);
                    $voto_laurea = eval("return " . $formula_valutata . ";");
                    $this->pdf->Cell($width_col / 2, 5, round($voto_laurea, 3), 1, 0, 'C');
                }
            }
            $this->pdf->Ln();
        }

        // Aggiungi nota informativa
        $this->pdf->Ln(5);
        $informazioni = $isVotoTesi ? 
            "Scegli il voto di tesi, prendi il corrispondente voto di laurea" :
            "Scegli il voto commissione, prendi il corrispondente voto di laurea";

        // Aggiungi descrizione per un laureando della Triennale di Ingegneria Informatica
        if ($this->carriera_laureando->getCdL() === "T. Ing. Informatica") {
            $informazioni .= " e somma il voto di tesi tra 1 e 3, quindi arrotonda.";
        }else{
            $informazioni .= " ed arrotonda.";
        }

        $this->pdf->SetFontSize(10);
        $this->pdf->MultiCell(0, 5, "VOTO DI LAUREA FINALE: " . $informazioni);
    }
}