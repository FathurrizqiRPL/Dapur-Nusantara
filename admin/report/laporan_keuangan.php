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
$tanggalInfo = '';

if ($mode === '30') {
    $tanggalAkhir = date('Y-m-d');
    $tanggalAwal = date('Y-m-d', strtotime('-30 days'));
    $whereTanggal = "AND r.tanggal >= '$tanggalAwal'";
    $tanggalInfo = "Dari $tanggalAwal sampai $tanggalAkhir";
} else {
    $tanggalInfo = "Laporan Keseluruhan (Semua Periode)";
}


/*************************************************
 * POINT 4️⃣ - QUERY DATABASE
 *************************************************/

// Penghasilan (Confirmed)
$qIncome = mysqli_query($conn, "
    SELECT SUM(r.harga_total) AS total
    FROM reservations r
    WHERE (
        r.status = 'confirmed'
        OR r.status = 'refunded'
      ) $whereTanggal
");
$income = mysqli_fetch_assoc($qIncome)['total'] ?? 0;

// Refund (Cancelled + ada rekening)
$qRefund = mysqli_query($conn, "
    SELECT SUM(r.harga_total) AS total
    FROM reservations r
    WHERE r.status = 'refunded'
    $whereTanggal
");
$refund = mysqli_fetch_assoc($qRefund)['total'] ?? 0;

// Bersih
$bersih = $income - $refund;

// Untuk laporan keseluruhan, ambil data per bulan (income, refund, netto)
$dataPerBulan = [];
if ($mode === 'all') {
    $qBulan = mysqli_query($conn, "
        SELECT DATE_FORMAT(r.tanggal, '%Y-%m') AS bulan,
               SUM(CASE WHEN r.status IN ('confirmed','refunded') THEN r.harga_total ELSE 0 END) AS gross_income,
               SUM(CASE WHEN r.status = 'refunded' THEN r.harga_total ELSE 0 END) AS refund,
               (SUM(CASE WHEN r.status IN ('confirmed','refunded') THEN r.harga_total ELSE 0 END) - SUM(CASE WHEN r.status = 'refunded' THEN r.harga_total ELSE 0 END)) AS net
        FROM reservations r
        WHERE r.status IN ('confirmed', 'refunded')
        GROUP BY DATE_FORMAT(r.tanggal, '%Y-%m')
        ORDER BY DATE_FORMAT(r.tanggal, '%Y-%m') DESC
    ");
    while ($row = mysqli_fetch_assoc($qBulan)) {
        $bulanTahun = date('F Y', strtotime($row['bulan'] . '-01'));
        $dataPerBulan[] = [
            'bulan'   => $bulanTahun,
            'income'  => (float)$row['gross_income'],
            'refund'  => (float)$row['refund'],
            'total'   => (float)$row['net']
        ];
    }
}


/*************************************************
 * POINT 5️⃣ - GENERATE PDF
 *************************************************/
$pdf = new FPDF();
$pdf->AddPage();

// Judul
$pdf->SetFont('Arial','B',16);
$pdf->Cell(0,10,'LAPORAN KEUANGAN DAPUR NUSANTARA',0,1,'C');

// Periode
$pdf->SetFont('Arial','',11);
$pdf->Cell(0,8,$tanggalInfo,0,1,'C');

$pdf->Ln(5);

// Untuk 30 hari
if ($mode === '30') {
    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(100,10,'Total Penghasilan',1);
    $pdf->Cell(0,10,'Rp '.number_format($income),1,1);

    $pdf->Cell(100,10,'Total Refund',1);
    $pdf->Cell(0,10,'Rp '.number_format($refund),1,1);

    $pdf->SetFont('Arial','B',12);
    $pdf->Cell(100,10,'Total Bersih',1);
    $pdf->Cell(0,10,'Rp '.number_format($bersih),1,1);
}

// Untuk keseluruhan, tampilkan per bulan
if ($mode === 'all' && count($dataPerBulan) > 0) {
    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(60,10,'Bulan',1);
    $pdf->Cell(45,10,'Penghasilan',1);
    $pdf->Cell(45,10,'Refund',1);
    $pdf->Cell(0,10,'Bersih',1,1);

    $pdf->SetFont('Arial','',10);
    $totalIncomeAll = 0;
    $totalRefundAll = 0;
    $totalNetAll = 0;
    foreach ($dataPerBulan as $data) {
        $pdf->Cell(60,8,$data['bulan'],1);
        $pdf->Cell(45,8,'Rp '.number_format($data['income']),1);
        $pdf->Cell(45,8,'Rp '.number_format($data['refund']),1);
        $pdf->Cell(0,8,'Rp '.number_format($data['total']),1,1);

        $totalIncomeAll += $data['income'];
        $totalRefundAll += $data['refund'];
        $totalNetAll += $data['total'];
    }

    $pdf->SetFont('Arial','B',11);
    $pdf->Cell(60,10,'TOTAL',1);
    $pdf->Cell(45,10,'Rp '.number_format($totalIncomeAll),1);
    $pdf->Cell(45,10,'Rp '.number_format($totalRefundAll),1);
    $pdf->Cell(0,10,'Rp '.number_format($totalNetAll),1,1);
}

$pdf->Output();
