<?php
// controller/export_pdf.php
session_start();
include '../../../db/koneksi.php';

// Install TCPDF terlebih dahulu: composer require tecnickcom/tcpdf
// Atau download manual dari https://tcpdf.org/
require_once('../../../vendor/tecnickcom/tcpdf/tcpdf.php');

class LaporanPDF extends TCPDF
{
    private $nama_pimpinan;
    private $jabatan_pimpinan;

    public function __construct($nama_pimpinan, $jabatan_pimpinan)
    {
        parent::__construct();
        $this->nama_pimpinan = $nama_pimpinan;
        $this->jabatan_pimpinan = $jabatan_pimpinan;
    }

    // Header yang lebih clean dan professional
    public function Header()
    {
        // Background header dengan warna solid yang elegan
        $this->SetFillColor(34, 139, 204);
        $this->Rect(0, 0, 210, 28, 'F');

        // Logo placeholder (jika ada)
        // $this->Image('../../assets/logo.png', 20, 8, 15);

        // Company Name - centered dan bold
        $this->SetFont('helvetica', 'B', 18);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 6);
        $this->Cell(0, 10, 'BUANA PET SHOP', 0, 1, 'C');

        // Subtitle - smaller font
        $this->SetFont('helvetica', '', 10);
        $this->SetXY(10, 16);
        $this->Cell(0, 6, 'Laporan Transaksi Komprehensif', 0, 1, 'C');

        // Reset text color
        $this->SetTextColor(0, 0, 0);

        $this->Ln(8);
    }

    // Footer yang lebih clean
    public function Footer()
    {
        // Thin line separator
        $this->SetY(-20);
        $this->SetLineWidth(0.2);
        $this->SetDrawColor(34, 139, 204);
        $this->Line(10, $this->GetY(), 200, $this->GetY());

        // Footer content in two columns
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 8);
        $this->SetTextColor(100, 100, 100);

        // Left: Page info
        $this->Cell(95, 5, 'Halaman ' . $this->getAliasNumPage() . ' dari ' . $this->getAliasNbPages(), 0, 0, 'L');

        // Right: Generation time
        $this->Cell(95, 5, 'Dicetak: ' . date('d/m/Y H:i'), 0, 1, 'R');

        // Center: Company info
        $this->SetY(-10);
        $this->SetFont('helvetica', 'I', 7);
        $this->Cell(0, 5, 'Buana Pet Shop - Sistem Manajemen Transaksi', 0, 1, 'C');

        // Reset text color
        $this->SetTextColor(0, 0, 0);
    }

    // Method untuk section title
    public function addSectionTitle($title)
    {
        $this->SetFont('helvetica', 'B', 11);
        $this->SetTextColor(34, 139, 204);
        $this->Cell(0, 8, $title, 0, 1, 'L');
        $this->SetTextColor(0, 0, 0);

        // Underline
        $this->SetLineWidth(0.3);
        $this->SetDrawColor(34, 139, 204);
        $this->Line(10, $this->GetY() - 1, 70, $this->GetY() - 1);
        $this->Ln(5);
    }

    // Method untuk info row yang rapi
    public function addInfoRow($label, $value, $isLast = false)
    {
        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(40, 6, $label, 0, 0, 'L');
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 6, $value, 0, 1, 'L');

        if (!$isLast) {
            $this->Ln(1);
        }
    }

    // Signature section yang professional
    public function addSignature()
    {
        $this->Ln(15);

        // Signature area dengan border subtle
        $this->SetDrawColor(200, 200, 200);
        $this->SetLineWidth(0.1);
        $this->Rect(120, $this->GetY(), 70, 40, 'D');

        $this->Ln(3);

        // Date and location
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 6, 'Jakarta, ' . date('d F Y'), 0, 1, 'R');
        $this->Ln(2);

        // Title
        $this->SetFont('helvetica', '', 9);
        $this->Cell(0, 6, $this->jabatan_pimpinan, 0, 1, 'R');

        $this->Ln(20); // Space for signature

        // Name
        $this->SetFont('helvetica', 'B', 9);
        $this->Cell(0, 6, $this->nama_pimpinan, 0, 1, 'R');

        // Clean underline
        $y = $this->GetY();
        $this->SetLineWidth(0.2);
        $this->SetDrawColor(0, 0, 0);
        $this->Line(140, $y, 185, $y);
    }
}

try {
    // Get form data
    $jenis_laporan = $_POST['jenis_laporan'];
    $status_transaksi = $_POST['status_transaksi'];
    $date_range = $_POST['date_range'];
    $tanggal_mulai = $_POST['tanggal_mulai'] ?? null;
    $tanggal_selesai = $_POST['tanggal_selesai'] ?? null;
    $nama_pimpinan = $_POST['nama_pimpinan'];
    $jabatan_pimpinan = $_POST['jabatan_pimpinan'];

    // Create PDF instance
    $pdf = new LaporanPDF($nama_pimpinan, $jabatan_pimpinan);
    $pdf->SetCreator('Buana Pet Shop Management System');
    $pdf->SetAuthor('Administrator');
    $pdf->SetTitle('Laporan Transaksi - ' . date('d/m/Y'));
    $pdf->SetSubject('Laporan Transaksi');

    // Optimal margins untuk layout yang rapi
    $pdf->SetMargins(10, 35, 10);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(20);

    // Auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 25);

    // Add first page
    $pdf->AddPage();

    // === INFORMASI LAPORAN SECTION ===
    $pdf->addSectionTitle('INFORMASI LAPORAN');

    // Info dalam format yang rapi
    $pdf->addInfoRow('Jenis Laporan:', ucwords(str_replace('_', ' ', $jenis_laporan)));
    $pdf->addInfoRow('Status Transaksi:', ucwords($status_transaksi === 'all' ? 'Semua Status' : $status_transaksi));

    if ($date_range === 'custom' && $tanggal_mulai && $tanggal_selesai) {
        $periode = date('d F Y', strtotime($tanggal_mulai)) . ' s/d ' . date('d F Y', strtotime($tanggal_selesai));
    } else {
        $periode = 'Semua Periode';
    }
    $pdf->addInfoRow('Periode Laporan:', $periode);
    $pdf->addInfoRow('Tanggal Cetak:', date('d F Y, H:i:s'), true);

    $pdf->Ln(12);

    // Build queries and get data
    $all_data = [];
    $total_nilai = 0;
    $total_transaksi = 0;

    // Date condition
    $date_condition = "";
    $date_condition_service = "";
    if ($date_range === 'custom' && $tanggal_mulai && $tanggal_selesai) {
        $date_condition = " AND DATE(tc.tgl_transaksi_cart) BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'";
        $date_condition_service = " AND DATE(ts.tgl_transaksi_service) BETWEEN '$tanggal_mulai' AND '$tanggal_selesai'";
    }

    // Status condition
    $status_condition = "";
    $status_condition_service = "";
    if ($status_transaksi !== 'all') {
        $status_condition = " AND tc.status_transaksi_cart = '$status_transaksi'";
        $status_condition_service = " AND ts.status_transaksi_service = '$status_transaksi'";
    }

    // Get cart transactions
    if ($jenis_laporan === 'cart' || $jenis_laporan === 'all') {
        $query_cart = "
            SELECT 
                tc.id_transaksi_cart,
                tc.tgl_transaksi_cart,
                tc.status_transaksi_cart,
                u.username,
                p.nama as nama_pelanggan,
                b.nama_barang,
                cb.jumlah,
                b.harga_barang,
                (cb.jumlah * CAST(b.harga_barang AS DECIMAL)) as total
            FROM tb_transaksi_cart tc
            JOIN tb_cart cb ON tc.id_cart = cb.id_cart
            JOIN tb_barang b ON cb.id_barang = b.id_barang
            JOIN tb_user u ON cb.id_user = u.id_user
            JOIN tb_pelanggan p ON u.id_user = p.id_user
            WHERE 1=1 $status_condition $date_condition
            ORDER BY tc.tgl_transaksi_cart DESC
        ";

        $result_cart = mysqli_query($conn, $query_cart);
        while ($row = mysqli_fetch_assoc($result_cart)) {
            $all_data[] = [
                'id' => 'TC-' . sprintf('%04d', $row['id_transaksi_cart']),
                'tanggal' => $row['tgl_transaksi_cart'],
                'jenis' => 'Cart',
                'pelanggan' => $row['nama_pelanggan'],
                'item' => $row['nama_barang'],
                'jumlah' => $row['jumlah'],
                'harga' => $row['harga_barang'],
                'total' => $row['total'],
                'status' => $row['status_transaksi_cart']
            ];
            $total_nilai += $row['total'];
            $total_transaksi++;
        }
    }

    // Get service transactions
    if ($jenis_laporan === 'service' || $jenis_laporan === 'all') {
        $query_service = "
            SELECT 
                ts.id_transaksi_service,
                ts.tgl_transaksi_service,
                ts.status_transaksi_service,
                u.username,
                p.nama as nama_pelanggan,
                s.nama_service,
                s.harga_service,
                b.waktu_booking
            FROM tb_transaksi_service ts
            JOIN tb_booking b ON ts.id_booking = b.id_booking
            JOIN tb_service s ON b.id_service = s.id_service
            JOIN tb_user u ON b.id_user = u.id_user
            JOIN tb_pelanggan p ON u.id_user = p.id_user
            WHERE 1=1 $status_condition_service $date_condition_service
            ORDER BY ts.tgl_transaksi_service DESC
        ";

        $result_service = mysqli_query($conn, $query_service);
        while ($row = mysqli_fetch_assoc($result_service)) {
            $all_data[] = [
                'id' => 'TS-' . sprintf('%04d', $row['id_transaksi_service']),
                'tanggal' => $row['tgl_transaksi_service'],
                'jenis' => 'Service',
                'pelanggan' => $row['nama_pelanggan'],
                'item' => $row['nama_service'],
                'jumlah' => 1,
                'harga' => $row['harga_service'],
                'total' => $row['harga_service'],
                'status' => $row['status_transaksi_service']
            ];
            $total_nilai += $row['harga_service'];
            $total_transaksi++;
        }
    }

    // Sort by date
    usort($all_data, function ($a, $b) {
        return strtotime($b['tanggal']) - strtotime($a['tanggal']);
    });

    // === DATA TRANSAKSI SECTION ===
    $pdf->addSectionTitle('DATA TRANSAKSI');

    // Table dengan proporsi yang tepat untuk A4
    $pdf->SetFont('helvetica', 'B', 8);
    $pdf->SetFillColor(34, 139, 204);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetDrawColor(34, 139, 204);

    // Header tabel dengan lebar yang optimal untuk A4 (total 190mm)
    $pdf->Cell(10, 8, 'No', 1, 0, 'C', true);           // 10mm
    $pdf->Cell(22, 8, 'ID Transaksi', 1, 0, 'C', true); // 22mm
    $pdf->Cell(20, 8, 'Tanggal', 1, 0, 'C', true);      // 20mm
    $pdf->Cell(15, 8, 'Jenis', 1, 0, 'C', true);        // 15mm
    $pdf->Cell(35, 8, 'Pelanggan', 1, 0, 'C', true);    // 35mm
    $pdf->Cell(40, 8, 'Item/Service', 1, 0, 'C', true); // 40mm
    $pdf->Cell(12, 8, 'Qty', 1, 0, 'C', true);          // 12mm
    $pdf->Cell(25, 8, 'Total (Rp)', 1, 0, 'C', true);   // 25mm
    $pdf->Cell(18, 8, 'Status', 1, 1, 'C', true);       // 18mm = Total: 197mm

    // Reset untuk data
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetFont('helvetica', '', 7);

    $no = 1;
    foreach ($all_data as $data) {
        // Check page break
        if ($pdf->GetY() > 245) {
            $pdf->AddPage();

            // Re-print header
            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(34, 139, 204);
            $pdf->SetTextColor(255, 255, 255);

            $pdf->Cell(10, 8, 'No', 1, 0, 'C', true);
            $pdf->Cell(22, 8, 'ID Transaksi', 1, 0, 'C', true);
            $pdf->Cell(20, 8, 'Tanggal', 1, 0, 'C', true);
            $pdf->Cell(15, 8, 'Jenis', 1, 0, 'C', true);
            $pdf->Cell(35, 8, 'Pelanggan', 1, 0, 'C', true);
            $pdf->Cell(40, 8, 'Item/Service', 1, 0, 'C', true);
            $pdf->Cell(12, 8, 'Qty', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Total (Rp)', 1, 0, 'C', true);
            $pdf->Cell(18, 8, 'Status', 1, 1, 'C', true);

            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFont('helvetica', '', 7);
        }

        // Alternating colors untuk readability
        $fill = ($no % 2 == 0);
        if ($fill) {
            $pdf->SetFillColor(248, 249, 250);
        } else {
            $pdf->SetFillColor(255, 255, 255);
        }

        // Truncate text yang terlalu panjang
        $pelanggan = strlen($data['pelanggan']) > 22 ? substr($data['pelanggan'], 0, 19) . '...' : $data['pelanggan'];
        $item = strlen($data['item']) > 25 ? substr($data['item'], 0, 22) . '...' : $data['item'];

        $pdf->Cell(10, 7, $no, 1, 0, 'C', $fill);
        $pdf->Cell(22, 7, $data['id'], 1, 0, 'C', $fill);
        $pdf->Cell(20, 7, date('d/m/y', strtotime($data['tanggal'])), 1, 0, 'C', $fill);
        $pdf->Cell(15, 7, $data['jenis'], 1, 0, 'C', $fill);
        $pdf->Cell(35, 7, $pelanggan, 1, 0, 'L', $fill);
        $pdf->Cell(40, 7, $item, 1, 0, 'L', $fill);
        $pdf->Cell(12, 7, $data['jumlah'], 1, 0, 'C', $fill);
        $pdf->Cell(25, 7, number_format($data['total'], 0, ',', '.'), 1, 0, 'R', $fill);

        // Status dengan color coding
        $status_text = $data['status'] === 'completed' ? 'Selesai' : 'Pending';
        $status_color = $data['status'] === 'completed' ? [40, 167, 69] : [255, 193, 7];
        $pdf->SetFillColor($status_color[0], $status_color[1], $status_color[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->Cell(18, 7, $status_text, 1, 1, 'C', true);
        $pdf->SetTextColor(0, 0, 0);

        $no++;
    }

    // === RINGKASAN LAPORAN SECTION ===
    $pdf->Ln(10);
    $pdf->addSectionTitle('RINGKASAN LAPORAN');

    // Summary dalam box yang rapi
    $pdf->SetFillColor(240, 248, 255);
    $pdf->SetDrawColor(34, 139, 204);
    $pdf->Rect(10, $pdf->GetY(), 190, 35, 'FD');

    $pdf->Ln(5);

    // Total transaksi
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(20, 6, '', 0, 0); // Indent
    $pdf->Cell(50, 6, 'Total Transaksi:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->Cell(0, 6, $total_transaksi . ' transaksi', 0, 1, 'L');

    // Total nilai
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->Cell(20, 6, '', 0, 0); // Indent
    $pdf->Cell(50, 6, 'Total Nilai:', 0, 0, 'L');
    $pdf->SetFont('helvetica', 'B', 11);
    $pdf->SetTextColor(220, 53, 69);
    $pdf->Cell(0, 6, 'Rp ' . number_format($total_nilai, 0, ',', '.'), 0, 1, 'L');
    $pdf->SetTextColor(0, 0, 0);

    $pdf->Ln(3);

    // Breakdown status
    $pending_count = 0;
    $completed_count = 0;
    $pending_value = 0;
    $completed_value = 0;

    foreach ($all_data as $data) {
        if ($data['status'] === 'pending') {
            $pending_count++;
            $pending_value += $data['total'];
        } else {
            $completed_count++;
            $completed_value += $data['total'];
        }
    }

    $pdf->SetFont('helvetica', 'B', 9);
    $pdf->Cell(20, 5, '', 0, 0); // Indent
    $pdf->Cell(0, 5, 'Breakdown Status:', 0, 1, 'L');

    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(25, 5, '', 0, 0); // Indent
    $pdf->Cell(30, 5, '• Pending:', 0, 0, 'L');
    $pdf->Cell(30, 5, $pending_count . ' transaksi', 0, 0, 'L');
    $pdf->Cell(0, 5, 'Rp ' . number_format($pending_value, 0, ',', '.'), 0, 1, 'L');

    $pdf->Cell(25, 5, '', 0, 0); // Indent
    $pdf->Cell(30, 5, '• Completed:', 0, 0, 'L');
    $pdf->Cell(30, 5, $completed_count . ' transaksi', 0, 0, 'L');
    $pdf->Cell(0, 5, 'Rp ' . number_format($completed_value, 0, ',', '.'), 0, 1, 'L');

    // Add signature
    $pdf->addSignature();

    // Output dengan nama file yang descriptive
    $jenis_text = ucwords(str_replace('_', '_', $jenis_laporan));
    $filename = 'Laporan_' . $jenis_text . '_' . date('Y-m-d_H-i-s') . '.pdf';

    // Headers untuk download
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: private, max-age=0, must-revalidate');
    header('Pragma: public');

    $pdf->Output($filename, 'D');

} catch (Exception $e) {
    echo "Error generating PDF: " . $e->getMessage();
    error_log("PDF Generation Error: " . $e->getMessage());
    exit;
}
?>