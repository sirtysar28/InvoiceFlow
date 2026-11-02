<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()?->isAdmin()) {
                abort(403, 'Hanya Admin yang bisa mengelola template pesan.');
            }
            return $next($request);
        });
    }

    private function companyId(): ?int
    {
        return auth()->user()?->company_id;
    }

    public function index()
    {
        $cid = $this->companyId();

        // Super Admin (tanpa perusahaan) mengelola template PLATFORM
        $query = MessageTemplate::where('company_id', $cid);

        // SaaS: kunjungan pertama perusahaan → copy template platform jadi miliknya
        if ($cid !== null && $query->count() === 0) {
            foreach (MessageTemplate::whereNull('company_id')->get() as $global) {
                $copy = $global->replicate(['id']);
                $copy->company_id = $cid;
                $copy->user_id = auth()->id();
                $copy->save();
            }
        }

        $templates = (clone $query)->orderBy('type')->orderBy('name')->get();

        return view('templates.index', compact('templates'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:whatsapp,email',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['company_id'] = $this->companyId();

        MessageTemplate::create($validated);

        ActivityLogger::log('template.create', 'Template Pesan', 'Membuat template "' . $validated['name'] . '" (' . $validated['type'] . ')');

        return back()->with('success', 'Template berhasil dibuat! ✅');
    }

    public function update(Request $request, MessageTemplate $template)
    {
        $this->authorizeTemplate($template);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        $template->update($validated);

        ActivityLogger::log('template.update', 'Template Pesan', 'Memperbarui template "' . $template->name . '"');

        return back()->with('success', 'Template berhasil diperbarui! ✅');
    }

    public function destroy(MessageTemplate $template)
    {
        $this->authorizeTemplate($template);

        $name = $template->name;
        $template->delete();

        ActivityLogger::log('template.delete', 'Template Pesan', 'Menghapus template "' . $name . '"');

        return back()->with('success', 'Template berhasil dihapus! 🗑️');
    }

    /** Hanya boleh kelola template perusahaan sendiri (SA = platform). */
    private function authorizeTemplate(MessageTemplate $template): void
    {
        if ($template->company_id !== $this->companyId()) {
            abort(403, 'Template ini milik perusahaan lain — tidak bisa diubah.');
        }
    }
}
