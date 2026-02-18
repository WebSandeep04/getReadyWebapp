<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Models\City;
use App\Models\State;

class CityController extends Controller
{
    public function index()
    {
        return view('admin.screens.city', [
            'total' => City::count(),
            'states' => State::where('status', 1)->get(),
        ]);
    }

    public function json(Request $request)
    {
        $query = City::with('state');

        if ($request->has('state_id') && $request->state_id != '') {
            $query->where('state_id', $request->state_id);
        }

        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|integer|in:0,1',
        ]);

        $city = City::create([
            'state_id' => $request->state_id,
            'name' => $request->name,
            'status' => $request->status ?? 1,
        ]);

        return response()->json(['success' => true, 'city' => $city->load('state')]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id',
            'name' => 'required|string|max:255',
            'status' => 'nullable|integer|in:0,1',
        ]);

        $city = City::findOrFail($id);
        $city->update([
            'state_id' => $request->state_id,
            'name' => $request->name,
            'status' => $request->status ?? $city->status,
        ]);

        return response()->json(['success' => true, 'city' => $city->load('state')]);
    }

    public function toggleStatus($id)
    {
        $city = City::findOrFail($id);
        $city->status = $city->status == 1 ? 0 : 1;
        $city->save();

        return response()->json(['success' => true, 'status' => $city->status]);
    }

    public function destroy($id)
    {
        $city = City::findOrFail($id);
        $city->delete();
        return response()->json(['success' => true]);
    }
}
