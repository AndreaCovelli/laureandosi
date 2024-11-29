<?php
require('C:\Users\Andco\Local Sites\laureandosi\app\public\lib\fpdf184\fpdf.php');

class StudentReport extends FPDF {
    function Header() {
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'T. Ing. Informatica', 0, 1, 'L');
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, 'CARRIERA E SIMULAZIONE DEL VOTO DI LAUREA', 0, 1, 'L');
        $this->Ln(5);
    }

    function StudentInfo($matricola, $nome, $cognome, $email, $data, $bonus) {
        $this->SetFont('Arial', '', 10);
        
        $this->Cell(30, 6, 'Matricola:', 0, 0);
        $this->Cell(60, 6, $matricola, 0, 1);
        
        $this->Cell(30, 6, 'Nome:', 0, 0);
        $this->Cell(60, 6, $nome, 0, 1);
        
        $this->Cell(30, 6, 'Cognome:', 0, 0);
        $this->Cell(60, 6, $cognome, 0, 1);
        
        $this->Cell(30, 6, 'Email:', 0, 0);
        $this->Cell(60, 6, $email, 0, 1);
        
        $this->Cell(30, 6, 'Data:', 0, 0);
        $this->Cell(60, 6, $data, 0, 1);
        
        $this->Cell(30, 6, 'Bonus:', 0, 0);
        $this->Cell(60, 6, $bonus, 0, 1);
        
        $this->Ln(10);
    }

    function ExamTableHeader() {
        $this->SetFont('Arial', 'B', 9);
        $this->Cell(80, 7, 'ESAME', 1, 0, 'L');
        $this->Cell(15, 7, 'CFU', 1, 0, 'C');
        $this->Cell(15, 7, 'VOT', 1, 0, 'C');
        $this->Cell(15, 7, 'MED', 1, 0, 'C');
        $this->Cell(15, 7, 'INF', 1, 1, 'C');
    }

    function ExamRow($esame, $cfu, $voto, $med, $inf) {
        $this->SetFont('Arial', '', 9);
        $this->Cell(80, 6, $esame, 1, 0, 'L');
        $this->Cell(15, 6, $cfu, 1, 0, 'C');
        $this->Cell(15, 6, $voto, 1, 0, 'C');
        $this->Cell(15, 6, $med ? 'X' : '', 1, 0, 'C');
        $this->Cell(15, 6, $inf ? 'X' : '', 1, 1, 'C');
    }
}

// Create PDF instance
$pdf = new StudentReport();
$pdf->AddPage();

// Add student information
$pdf->StudentInfo(
    '123456',
    'XXXXXXX',
    'YYYYYYY',
    'f.yyyyyy@studenti.unipi.it',
    '2022-09-23',
    'SI'
);

// Add exam table header
$pdf->ExamTableHeader();

// Sample exam data (you can load this from a database)
$exams = [
    ['FONDAMENTI DI PROGRAMMAZIONE', 9, 21, true, false],
    ['ANALISI MATEMATICA I', 12, 23, true, false],
    ['ALGEBRA LINEARE E ANALISI MATEMATICA II', 12, 27, true, false],
    ['FISICA GENERALE I', 12, 30, true, false],
    // Add other exams here
];

// Add exam rows
foreach ($exams as $exam) {
    $pdf->ExamRow($exam[0], $exam[1], $exam[2], $exam[3], $exam[4]);
}

// Add summary statistics
$pdf->Ln(10);
$pdf->SetFont('Arial', '', 10);
$pdf->Cell(0, 6, 'Media Pesata (M): 27.491', 0, 1);
$pdf->Cell(0, 6, 'Crediti che fanno media (CFU): 165', 0, 1);
$pdf->Cell(0, 6, 'Crediti curriculari conseguiti: 177/177', 0, 1);
$pdf->Cell(0, 6, 'Voto di tesi (T): 0', 0, 1);
$pdf->Cell(0, 6, 'Formula calcolo voto di laurea: M*3+18+T+C', 0, 1);
$pdf->Cell(0, 6, 'Media pesata esami INF: 27.522', 0, 1);

// Output PDF
$pdf->Output('F', "file.pdf");
?>