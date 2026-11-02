<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $this->requireAccess();

        $query = Product::where('user_id', auth()->id())->latest();

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%");
            });
        }

        $products = $query->paginate(20)->withQueryString();

        return view('products.index', compact('products'));
    }

    public function store(Request $request)
    {
        $this->requireAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'unit' => 'nullable|string|max:30',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ], [
            'name.required' => 'Nama produk/jasa wajib diisi.',
            'price.required' => 'Harga wajib diisi.',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['unit'] = $validated['unit'] ?: 'pcs';

        Product::create($validated);

        ActivityLogger::log('product.create', 'Produk/Jasa', 'Menambahkan produk "' . $validated['name'] . '"');

        return back()->with('success', 'Produk/jasa berhasil ditambahkan! ✅');
    }

    public function update(Request $request, Product $product)
    {
        $this->requireAccess();
        $this->authorizeProduct($product);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'unit' => 'nullable|string|max:30',
            'price' => 'required|numeric|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['unit'] = $validated['unit'] ?: 'pcs';

        $product->update($validated);

        ActivityLogger::log('product.update', 'Produk/Jasa', 'Memperbarui produk "' . $product->name . '"');

        return back()->with('success', 'Produk/jasa berhasil diperbarui! ✅');
    }

    public function destroy(Product $product)
    {
        $this->requireAccess();
        $this->authorizeProduct($product);

        $product->delete();

        ActivityLogger::log('product.delete', 'Produk/Jasa', 'Menghapus produk "' . $product->name . '"');

        return back()->with('success', 'Produk/jasa berhasil dihapus!');
    }

    private function authorizeProduct(Product $product): void
    {
        if (!auth()->user()->isAdmin() && $product->user_id !== auth()->id()) {
            abort(403);
        }
    }

    private function requireAccess(): void
    {
        abort_unless(auth()->user()?->canManageProducts(), 403, 'Akses khusus Admin.');
    }
}
