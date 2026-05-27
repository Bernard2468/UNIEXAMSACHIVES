<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PositionController extends Controller
{
    public function index()
    {
        $perPage = in_array(request('per_page'), [5, 10, 25, 50]) ? (int) request('per_page') : 10;
        $positions = Position::orderBy('name')->paginate($perPage);
        return view('admin.positions', compact('positions'));
    }

    public function store(Request $request)
    {
        $data = $this->validatePayload($request);
        Position::create($data);

        return redirect()->route('positions.index')->with('success', 'Position created successfully.');
    }

    public function edit(Position $position)
    {
        return view('admin.edit-position', compact('position'));
    }

    public function update(Request $request, Position $position)
    {
        $data = $this->validatePayload($request);
        $position->update($data);

        return redirect()->route('positions.index')->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        $position->delete();
        return redirect()->route('positions.index')->with('success', 'Position deleted successfully.');
    }

    /**
     * Validate + normalise the request payload for create/update.
     * The `category` field tags the position into one of the form
     * routing pools (HOD / Dean / Director) — see Position::CATEGORIES.
     */
    protected function validatePayload(Request $request): array
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'category' => ['nullable', Rule::in(array_keys(Position::CATEGORIES))],
        ]);

        $data['category'] = $data['category'] ?: null;

        return $data;
    }
}
