<?php

abstract class Prospetto {
    protected \FPDF $pdf;

    /**
     * Constructor
     * @param \FPDF $pdf
     */
    public function __construct(\FPDF $pdf) {
        $this->pdf = is_null($pdf) ? new \FPDF('P', 'mm', 'A4') : $pdf;
    }

    /**
     * Generates the prospetto
     * @return void
     */
    public function salvaProspetto(string $nomefile): void{
        // Assicurati che la directory esista
        $directory = dirname($nomefile);
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
        $this->pdf->Output('F', $nomefile.'.pdf');
    }
}