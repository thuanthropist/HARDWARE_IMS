<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentRequest;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DepartmentController extends Controller
{
    public function index(): View
    {
        $departments = Department::withCount(['products', 'categories'])
            ->orderBy('sort_order')
            ->paginate(15);

        return view('departments.index', ['departments' => $departments]);
    }

    public function create(): View
    {
        return view('departments.create');
    }

    public function store(DepartmentRequest $request): RedirectResponse
    {
        $data = $request->validatedForModel();

        if ($request->hasFile('image')) {
            $data['image_path'] = $request->file('image')->store('departments', 'public');
        }

        Department::create($data);

        return redirect()->route('departments.index')->with('success', 'Department created.');
    }

    public function show(Department $department): RedirectResponse
    {
        return redirect()->route('departments.edit', $department);
    }

    public function edit(Department $department): View
    {
        $department->load(['attributeSchemas']);

        return view('departments.edit', ['department' => $department]);
    }

    public function update(DepartmentRequest $request, Department $department): RedirectResponse
    {
        $data = $request->validatedForModel();

        if ($request->hasFile('image')) {
            if ($department->image_path) {
                Storage::disk('public')->delete($department->image_path);
            }

            $data['image_path'] = $request->file('image')->store('departments', 'public');
        }

        $department->update($data);

        return redirect()->route('departments.edit', $department)->with('success', 'Department updated.');
    }

    public function destroy(Department $department): RedirectResponse
    {
        if ($department->products()->exists()) {
            return redirect()->route('departments.index')
                ->with('error', 'Cannot delete a department that still has products.');
        }

        $department->delete();

        return redirect()->route('departments.index')->with('success', 'Department deleted.');
    }
}
