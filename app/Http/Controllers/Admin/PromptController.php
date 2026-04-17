<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Prompt;

class PromptController extends Controller
{
    public function index()
    {
        $showFilters = false;
        return view('admin.screens.prompt', [
            'showFilters' => $showFilters,
            'total' => Prompt::count(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:prompts,name',
            'prompt_text' => 'required|string',
        ]);

        $prompt = Prompt::create([
            'name' => $request->name,
            'prompt_text' => $request->prompt_text,
        ]);

        return response()->json(['success' => true, 'prompt' => $prompt]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:prompts,name,' . $id,
            'prompt_text' => 'required|string',
        ]);

        $prompt = Prompt::findOrFail($id);
        $prompt->update([
            'name' => $request->name,
            'prompt_text' => $request->prompt_text,
        ]);

        return response()->json(['success' => true, 'prompt' => $prompt]);
    }

    public function destroy($id)
    {
        $prompt = Prompt::findOrFail($id);
        $prompt->delete();
        return response()->json(['success' => true]);
    }

    public function json()
    {
        return response()->json(Prompt::all());
    }
}
