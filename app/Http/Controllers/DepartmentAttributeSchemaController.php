<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\DepartmentAttributeSchemaRequest;
use App\Models\Department;
use App\Models\DepartmentAttributeSchema;
use Illuminate\Http\RedirectResponse;

class DepartmentAttributeSchemaController extends Controller
{
    public function store(DepartmentAttributeSchemaRequest $request, Department $department): RedirectResponse
    {
        $department->attributeSchemas()->create($request->validatedForModel());

        return redirect()->route('departments.edit', $department)->with('success', 'Attribute added.');
    }

    public function update(DepartmentAttributeSchemaRequest $request, DepartmentAttributeSchema $attributeSchema): RedirectResponse
    {
        $attributeSchema->update($request->validatedForModel());

        return redirect()->route('departments.edit', $attributeSchema->department_id)->with('success', 'Attribute updated.');
    }

    public function destroy(DepartmentAttributeSchema $attributeSchema): RedirectResponse
    {
        $departmentId = $attributeSchema->department_id;
        $attributeSchema->delete();

        return redirect()->route('departments.edit', $departmentId)->with('success', 'Attribute removed.');
    }
}
