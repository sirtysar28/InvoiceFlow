<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class SalesOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->requireAccess();

        $query = SalesOrder::query();
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('order_number', 'like', "%{$q}%")
                    ->orWhere('client_name', 'like', "%{$q}%");
            });
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('orders.index', compact('orders'));
    }

    public function create()
    {
        $this->requireAccess();

        $clients = $this->getClients();
        $quotations = Quotation::where('status', 'accepted')
            ->when(!auth()->user()->isAdmin(), fn ($q) => $q->where('user_id', auth()->id()))
            ->latest()->get();

        $nextNumber = $this->nextNumber();

        return view('orders.create', compact('clients', 'quotations', 'nextNumber'));
    }

    public function store(Request $request)
    {
        $this->requireAccess();

        $validated = $this->validated($request);
        $validated['user_id'] = auth()->id();
        $validated['order_date'] = $validated['order_date'] ?? today();

        // Dari quotation? tandai converted & link
        if (!empty($validated['quotation_id'])) {
            $quotation = Quotation::find($validated['quotation_id']);
            if ($quotation) {
                $quotation->update(['status' => 'converted']);
            }
        }

        $order = SalesOrder::create($validated);

        ActivityLogger::log('order.create', 'Sales Order', 'Membuat order ' . $order->order_number . ' untuk ' . $order->client_name);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Sales Order berhasil dibuat! 🎉');
    }

    public function show(SalesOrder $order)
    {
        $this->requireAccess();
        $this->authorizeOrder($order);

        return view('orders.show', compact('order'));
    }

    public function edit(SalesOrder $order)
    {
        $this->requireAccess();
        $this->authorizeOrder($order);

        if ($order->status === 'invoiced') {
            return back()->with('error', 'Order yang sudah di-invoice tidak bisa diubah.');
        }

        $clients = $this->getClients();

        return view('orders.edit', compact('order', 'clients'));
    }

    public function update(Request $request, SalesOrder $order)
    {
        $this->requireAccess();
        $this->authorizeOrder($order);

        $order->update($this->validated($request));

        ActivityLogger::log('order.update', 'Sales Order', 'Memperbarui order ' . $order->order_number);

        return redirect()->route('orders.show', $order)
            ->with('success', 'Sales Order berhasil diperbarui!');
    }

    public function destroy(SalesOrder $order)
    {
        $this->requireAccess();
        $this->authorizeOrder($order);

        $order->delete();

        ActivityLogger::log('order.delete', 'Sales Order', 'Menghapus order ' . $order->order_number);

        return redirect()->route('orders.index')->with('success', 'Sales Order berhasil dihapus!');
    }

    /** Ubah status order. */
    public function markStatus(Request $request, SalesOrder $order)
    {
        $this->requireAccess();
        $this->authorizeOrder($order);

        $validated = $request->validate(['status' => 'required|in:new,processing,completed,cancelled']);

        $order->update(['status' => $validated['status']]);

        ActivityLogger::log('order.' . $validated['status'], 'Sales Order', 'Order ' . $order->order_number . ' → ' . $validated['status']);

        return back()->with('success', 'Status order diperbarui!');
    }

    /** Konversi order → Invoice. */
    public function convertToInvoice(SalesOrder $order)
    {
        $this->requireAccess();
        $this->authorizeOrder($order);

        if ($order->status === 'invoiced' || $order->invoice_id) {
            return back()->with('error', 'Order ini sudah menjadi invoice.');
        }

        $dueDays = (int) (\App\Models\SystemSetting::get('default_payment_due_days', 14));

        $invoice = Invoice::create([
            'user_id'        => $order->user_id,
            'client_id'      => $order->client_id,
            'invoice_number' => 'INV/' . date('Ym') . '/' . str_pad(Invoice::whereYear('created_at', date('Y'))->whereMonth('created_at', date('m'))->count() + 1, 3, '0', STR_PAD_LEFT),
            'client_name'    => $order->client_name,
            'client_email'   => $order->client_email ?: 'tidak-ada@email.com',
            'client_phone'   => $order->client_phone,
            'description'    => $order->items[0]['description'] ?? 'Order ' . $order->order_number,
            'quantity'       => $order->items[0]['quantity'] ?? 1,
            'unit_price'     => $order->items[0]['unit_price'] ?? $order->total,
            'subtotal'       => $order->subtotal,
            'discount_amount'=> $order->discount_amount,
            'tax_amount'     => $order->tax_amount,
            'total'          => $order->total,
            'due_date'       => now()->addDays($dueDays),
            'notes'          => 'Dari order ' . $order->order_number,
            'status'         => 'unpaid',
        ]);

        foreach ($order->items as $item) {
            InvoiceItem::create([
                'invoice_id'  => $invoice->id,
                'description' => $item['description'] ?? 'Item',
                'quantity'    => (int) ($item['quantity'] ?? 1),
                'unit_price'  => (float) ($item['unit_price'] ?? 0),
                'amount'      => (int) ($item['quantity'] ?? 1) * (float) ($item['unit_price'] ?? 0),
            ]);
        }

        $order->update(['status' => 'invoiced', 'invoice_id' => $invoice->id]);

        ActivityLogger::log('order.convert-invoice', 'Sales Order',
            'Konversi ' . $order->order_number . ' → Invoice ' . $invoice->invoice_number);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice berhasil dibuat dari order! ✅');
    }

    /* ================= helpers ================= */

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'order_number' => 'required|string|max:50|unique:sales_orders,order_number' . ($request->route('order') ? ',' . $request->route('order')->id : ''),
            'quotation_id' => 'nullable|exists:quotations,id',
            'client_id' => 'nullable|exists:clients,id',
            'client_name' => 'required|string|max:255',
            'client_email' => 'nullable|email|max:255',
            'client_phone' => 'nullable|string|max:30',
            'items' => 'required|array|min:1',
            'items.*.description' => 'nullable|string|max:500',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'order_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $items = $validated['items'];
        $subtotal = collect($items)->sum(fn ($i) => (int) $i['quantity'] * (float) $i['unit_price']);

        $validated['items'] = $items;
        $validated['subtotal'] = $subtotal;
        $validated['discount_amount'] = 0;
        $validated['tax_amount'] = 0;
        $validated['total'] = $subtotal;

        return $validated;
    }

    private function nextNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $count = SalesOrder::whereYear('created_at', $year)->whereMonth('created_at', $month)->count();
        return "SO/{$year}{$month}/" . str_pad($count + 1, 3, '0', STR_PAD_LEFT);
    }

    private function getClients()
    {
        $query = Client::orderBy('name');
        if (!auth()->user()->isAdmin()) {
            $query->where('user_id', auth()->id());
        }
        return $query->get(['id', 'name', 'email', 'phone', 'company']);
    }

    private function authorizeOrder(SalesOrder $order): void
    {
        if (!auth()->user()->isAdmin() && $order->user_id !== auth()->id()) {
            abort(403);
        }
    }

    private function requireAccess(): void
    {
        abort_unless(auth()->user()?->canManageOrders(), 403, 'Akses: Admin & Sales.');
    }
}
