<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\State;

class StateController extends Controller
{
    public function index()
    {
        return view('admin.screens.state', [
            'total' => State::count(),
        ]);
    }

    public function json()
    {
        return response()->json(State::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:states,name',
            'status' => 'nullable|integer|in:0,1',
        ]);

        $state = State::create([
            'name' => $request->name,
            'status' => $request->status ?? 1,
        ]);

        return response()->json(['success' => true, 'state' => $state]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:states,name,' . $id,
            'status' => 'nullable|integer|in:0,1',
        ]);

        $state = State::findOrFail($id);
        $state->update([
            'name' => $request->name,
            'status' => $request->status ?? $state->status,
        ]);

        return response()->json(['success' => true, 'state' => $state]);
    }

    public function toggleStatus($id)
    {
        $state = State::findOrFail($id);
        $state->status = $state->status == 1 ? 0 : 1;
        $state->save();

        return response()->json(['success' => true, 'status' => $state->status]);
    }

    public function destroy($id)
    {
        $state = State::findOrFail($id);
        $state->delete();
        return response()->json(['success' => true]);
    }
}
