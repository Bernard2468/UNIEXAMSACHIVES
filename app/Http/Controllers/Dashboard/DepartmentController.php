<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Department;

class DepartmentController extends Controller
{
    public function index()
    {
        // Tab 1 — Faculties / Schools, each with its nested departments.
        $faculties = Department::faculties()
            ->with('departments')
            ->orderBy('name')
            ->get();

        // Tab 2 — standalone Units.
        $units = Department::units()->orderBy('name')->get();

        // Edge case: a department that has been typed 'department' but has no parent
        // (e.g. its faculty was deleted). Surface it so it can be re-assigned rather
        // than silently vanishing.
        $unassignedDepartments = Department::where('type', Department::TYPE_DEPARTMENT)
            ->where(function ($q) {
                $q->whereNull('parent_id')
                    ->orWhereNotIn('parent_id', function ($sub) {
                        $sub->select('id')->from('departments')
                            ->where('type', Department::TYPE_FACULTY);
                    });
            })
            ->orderBy('name')
            ->get();

        // Parent options for the create/edit forms.
        $facultyOptions = $faculties->map(fn ($f) => ['id' => $f->id, 'name' => $f->name]);

        $total = Department::count();

        return view('admin.departments', compact(
            'faculties',
            'units',
            'unassignedDepartments',
            'facultyOptions',
            'total'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateDepartment($request);

        Department::create([
            'name'      => $data['name'],
            'type'      => $data['type'],
            'parent_id' => $data['type'] === Department::TYPE_DEPARTMENT ? $data['parent_id'] : null,
        ]);

        return redirect()->route('departments.index')
            ->with('success', 'Saved successfully.');
    }

    public function edit(Department $department)
    {
        $facultyOptions = Department::faculties()
            ->where('id', '!=', $department->id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.edit-department', compact('department', 'facultyOptions'));
    }

    public function update(Request $request, Department $department)
    {
        $data = $this->validateDepartment($request, $department);

        // Guard: a faculty/school that still has departments under it cannot be
        // turned into a department or unit — that would orphan real assignments.
        if ($department->isFaculty()
            && $data['type'] !== Department::TYPE_FACULTY
            && $department->departments()->exists()) {
            return back()->withInput()->withErrors([
                'type' => 'This faculty/school still has departments under it. Move or delete those departments before changing its type.',
            ]);
        }

        $department->update([
            'name'      => $data['name'],
            'type'      => $data['type'],
            'parent_id' => $data['type'] === Department::TYPE_DEPARTMENT ? $data['parent_id'] : null,
        ]);

        return redirect()->route('departments.index')
            ->with('success', 'Updated successfully.');
    }

    public function destroy(Department $department)
    {
        // Guard: don't delete a faculty/school that still holds departments — the
        // admin should consciously move or remove its children first.
        if ($department->isFaculty() && $department->departments()->exists()) {
            return back()->withErrors([
                'delete' => 'This faculty/school still has departments under it. Move or delete them first.',
            ]);
        }

        $department->delete();

        return redirect()->route('departments.index')
            ->with('success', 'Deleted successfully.');
    }

    /**
     * Shared validation for store/update. A 'department' must name a real faculty as
     * its parent; faculties and units never carry a parent.
     */
    private function validateDepartment(Request $request, ?Department $current = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in(Department::TYPES)],
            'parent_id' => [
                'nullable',
                'required_if:type,' . Department::TYPE_DEPARTMENT,
                Rule::exists('departments', 'id')->where(
                    fn ($q) => $q->where('type', Department::TYPE_FACULTY)
                ),
                // A department can never be its own parent.
                Rule::notIn($current ? [$current->id] : []),
            ],
        ], [
            'parent_id.required_if' => 'Please choose the faculty or school this department belongs to.',
            'parent_id.exists'      => 'The selected parent must be a faculty or school.',
            'parent_id.not_in'      => 'A department cannot be its own parent.',
        ]);

        return $data;
    }
}
