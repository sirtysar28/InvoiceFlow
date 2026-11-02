<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\SalesOrder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $this->requireAccess();

        $query = Quotation::query();
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('quote_number', 'like', "%{$q}%")
                    ->orWhere('client_name', 'like', "%{$q}%");
            });
        }

        $quotations = $query->latest()->paginate(15)->withQueryString();

        return view('quotations.index', compact('quotations'));
    }

    public function create()
    {
        $this->requireAccess();

        $clients = $this->getClients();
        $nextNumber = $this->nextNumber('QUO');

        return view('quotations.create', compact('clients', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $this->requireAccess();

        $validated = $this->validated($request);
        $validated['user_id'] = auth()->id();

        $quotation = Quotation::create($validated);

        ActivityLogger::log('quotation.create', 'Quotation', 'Membuat penawaran ' . $quotation->quote_number . ' untuk ' . $quotation->client_name);

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Penawaran berhasil dibuat! 🎉');
    }

    public function show(Quotation $quotation)
    {
        $this->requireAccess();
        $this->authorizeQuotation($quotation);

        return view('quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        $this->requireAccess();
        $this->authorizeQuotation($quotation);

        if (in_array($quotation->status, ['accepted', 'converted'])) {
            return back()->with('error', 'Penawaran yang sudah disetujui tidak bisa diubah.');
        }

        $clients = $this->getClients();

        return view('quotations.edit', compact('quotation', 'clients'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $this->requireAccess();
        $this->authorizeQuotation($quotation);

        $quotation->update($this->validated($request));

        ActivityLogger::log('quotation.update', 'Quotation', 'Memperbarui penawaran ' . $quotation->quote_number);

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Penawaran berhasil diperbarui!');
    }

    public function destroy(Quotation $quotation)
    {
        $this->requireAccess();
        $this->authorizeQuotation($quotation);

        $quotation->delete();

        ActivityLogger::log('quotation.delete', 'Quotation', 'Menghapus penawaran ' . $quotation->quote_number);

        return redirect()->route('quotations.index')->with('success', 'Penawaran berhasil dihapus!');
    }

    /** Tandai penawaran terkirim / disetujui / ditolak. */
    public function markStatus(Request $request, Quotation $quotation)
    {
        $this->requireAccess();
        $this->authorizeQuotation($quotation);

        $validated = $request->validate(['status' => 'required|in:draft,sent,accepted,rejected']);

        $quotation->update(['status' => $validated['status']]);

        ActivityLogger::log('quotation.' . $validated['status'], 'Quotation',
            'Penawaran ' . $quotation->quote_number . ' → ' . $validated['status']);

        return back()->with('success', 'Status penawaran diperbarui!');
    }

    /** Konversi penawaran → Sales Order. */
    public function convertToOrder(Quotation $quotation)
    {
        $this->requireAccess();
        $this->authorizeQuotation($quotation);

        if ($quotation->status === 'converted') {
            return back()->with('error', 'Penawaran ini sudah dikonversi.');
        }

        $order = SalesOrder::create([
            'user_id'        => $quotation->user_id,
            'order_number'   => $this->nextNumber('SO'),
            'quotation_id'   => $quotation->id,
            'client_id'      => $quotation->client_id,
            'client_name'    => $quotation->client_name,
            'client_email'   => $quotation->client_email,
            'client_phone'   => $quotation->client_phone,
            'items'          => $quotation->items,
            'subtotal'       => $quotation->subtotal,
            'discount_amount'=> $quotation->discount_amount,
            'tax_amount'     => $quotation->tax_amount,
            'total'          => $quotation->total,
            'order_date'     => today(),
            'status'         => 'new',
            'notes'          => 'Dari penawaran ' . $quotation->quote_number,
        ]);

        $quotation->update(['status' => 'converted']);

        ActivityLogger::log('quotation.convert-order', 'Quotation',
            'Konversi ' . $quotation->quote_number . ' → Sales Order ' . $order->order_number);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Sales Order berhasil dibuat dari penawaran! ✅');
    }

    /** Konversi penawaran → Invoice langsung. */
    public function convertToInvoice(Quotation $quotation)
    {
        $this->requireAccess();
        $this->authorizeQuotation($quotation);

        if ($quotation->status === 'converted') {
            return back()->with('error', 'Penawaran ini sudah dikonversi.');
        }

        $dueDays = (int) (\App\Models\SystemSetting::get('default_payment_due_days', 14));

        $invoice = Invoice::create([
            'user_id'         => $quotation->user_id,
            'client_id'       => $quotation->client_id,
            'invoice_number'  => $this->nextNumber('INV'),
            'client_name'     => $quotation->client_name,
            'client_email'    => $quotation->client_email ?: 'tidak-ada@email.com',
            'client_phone'    => $quotation->client_phone,
            'description'     => $quotation->items[0]['description'] ?? 'Penawaran ' . $quotation->quote_number,
            'quantity'        => $quotation->items[0]['quantity'] ?? 1,
            'unit_price'      => $quotation->items[0]['unit_price'] ?? $quotation->total,
            'subtotal'        => $quotation->subtotal,
            'discount'        => $quotation->discount,
            'discount_amount' => $quotation->discount_amount,
            'tax'             => $quotation->tax,
            'tax_amount'      => $quotation->tax_amount,
            'total'           => $quotation->total,
            'due_date'        => now()->addDays($dueDays),
            'notes'           => 'Dari penawaran ' . $quotation->quote_number,
            'status'          => 'unpaid',
        ]);

        foreach ($quotation->items as $item) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $item['description'] ?? 'Item',
                'quantity'    => (int) ($item['quantity'] ?? 1),
                'unit_price'  => (float) ($item['unit_price'] ?? 0),
                'amount'      => (int) ($item['quantity'] ?? 1) * (float) ($item['unit_price'] ?? 0),
            ]);
        }

        $quotation->update(['status' => 'converted']);

        ActivityLogger::log('quotation.convert-invoice', 'Quotation',
            'Konversi ' . $quotation->quote_number . ' → Invoice ' . $invoice->invoice_number);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice berhasil dibuat dari penawaran! ✅');
    }

    /* ================= helpers ================= */

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'quote_number' => 'required|string|max:50|unique:quotations,quote_number' . ($request->route('quotation') ? ',' . $request->route('quotation')->id : ''),
            'client_id' => 'nullable|exists:clients,id',
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:30',
            'items' => 'required|array|min:1',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'tax' => 'nullable|numeric|min:0|max:100',
            'valid_until' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Hitung totals
        $items = $validated['items'];
        $subtotal = collect($items)->sum(fn ($i) => (int) $i['quantity'] * (float) $i['unit_price']);
        $discount = (float) ($validated['discount'] ?? 0);
        $discountAmount = $subtotal * ($discount / 100);
        $tax = (float) ($validated['tax'] ?? 0);
        $taxAmount = $subtotal * ($tax / 100);

        $validated['subtotal'] = $subtotal;
        $validated['discount'] = $discount;
        $validated['discount_amount'] = $discountAmount;
        $validated['tax'] = $tax;
        $validated['tax_amount'] = $taxAmount;
        $validated['total'] = $subtotal - $discountAmount + $taxAmount;

        return $validated;
    }

    private function nextNumber(string $prefix): string
    {
        $year = date('Y');
        $month = date('m');
        $model = $prefix === 'QUO' ? Quotation::class : SalesOrder::class;
        $count = $model::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
        return "{$prefix}/{$year}{$month}/" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    private function getClients()
    {
        $query = Client::orderBy('name');
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }
        return $query->get(['id', 'name', 'email', 'phone', 'company']);
    }

    private function authorizeQuotation(Quotation $quotation): void
    {
        if (!auth()->user()->isAdmin() && $quotation->user_id !== auth()->id()) {
            abort(403);
        }
    }

    private function requireAccess(): void
    {
        abort_unless(auth()->user()?->canManageQuotations(), 403, 'Akses: Admin & Sales.');
    }
}
