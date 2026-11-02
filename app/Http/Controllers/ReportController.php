<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Client;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    /** Daftar jenis laporan. */
    public const TYPES = [
        'penjualan'            => ['label' => 'Laporan Penjualan', 'icon' => 'bi-cart-check', 'desc' => 'Rekap transaksi penjualan (invoice terbit) per periode.'],
        'invoice'              => ['label' => 'Laporan Invoice', 'icon' => 'bi-receipt-cutoff', 'desc' => 'Detail seluruh invoice beserta status pembayarannya.'],
        'pembayaran'           => ['label' => 'Laporan Pembayaran', 'icon' => 'bi-wallet2', 'desc' => 'Riwayat pembayaran diterima (Midtrans / manual / ditandai lunas).'],
        'piutang'              => ['label' => 'Piutang', 'icon' => 'bi-hourglass-split', 'desc' => 'Invoice belum dibayar dengan analisa umur piutang (aging).'],
        'overdue'              => ['label' => 'Invoice Overdue', 'icon' => 'bi-exclamation-triangle', 'desc' => 'Invoice yang melewati jatuh tempo beserta hari keterlambatan.'],
        'pendapatan-periode'   => ['label' => 'Pendapatan per Periode', 'icon' => 'bi-bar-chart-line', 'desc' => 'Pendapatan (invoice lunas) dikelompokkan per bulan.'],
        'pendapatan-customer'  => ['label' => 'Pendapatan per Customer', 'icon' => 'bi-people', 'desc' => 'Peringkat customer berdasarkan total pembayaran diterima.'],
        'pendapatan-produk'    => ['label' => 'Pendapatan per Produk/Jasa', 'icon' => 'bi-box-seam', 'desc' => 'Produk/jasa terlaris berdasarkan invoice lunas.'],
    ];

    /** Field tanggal yang dipakai filter tiap jenis laporan. */
    private const DATE_FIELD = [
        'pembayaran' => 'paid_at',
        'pendapatan-periode' => 'paid_at',
        'pendapatan-customer' => 'paid_at',
        'pendapatan-produk' => 'paid_at',
        'default' => 'created_at',
    ];

    public function index()
    {
        $this->requireAccess();
        return view('reports.index', ['types' => self::TYPES]);
    }

    public function show(Request $request, string $type)
    {
        $this->requireAccess();
        $report = $this->build($request, $type);
        return view('reports.show', compact('report'));
    }

    public function exportXlsx(Request $request, string $type)
    {
        $this->requireAccess();
        $report = $this->build($request, $type);

        ActivityLogger::log('report.export', 'Laporan', 'Export "' . $report['title'] . '" ke Excel');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(Str::limit(str_replace(['/', ' '], '-', $report['title']), 28, ''));

        $sheet->mergeCells('A1:' . self::colLetter(count($report['headers'])) . '1');
        $sheet->setCellValue('A1', strtoupper($report['title']) . ' — InvoiceFlow');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->mergeCells('A2:' . self::colLetter(count($report['headers'])) . '2');
        $sheet->setCellValue('A2', $report['period_label'] . '  |  Dicetak: ' . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setRGB('6B7280');

        $row = 4;
        foreach (array_chunk($report['summary'], 2, true) as $pair) {
            $col = 'A';
            foreach ($pair as $label => $value) {
                $sheet->setCellValue($col . $row, $label)->getStyle($col . $row)->getFont()->setBold(true);
                $sheet->setCellValue(++$col . $row, $value);
                $col++;
            }
            $row++;
        }

        $row++;
        $headerRange = 'A' . $row . ':' . self::colLetter(count($report['headers'])) . $row;
        $this->styleHeader($sheet, $headerRange);
        foreach ($report['headers'] as $i => $h) {
            $sheet->setCellValue(self::colLetter($i + 1) . $row, $h);
        }
        $row++;

        foreach ($report['rows'] as $dataRow) {
            $col = 1;
            foreach ($dataRow as $cell) {
                $sheet->setCellValue(self::colLetter($col) . $row, $cell);
                $col++;
            }
            $row++;
        }

        if (empty($report['rows'])) {
            $sheet->setCellValue('A' . $row, '— Tidak ada data pada periode ini —');
        }

        foreach (range('A', self::colLetter(count($report['headers']))) as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $filename = 'Laporan-' . Str::limit(str_replace(['/', ' '], '-', $report['title']), 40, '') . '_' . now()->format('Ymd') . '.xlsx';

        return response()->streamDownload(function () use ($spreadsheet) {
            (new Xlsx($spreadsheet))->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function exportPdf(Request $request, string $type)
    {
        $this->requireAccess();
        $report = $this->build($request, $type);

        ActivityLogger::log('report.export', 'Laporan', 'Export "' . $report['title'] . '" ke PDF');

        $pdf = Pdf::loadView('reports.pdf', compact('report'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('Laporan-' . str_replace(['/', ' '], '-', $report['title']) . '_' . now()->format('Ymd') . '.pdf');
    }

    /* ================================================================
     * Builder laporan per jenis — menghasilkan struktur generik:
     * ['title','icon','desc','period_label','summary','headers','rows']
     * ================================================================ */

    private function build(Request $request, string $type): array
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        [$from, $to] = $this->resolveRange($request);
        $dateField = self::DATE_FIELD[$type] ?? self::DATE_FIELD['default'];

        return match ($type) {
            'penjualan' => $this->penjualan($from, $to),
            'invoice' => $this->invoiceReport($from, $to, $request),
            'pembayaran' => $this->pembayaran($from, $to),
            'piutang' => $this->piutang($from, $to),
            'overdue' => $this->overdue($from, $to),
            'pendapatan-periode' => $this->pendapatanPeriode($from, $to),
            'pendapatan-customer' => $this->pendapatanCustomer($from, $to),
            'pendapatan-produk' => $this->pendapatanProduk($from, $to),
        };
    }

    private function penjualan(Carbon $from, Carbon $to): array
    {
        $invoices = $this->scopeQuery('created_at', $from, $to)->orderBy('created_at')->get();

        $monthly = $invoices->groupBy(fn ($i) => $i->created_at->format('m/Y'));

        $rows = [];
        foreach ($monthly as $month => $group) {
            $rows[] = [
                $month,
                $group->count(),
                $group->where('is_paid', true)->count(),
                'Rp ' . number_format($group->sum('total'), 0, ',', '.'),
                'Rp ' . number_format($group->where('is_paid', false)->sum('total'), 0, ',', '.'),
            ];
        }

        return [
            'title' => 'Laporan Penjualan',
            'icon' => self::TYPES['penjualan']['icon'],
            'period_label' => 'Periode: ' . $from->format('d/m/Y') . ' s/d ' . $to->format('d/m/Y'),
            'summary' => [
                'Total Invoice Terbit' => $invoices->count(),
                'Total Penjualan' => 'Rp ' . number_format($invoices->sum('total'), 0, ',', '.'),
                'Sudah Dibayar' => 'Rp ' . number_format($invoices->where('is_paid', true)->sum('total'), 0, ',', '.'),
                'Belum Tertagih' => 'Rp ' . number_format($invoices->where('is_paid', false)->sum('total'), 0, ',', '.'),
            ],
            'headers' => ['Bulan', 'Jumlah Invoice', 'Lunas', 'Total Penjualan', 'Belum Tertagih'],
            'rows' => $rows,
        ];
    }

    private function invoiceReport(Carbon $from, Carbon $to, Request $request): array
    {
        $status = $request->get('status', 'all');
        $clientId = $request->get('client_id');

        $query = $this->scopeQuery('created_at', $from, $to)->orderBy('created_at');
        $this->applyStatus($query, $status);
        if ($clientId) {
            $query->where('client_id', $clientId);
        }
        $invoices = $query->get();

        $rows = $invoices->map(fn ($i) => [
            $i->invoice_number,
            $i->created_at->format('d/m/Y'),
            $i->client_name,
            optional($i->due_date)->format('d/m/Y'),
            'Rp ' . number_format($i->subtotal, 0, ',', '.'),
            'Rp ' . number_format($i->tax_amount, 0, ',', '.'),
            'Rp ' . number_format($i->total, 0, ',', '.'),
            'BADGE:' . $i->status_color . ':' . $i->status_label,
        ])->all();

        return [
            'title' => 'Laporan Invoice',
            'icon' => self::TYPES['invoice']['icon'],
            'period_label' => 'Periode: ' . $from->format('d/m/Y') . ' s/d ' . $to->format('d/m/Y')
                . ' | Status: ' . ($status === 'all' ? 'Semua' : ucfirst($status)),
            'summary' => [
                'Jumlah Invoice' => $invoices->count(),
                'Lunas' => $invoices->where('is_paid', true)->count(),
                'Belum Dibayar' => $invoices->where('is_paid', false)->count(),
                'Overdue' => $invoices->filter(fn ($i) => !$i->is_paid && $i->isOverdue())->count(),
                'Total Tagihan' => 'Rp ' . number_format($invoices->sum('total'), 0, ',', '.'),
                'Sudah Dibayar' => 'Rp ' . number_format($invoices->where('is_paid', true)->sum('total'), 0, ',', '.'),
                'Outstanding' => 'Rp ' . number_format($invoices->where('is_paid', false)->sum('total'), 0, ',', '.'),
            ],
            'headers' => ['No. Invoice', 'Tanggal', 'Klien', 'Jatuh Tempo', 'Subtotal', 'Pajak', 'Total', 'Status'],
            'rows' => $rows,
            'filters' => ['status' => $status, 'client_id' => $clientId],
        ];
    }

    private function pembayaran(Carbon $from, Carbon $to): array
    {
        // Invoice lunas pada periode (paid_at dalam rentang)
        $invoices = $this->scopeQuery('paid_at', $from, $to)
            ->where('is_paid', true)
            ->with('payments')
            ->orderBy('paid_at')
            ->get();

        $rows = [];
        $byMethod = [];

        foreach ($invoices as $inv) {
            $payment = $inv->payments->where('status', 'success')->first();
            $method = $payment
                ? ($payment->method === 'midtrans' ? 'Midtrans (' . ($payment->payment_type ?: 'online') . ')' : 'Transfer Manual + Verifikasi')
                : 'Ditandai Lunas (Admin)';

            $byMethod[$payment?->method ?? 'admin'] = ($byMethod[$payment?->method ?? 'admin'] ?? 0) + 1;

            $rows[] = [
                optional($inv->paid_at)->format('d/m/Y H:i'),
                $inv->invoice_number,
                $inv->client_name,
                $method,
                'Rp ' . number_format($inv->total, 0, ',', '.'),
            ];
        }

        return [
            'title' => 'Laporan Pembayaran',
            'icon' => self::TYPES['pembayaran']['icon'],
            'period_label' => 'Periode pembayaran: ' . $from->format('d/m/Y') . ' s/d ' . $to->format('d/m/Y'),
            'summary' => [
                'Total Pembayaran' => count($rows),
                'Total Diterima' => 'Rp ' . number_format($invoices->sum('total'), 0, ',', '.'),
                'Via Midtrans' => $byMethod['midtrans'] ?? 0,
                'Konfirmasi Manual' => $byMethod['manual'] ?? 0,
                'Ditandai Admin' => $byMethod['admin'] ?? 0,
            ],
            'headers' => ['Tanggal Bayar', 'No. Invoice', 'Klien', 'Metode', 'Jumlah'],
            'rows' => $rows,
        ];
    }

    private function piutang(Carbon $from, Carbon $to): array
    {
        $invoices = $this->scopeQuery('created_at', $from, $to)
            ->where('is_paid', false)
            ->orderBy('due_date')
            ->get();

        $aging = ['Belum jatuh tempo' => 0, '1–30 hari' => 0, '31–60 hari' => 0, '61–90 hari' => 0, '> 90 hari' => 0];
        $rows = [];

        foreach ($invoices as $i) {
            $days = $i->due_date ? $i->due_date->diffInDays(now()) : 0;
            $late = $i->isOverdue() ? $days : 0;

            $bucket = !$i->isOverdue() ? 'Belum jatuh tempo'
                : ($late <= 30 ? '1–30 hari' : ($late <= 60 ? '31–60 hari' : ($late <= 90 ? '61–90 hari' : '> 90 hari')));
            $aging[$bucket] += $i->total;

            $rows[] = [
                $i->invoice_number,
                $i->client_name,
                $i->created_at->format('d/m/Y'),
                optional($i->due_date)->format('d/m/Y'),
                $i->isOverdue() ? $late . ' hari' : '-',
                'BADGE:' . $i->status_color . ':' . $i->status_label,
                'Rp ' . number_format($i->total, 0, ',', '.'),
            ];
        }

        return [
            'title' => 'Laporan Piutang',
            'icon' => self::TYPES['piutang']['icon'],
            'period_label' => 'Invoice terbit: ' . $from->format('d/m/Y') . ' s/d ' . $to->format('d/m/Y') . ' (posisi per hari ini)',
            'summary' => [
                'Total Piutang' => 'Rp ' . number_format($invoices->sum('total'), 0, ',', '.'),
                'Jumlah Invoice' => $invoices->count(),
                'Aging Belum Jatuh Tempo' => 'Rp ' . number_format($aging['Belum jatuh tempo'], 0, ',', '.'),
                'Aging 1–30 hari' => 'Rp ' . number_format($aging['1–30 hari'], 0, ',', '.'),
                'Aging 31–60 hari' => 'Rp ' . number_format($aging['31–60 hari'], 0, ',', '.'),
                'Aging 61–90 hari' => 'Rp ' . number_format($aging['61–90 hari'], 0, ',', '.'),
                'Aging > 90 hari' => 'Rp ' . number_format($aging['> 90 hari'], 0, ',', '.'),
            ],
            'headers' => ['No. Invoice', 'Klien', 'Tanggal', 'Jatuh Tempo', 'Terlambat', 'Status', 'Jumlah'],
            'rows' => $rows,
        ];
    }

    private function overdue(Carbon $from, Carbon $to): array
    {
        $invoices = $this->scopeQuery('created_at', $from, $to)
            ->unpaid()->overdue()
            ->orderBy('due_date')
            ->get();

        $rows = $invoices->map(fn ($i) => [
            $i->invoice_number,
            $i->client_name,
            optional($i->due_date)->format('d/m/Y'),
            $i->due_date->diffInDays(now()) . ' hari',
            'Rp ' . number_format($i->total, 0, ',', '.'),
            $i->client_phone ?: '-',
        ])->all();

        $avgLate = $invoices->count()
            ? round($invoices->average(fn ($i) => $i->due_date->diffInDays(now())))
            : 0;

        return [
            'title' => 'Invoice Overdue',
            'icon' => self::TYPES['overdue']['icon'],
            'period_label' => 'Periode terbit: ' . $from->format('d/m/Y') . ' s/d ' . $to->format('d/m/Y'),
            'summary' => [
                'Jumlah Overdue' => $invoices->count(),
                'Total Nilai' => 'Rp ' . number_format($invoices->sum('total'), 0, ',', '.'),
                'Rata-rata Terlambat' => $avgLate . ' hari',
                'Klien Terdampak' => $invoices->unique('client_name')->count(),
            ],
            'headers' => ['No. Invoice', 'Klien', 'Jatuh Tempo', 'Terlambat', 'Jumlah', 'Telp Klien'],
            'rows' => $rows,
        ];
    }

    private function pendapatanPeriode(Carbon $from, Carbon $to): array
    {
        $invoices = $this->scopeQuery('paid_at', $from, $to)
            ->where('is_paid', true)
            ->orderBy('paid_at')
            ->get();

        $grouped = $invoices->groupBy(fn ($i) => $i->paid_at?->format('m/Y'));

        $rows = [];
        $current = $from->copy()->startOfMonth();
        while ($current <= $to) {
            $key = $current->format('m/Y');
            $group = $grouped[$key] ?? collect();
            $rows[] = [
                $key,
                $group->count(),
                'Rp ' . number_format($group->sum('total'), 0, ',', '.'),
                $invoices->count() ? round($group->sum('total') / max(1, $invoices->sum('total')) * 100, 1) . '%' : '0%',
            ];
            $current->addMonth();
        }

        return [
            'title' => 'Pendapatan per Periode',
            'icon' => self::TYPES['pendapatan-periode']['icon'],
            'period_label' => 'Pendapatan diterima: ' . $from->format('d/m/Y') . ' s/d ' . $to->format('d/m/Y'),
            'summary' => [
                'Total Pendapatan' => 'Rp ' . number_format($invoices->sum('total'), 0, ',', '.'),
                'Jumlah Invoice Lunas' => $invoices->count(),
                'Rata-rata per Bulan' => 'Rp ' . number_format($grouped->count() ? $invoices->sum('total') / max(1, $grouped->count()) : 0, 0, ',', '.'),
                'Bulan Terbaik' => $grouped->map(fn ($g) => $g->sum('total'))->sortDesc()->keys()->first() ?: '-',
            ],
            'headers' => ['Bulan', 'Invoice Lunas', 'Pendapatan', 'Kontribusi'],
            'rows' => $rows,
        ];
    }

    private function pendapatanCustomer(Carbon $from, Carbon $to): array
    {
        $invoices = $this->scopeQuery('paid_at', $from, $to)
            ->where('is_paid', true)
            ->orderBy('paid_at')
            ->get();

        $grouped = $invoices->groupBy('client_name');

        $rows = $grouped->map(fn ($g, $name) => [
            $name,
            $g->count(),
            'Rp ' . number_format($g->sum('total'), 0, ',', '.'),
            optional($g->sortByDesc('paid_at')->first()->paid_at)->format('d/m/Y') ?: '-',
            $invoices->sum('total') > 0 ? round($g->sum('total') / $invoices->sum('total') * 100, 1) . '%' : '0%',
        ])->sortByDesc(fn ($r) => (int) filter_var($r[2], FILTER_SANITIZE_NUMBER_INT))->values()->all();

        return [
            'title' => 'Pendapatan per Customer',
            'icon' => self::TYPES['pendapatan-customer']['icon'],
            'period_label' => 'Pendapatan diterima: ' . $from->format('d/m/Y') . ' s/d ' . $to->format('d/m/Y'),
            'summary' => [
                'Total Pendapatan' => 'Rp ' . number_format($invoices->sum('total'), 0, ',', '.'),
                'Jumlah Customer' => $grouped->count(),
                'Customer Teratas' => $rows[0][0] ?? '-',
            ],
            'headers' => ['Customer', 'Invoice Lunas', 'Total Dibayar', 'Terakhir Bayar', 'Kontribusi'],
            'rows' => $rows,
        ];
    }

    private function pendapatanProduk(Carbon $from, Carbon $to): array
    {
        $invoiceIds = $this->scopeQuery('paid_at', $from, $to)
            ->where('is_paid', true)
            ->pluck('id');

        $items = \App\Models\InvoiceItem::whereIn('invoice_id', $invoiceIds)
            ->get()
            ->groupBy(fn ($it) => $it->description ?: '(Tanpa deskripsi)');

        $rows = $items->map(fn ($g, $name) => [
            $name,
            $g->sum('quantity'),
            'Rp ' . number_format($g->avg('unit_price'), 0, ',', '.'),
            'Rp ' . number_format($g->sum('amount'), 0, ',', '.'),
        ])->sortByDesc(fn ($r) => (int) filter_var($r[3], FILTER_SANITIZE_NUMBER_INT))->values()->all();

        return [
            'title' => 'Pendapatan per Produk/Jasa',
            'icon' => self::TYPES['pendapatan-produk']['icon'],
            'period_label' => 'Berdasarkan invoice lunas: ' . $from->format('d/m/Y') . ' s/d ' . $to->format('d/m/Y'),
            'summary' => [
                'Jumlah Produk/Jasa' => $items->count(),
                'Total Unit Terjual' => $items->flatten()->sum('quantity'),
                'Total Pendapatan' => 'Rp ' . number_format($items->flatten()->sum('amount'), 0, ',', '.'),
            ],
            'headers' => ['Produk/Jasa', 'Qty Terjual', 'Harga Rata-rata', 'Total Pendapatan'],
            'rows' => $rows,
        ];
    }

    /* ================= helpers ================= */

    private function scopeQuery(string $dateField, Carbon $from, Carbon $to)
    {
        $user = auth()->user();
        $query = Invoice::query();

        if (!($user?->isAdmin() || $user?->isFinance())) {
            $query->where('user_id', $user->id);
        }

        return $query->whereBetween($dateField, [$from->copy()->startOfDay(), $to->copy()->endOfDay()]);
    }

    private function applyStatus($query, string $status): void
    {
        switch ($status) {
            case 'paid': $query->paid(); break;
            case 'unpaid': $query->unpaid(); break;
            case 'overdue': $query->overdue(); break;
        }
    }

    private function resolveRange(Request $request): array
    {
        $from = $request->get('from') ? Carbon::parse($request->get('from')) : now()->startOfMonth();
        $to = $request->get('to') ? Carbon::parse($request->get('to')) : now()->endOfMonth();
        if ($to->lt($from)) {
            [$from, $to] = [$to, $from];
        }
        return [$from, $to];
    }

    private function styleHeader($sheet, string $range): void
    {
        $sheet->getStyle($range)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'E5E7EB']]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }

    private function requireAccess(): void
    {
        abort_unless(auth()->user()?->canViewReports(), 403, 'Akses laporan: Admin & Finance.');
    }
    /** Kolom Excel dari angka (1=A). */
    private static function colLetter(int $n): string
    {
        $letter = '';
        while ($n > 0) {
            $mod = ($n - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $n = intdiv($n - $mod - 1, 26);
        }
        return $letter ?: 'A';
    }
}
