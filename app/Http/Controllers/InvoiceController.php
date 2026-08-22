<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Models\BusinessProfile;
use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceSendToClient;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Invoice::query();

        // Klien hanya lihat invoice dengan email miliknya
        if ($user?->isKlien()) {
            $query->where('client_email', $user->email);
        }
        // Karyawan hanya lihat invoice buatannya sendiri
        elseif (!$user?->isAdmin()) {
            $query->where('user_id', $user->id);
        }
        // Admin lihat semua (no filter)

        // Filter
        if ($request->has('status')) {
            switch ($request->status) {
                case 'unpaid':
                    $query->unpaid();
                    break;
                case 'paid':
                    $query->paid();
                    break;
                case 'overdue':
                    $query->overdue();
                    break;
                case 'today':
                    $query->where('is_paid', false)->whereDate('due_date', now());
                    break;
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('client_name', 'like', "%{$search}%")
                  ->orWhere('client_email', 'like', "%{$search}%");
            });
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        // Summary counts
        $countBase = Invoice::query();
        if ($user?->isKlien()) {
            $countBase->where('client_email', $user->email);
        } elseif (!$user?->isAdmin()) {
            $countBase->where('user_id', $user->id);
        }

        $counts = [
            'all' => (clone $countBase)->count(),
            'unpaid' => (clone $countBase)->unpaid()->count(),
            'paid' => (clone $countBase)->paid()->count(),
            'overdue' => (clone $countBase)->overdue()->count(),
            'today' => (clone $countBase)
                ->where('is_paid', false)
                ->whereDate('due_date', now())->count(),
        ];

        return view('invoices.index', compact('invoices', 'counts'));
    }

    public function create(Request $request)
    {
        $this->forbidKlien();
        $nextNumber = $this->generateInvoiceNumber();
        $clients = $this->getUserClients();
        $selectedClientId = $request->get('client_id');

        // Pre-fill client data if client_id provided
        $prefillClient = null;
        if ($selectedClientId) {
            $prefillClient = Client::find($selectedClientId);
            if ($prefillClient && !auth()->user()?->isAdmin() && $prefillClient->user_id !== auth()->id()) {
                $prefillClient = null;
            }
        }

        return view('invoices.create', compact('nextNumber', 'clients', 'selectedClientId', 'prefillClient'));
    }

    public function store(Request $request)
    {
        $this->forbidKlien();
        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'invoice_number' => 'required|string|max:50|unique:invoices',
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0|max:100',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Calculate totals from items
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += (int) $item['quantity'] * (float) $item['unit_price'];
        }

        $taxPercent = $validated['tax'] ? (float) $validated['tax'] : 0;
        $taxAmount = $subtotal * ($taxPercent / 100);

        $discountPercent = $validated['discount'] ? (float) $validated['discount'] : 0;
        $discountAmount = $subtotal * ($discountPercent / 100);

        $total = $subtotal + $taxAmount - $discountAmount;

        // Backward compatibility: store first item's description on invoice
        $firstItem = $validated['items'][0];

        $invoice = Invoice::create([
            'user_id' => auth()->id(),
            'client_id' => $validated['client_id'] ?? null,
            'invoice_number' => $validated['invoice_number'],
            'client_name' => $validated['client_name'],
            'client_email' => $validated['client_email'],
            'client_phone' => $validated['client_phone'] ?? null,
            'description' => $firstItem['description'] ?? 'Layanan',
            'quantity' => (int) $firstItem['quantity'],
            'unit_price' => (float) $firstItem['unit_price'],
            'subtotal' => $subtotal,
            'tax' => $validated['tax'] ?? null,
            'tax_amount' => $taxAmount,
            'discount' => $validated['discount'] ?? null,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'unpaid',
        ]);

        // Create all line items
        foreach ($validated['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'] ?? 'Layanan',
                'quantity' => (int) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
                'amount' => (int) $item['quantity'] * (float) $item['unit_price'],
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice berhasil dibuat!');
    }

    public function show(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);
        $invoice->load('items');
        $businessProfile = BusinessProfile::where('user_id', auth()->id())->first();
        $waTemplate = MessageTemplate::where('user_id', auth()->id())
            ->where('type', 'whatsapp')->first();
        $emailTemplate = MessageTemplate::where('user_id', auth()->id())
            ->where('type', 'email')->first();

        return view('invoices.show', compact('invoice', 'businessProfile', 'waTemplate', 'emailTemplate'));
    }

    public function edit(Invoice $invoice)
    {
        $this->forbidKlien();
        $this->authorizeInvoice($invoice);
        $invoice->load('items');
        $clients = $this->getUserClients();
        return view('invoices.edit', compact('invoice', 'clients'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $this->forbidKlien();
        $this->authorizeInvoice($invoice);

        $validated = $request->validate([
            'client_id' => 'nullable|exists:clients,id',
            'invoice_number' => 'required|string|max:50|unique:invoices,invoice_number,' . $invoice->id,
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'client_phone' => 'nullable|string|max:20',
            'items' => 'required|array|min:1',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'tax' => 'nullable|numeric|min:0|max:100',
            'discount' => 'nullable|numeric|min:0|max:100',
            'due_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        // Calculate totals from items
        $subtotal = 0;
        foreach ($validated['items'] as $item) {
            $subtotal += (int) $item['quantity'] * (float) $item['unit_price'];
        }

        $taxPercent = $validated['tax'] ? (float) $validated['tax'] : 0;
        $taxAmount = $subtotal * ($taxPercent / 100);

        $discountPercent = $validated['discount'] ? (float) $validated['discount'] : 0;
        $discountAmount = $subtotal * ($discountPercent / 100);

        $total = $subtotal + $taxAmount - $discountAmount;

        $firstItem = $validated['items'][0];

        $invoice->update([
            'client_id' => $validated['client_id'] ?? null,
            'invoice_number' => $validated['invoice_number'],
            'client_name' => $validated['client_name'],
            'client_email' => $validated['client_email'],
            'client_phone' => $validated['client_phone'] ?? null,
            'description' => $firstItem['description'] ?? 'Layanan',
            'quantity' => (int) $firstItem['quantity'],
            'unit_price' => (float) $firstItem['unit_price'],
            'subtotal' => $subtotal,
            'tax' => $validated['tax'] ?? null,
            'tax_amount' => $taxAmount,
            'discount' => $validated['discount'] ?? null,
            'discount_amount' => $discountAmount,
            'total' => $total,
            'due_date' => $validated['due_date'],
            'notes' => $validated['notes'] ?? null,
        ]);

        // Replace all line items
        $invoice->items()->delete();
        foreach ($validated['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'description' => $item['description'] ?? 'Layanan',
                'quantity' => (int) $item['quantity'],
                'unit_price' => (float) $item['unit_price'],
                'amount' => (int) $item['quantity'] * (float) $item['unit_price'],
            ]);
        }

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice berhasil diperbarui!');
    }

    public function destroy(Invoice $invoice)
    {
        $this->forbidKlien();
        $this->authorizeInvoice($invoice);

        // Karyawan & klien tidak bisa hapus
        if (!auth()->user()?->canDelete()) {
            abort(403, 'Anda tidak memiliki izin untuk menghapus invoice.');
        }

        $invoice->delete();
        return redirect()->route('invoices.index')
            ->with('success', 'Invoice berhasil dihapus!');
    }

    public function markAsPaid(Invoice $invoice)
    {
        $this->forbidKlien();
        $this->authorizeInvoice($invoice);
        $invoice->markAsPaid();
        return back()->with('success', 'Invoice berhasil ditandai lunas! ✅');
    }

    public function markAsUnpaid(Invoice $invoice)
    {
        $this->forbidKlien();
        $this->authorizeInvoice($invoice);
        $invoice->is_paid = false;
        $invoice->status = 'unpaid';
        $invoice->paid_at = null;
        $invoice->save();
        return back()->with('success', 'Status invoice dikembalikan ke belum lunas.');
    }

    public function downloadPdf(Invoice $invoice)
    {
        $this->authorizeInvoice($invoice);
        $invoice->load('items');
        $businessProfile = BusinessProfile::where('user_id', auth()->id())->first();

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'businessProfile'));
        return $pdf->download("Invoice-{$invoice->invoice_number}.pdf");
    }

    public function sendWhatsApp(Invoice $invoice)
    {
        $this->forbidKlien();
        $this->authorizeInvoice($invoice);
        $invoice->markAsSent();

        $businessProfile = BusinessProfile::where('user_id', auth()->id())->first();
        $template = MessageTemplate::where('user_id', auth()->id())
            ->where('type', 'whatsapp')->first();

        $message = $template
            ? $template->getParsedBody($invoice)
            : $this->getDefaultWaMessage($invoice, $businessProfile);

        $phone = preg_replace('/[^0-9]/', '', $invoice->client_phone);
        $waUrl = "https://wa.me/{$phone}?text=" . urlencode($message);

        return redirect()->away($waUrl);
    }

    public function sendEmail(Request $request, Invoice $invoice)
    {
        $this->forbidKlien();
        $this->authorizeInvoice($invoice);

        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $invoice->markAsSent();
        $invoice->load('items');
        $businessProfile = BusinessProfile::where('user_id', auth()->id())->first();

        Mail::to($invoice->client_email)->send(
            new InvoiceSendToClient($invoice, $businessProfile, $request->subject, $request->message)
        );

        return back()->with('success', 'Email berhasil dikirim ke ' . $invoice->client_email);
    }

    public function publicView($id)
    {
        $invoice = Invoice::findOrFail($id);
        $businessProfile = BusinessProfile::where('user_id', $invoice->user_id)->first();
        return view('invoices.public', compact('invoice', 'businessProfile'));
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = date('m');
        $count = Invoice::where('user_id', auth()->id())
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();
        $sequence = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
        return "{$prefix}/{$year}{$month}/{$sequence}";
    }

    private function getUserClients()
    {
        $isAdmin = auth()->user()?->isAdmin();
        $query = Client::orderBy('name');

        if (!$isAdmin) {
            $query->where('user_id', auth()->id());
        }

        return $query->get(['id', 'name', 'email', 'phone', 'company']);
    }

    private function authorizeInvoice(Invoice $invoice): void
    {
        $user = auth()->user();

        // Admin bisa akses semua invoice
        if ($user?->isAdmin()) {
            return;
        }

        // Karyawan bisa akses invoice miliknya sendiri
        if ($user?->isKaryawan()) {
            if ($invoice->user_id !== $user->id) {
                abort(403, 'Unauthorized');
            }
            return;
        }

        // Klien hanya bisa lihat invoice yang terkait dengan emailnya
        if ($user?->isKlien()) {
            if ($invoice->client_email !== $user->email) {
                abort(403, 'Unauthorized');
            }
            return;
        }

        // Fallback: user biasa cuma bisa akses invoice miliknya sendiri
        if ($invoice->user_id !== $user?->id) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * Cegah klien dari aksi create/edit/delete
     */
    private function forbidKlien(): void
    {
        if (auth()->user()?->isKlien()) {
            abort(403, 'Klien hanya dapat melihat invoice.');
        }
    }

    private function getDefaultWaMessage(Invoice $invoice, $profile): string
    {
        $msg = "Halo {$invoice->client_name},\n\n";
        $msg .= "Berikut tagihan invoice Anda:\n";
        $msg .= "No. Invoice: {$invoice->invoice_number}\n";
        $msg .= "Total: Rp " . number_format($invoice->total, 0, ',', '.') . "\n";
        $msg .= "Jatuh Tempo: " . $invoice->due_date->format('d/m/Y') . "\n\n";

        if ($profile && $profile->bank_account_number) {
            $msg .= "Pembayaran ke rekening:\n";
            $msg .= "Bank: {$profile->bank_name}\n";
            $msg .= "No. Rek: {$profile->bank_account_number}\n";
            $msg .= "A.n: {$profile->bank_account_name}\n\n";
        }

        $msg .= "Link Invoice: " . route('invoices.public', $invoice->id) . "\n\n";
        $msg .= "Terima kasih.";

        return $msg;
    }
}
