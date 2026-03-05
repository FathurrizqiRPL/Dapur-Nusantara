<?php
define('FPDF_FONTPATH', __DIR__ . '/../fpdf/font/');
require_once __DIR__ . '/../fpdf/fpdf.php';
require __DIR__ . '/../../config.php';

if (!class_exists('FPDF')) {
    die('FPDF class tidak ditemukan.');
}

/* ===============================
   MODE LAPORAN
=================================*/
$mode = $_GET['mode'] ?? 'all';
$whereTanggal = '';
$tanggalInfo = '';

if ($mode === '30') {
    $tanggalAkhir = date('Y-m-d');
    $tanggalAwal = date('Y-m-d', strtotime('-30 days'));
    $whereTanggal = "AND r.tanggal >= '$tanggalAwal'";
    $tanggalInfo = "Periode $tanggalAwal s/d $tanggalAkhir";
} else {
    $tanggalInfo = "Semua Periode";
}

/* ===============================
   QUERY DATA
=================================*/
$qIncome = mysqli_query($conn, "
    SELECT SUM(r.harga_total) AS total
    FROM reservations r
    WHERE r.status IN ('confirmed','refunded')
    $whereTanggal
");
$income = mysqli_fetch_assoc($qIncome)['total'] ?? 0;

$qRefund = mysqli_query($conn, "
    SELECT SUM(r.harga_total) AS total
    FROM reservations r
    WHERE r.status = 'refunded'
    $whereTanggal
");
$refund = mysqli_fetch_assoc($qRefund)['total'] ?? 0;

$bersih = $income - $refund;

/* ===============================
   PDF CLASS CUSTOM
=================================*/
class PDF extends FPDF {

    function Header() {

    // Logo
    $this->Image(__DIR__ . '/../../assets/img/logo2hitam.png',10,12,22);

    
    $this->SetFont('Arial','B',16);
    $this->Cell(0,8,'PT DAPUR NUSANTARA INDONESIA',0,1,'C');

    
    $this->SetFont('Arial','',11);
    $this->Cell(0,6,'Sistem Reservasi & Manajemen Restoran',0,1,'C');


    $this->SetFont('Arial','',10);
    $this->MultiCell(0,5,
        "Jl. Anggrek Raya No. 18, Kompleks Kuliner Surya Mandala,\n".
        "Jakarta Timur, DKI Jakarta 13450\n".
        "Telp: (021) 1234-5678 | Email: info@dapurnusantara.co.id",
        0,
        'C'
    );

    $this->Ln(3);
    $this->SetLineWidth(1);
    $this->Line(10,$this->GetY(),200,$this->GetY());
    $this->Ln(6);
}

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Generated on '.date('d M Y H:i').' | Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

/* ===============================
   GENERATE PDF
=================================*/
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();

/* ===============================
   INFO LAPORAN
=================================*/
$pdf->SetFont('Arial','',11);
$pdf->Cell(40,8,'Periode',0);
$pdf->Cell(0,8,': '.$tanggalInfo,0,1);

$pdf->Cell(40,8,'Dicetak Oleh',0);
$pdf->Cell(0,8,': Admin System',0,1);

$pdf->Ln(5);

/* ===============================
   SUMMARY BOX
=================================*/
$pdf->SetFont('Arial','B',11);
$pdf->SetFillColor(240,240,240);

$pdf->Cell(63,10,'Total Income',1,0,'C',true);
$pdf->Cell(63,10,'Total Refund',1,0,'C',true);
$pdf->Cell(63,10,'Net Income',1,1,'C',true);

$pdf->SetFont('Arial','',11);
$pdf->Cell(63,10,'Rp '.number_format($income),1,0,'C');
$pdf->Cell(63,10,'Rp '.number_format($refund),1,0,'C');
$pdf->Cell(63,10,'Rp '.number_format($bersih),1,1,'C');

$pdf->Ln(8);

/* ===============================
   DETAIL PER BULAN (ALL MODE)
=================================*/
if ($mode === 'all') {

    $qBulan = mysqli_query($conn, "
        SELECT DATE_FORMAT(r.tanggal, '%Y-%m') AS bulan,
               SUM(CASE WHEN r.status IN ('confirmed','refunded') THEN r.harga_total ELSE 0 END) AS income,
               SUM(CASE WHEN r.status = 'refunded' THEN r.harga_total ELSE 0 END) AS refund
        FROM reservations r
        WHERE r.status IN ('confirmed','refunded')
        GROUP BY DATE_FORMAT(r.tanggal, '%Y-%m')
        ORDER BY DATE_FORMAT(r.tanggal, '%Y-%m') DESC
    ");

    $pdf->SetFont('Arial','B',11);
    $pdf->SetFillColor(220,220,220);
    $pdf->Cell(60,8,'Bulan',1,0,'C',true);
    $pdf->Cell(45,8,'Income',1,0,'C',true);
    $pdf->Cell(45,8,'Refund',1,0,'C',true);
    $pdf->Cell(40,8,'Net',1,1,'C',true);

    $pdf->SetFont('Arial','',10);

    while ($row = mysqli_fetch_assoc($qBulan)) {

        $bulan = date('F Y', strtotime($row['bulan'].'-01'));
        $incomeBulan = $row['income'];
        $refundBulan = $row['refund'];
        $netBulan = $incomeBulan - $refundBulan;

        $pdf->Cell(60,8,$bulan,1);
        $pdf->Cell(45,8,'Rp '.number_format($incomeBulan),1);
        $pdf->Cell(45,8,'Rp '.number_format($refundBulan),1);
        $pdf->Cell(40,8,'Rp '.number_format($netBulan),1,1);
    }
}
/* ===============================
   PENJELASAN SUMBER KEUANGAN
=================================*/
$pdf->Ln(10);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,8,'Keterangan Laporan Keuangan',0,1);

$pdf->SetFont('Arial','',10);
$pdf->MultiCell(0,6,
"1. Sumber Income berasal dari total reservasi yang telah dikonfirmasi (status confirmed) serta reservasi yang telah diproses refund.
2. Pengurangan dana berasal dari transaksi refund akibat pembatalan reservasi oleh pelanggan maupun persetujuan pembatalan oleh admin.
3. Net Income merupakan selisih antara Total Income dan Total Refund pada periode yang dipilih."
);

$pdf->Ln(10);

/* ===============================
   TANDA TANGAN RESMI
=================================*/
$pdf->SetFont('Arial','',11);

$pdf->Cell(0,6,'Jakarta, '.date('d F Y'),0,1,'R');
$pdf->Ln(15);

$pdf->Cell(0,6,'Mengetahui,',0,1,'R');
$pdf->Cell(0,6,'Kepala PT Dapur Nusantara Indonesia',0,1,'R');

$pdf->Ln(25);

$pdf->Cell(0,6,'(______________________________)',0,1,'R');
$pdf->Cell(0,6,'Budi Santoso, S.E., M.M.',0,1,'R');


$pdf->Output();