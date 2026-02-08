<?php
/*************************************************
 * POINT 1️⃣ - DEFINE FONT PATH
 *************************************************/
define('FPDF_FONTPATH', __DIR__ . '/../fpdf/font/');


/*************************************************
 * POINT 2️⃣ - REQUIRE LIBRARY & DATABASE
 *************************************************/
require __DIR__ . '/../fpdf/fpdf.php';
require __DIR__ . '/../../config.php';


/*************************************************
 * POINT 3️⃣ - AMBIL MODE LAPORAN (all / 30 hari)
 *************************************************/
$mode = $_GET['mode'] ?? 'all';
$whereTanggal = '';

if ($mode === '30') {
    $whereTanggal = "AND tanggal >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
}


/*************************************************
 * POINT 4️⃣ - QUERY DATABASE
 *************************************************/

// Penghasilan (Confirmed)
$qIncome = mysqli_query($conn, "
    SELECT SUM(harga_total) AS total 
    FROM reservations 
    WHERE status = 'Confirmed' $whereTanggal
");
$income = mysqli_fetch_assoc($qIncome)['total'] ?? 0;

// Refund (Cancelled + ada rekening)
$qRefund = mysqli_query($conn, "
    SELECT SUM(harga_total) AS total 
    FROM reservations 
    WHERE status = 'Cancelled'
    AND rekening_refund IS NOT NULL
    $whereTanggal
");
$refund = mysqli_fetch_assoc($qRefund)['total'] ?? 0;

// Bersih
$bersih = $income - $refund;


/*************************************************
 * POINT 5️⃣ - GENERATE PDF
 *************************************************/
$pdf = new FPDF();
$pdf->AddPage();

$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'LAPORAN KEUANGAN',0,1,'C');

$pdf->SetFont('Arial','',12);
$pdf->Cell(0,8,
    $mode === '30' ? 'Periode 30 Hari Terakhir' : 'Keseluruhan',
    0,1,'C'
);

$pdf->Ln(10);

$pdf->Cell(100,10,'Total Penghasilan',1);
$pdf->Cell(0,10,'Rp '.number_format($income),1,1);

$pdf->Cell(100,10,'Total Refund',1);
$pdf->Cell(0,10,'Rp '.number_format($refund),1,1);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(100,10,'Total Bersih',1);
$pdf->Cell(0,10,'Rp '.number_format($bersih),1,1);

$pdf->Output();
