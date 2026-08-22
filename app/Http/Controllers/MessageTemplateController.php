<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function index()
    {
        $templates = MessageTemplate::where('user_id', auth()->id())->get();
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

        MessageTemplate::create($validated);

        return back()->with('success', 'Template berhasil dibuat! ✅');
    }

    public function update(Request $request, MessageTemplate $template)
    {
        if ($template->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
        ]);

        $template->update($validated);

        return back()->with('success', 'Template berhasil diperbarui! ✅');
    }

    public function destroy(MessageTemplate $template)
    {
        if ($template->user_id !== auth()->id()) {
            abort(403);
        }

        $template->delete();

        return back()->with('success', 'Template berhasil dihapus!');
    }
}
