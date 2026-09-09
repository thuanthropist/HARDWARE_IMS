<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\Department;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        $categories = Category::with(['department', 'parent'])
            ->withCount('products')
            ->orderBy('department_id')
            ->orderBy('name')
            ->paginate(20);

        return view('categories.index', ['categories' => $categories]);
    }

    public function create(): View
    {
        return view('categories.create', $this->formData());
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        Category::create($request->validatedForModel());

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category): View
    {
        return view('categories.edit', $this->formData($category));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validatedForModel());

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists() || $category->children()->exists()) {
            return redirect()->route('categories.index')
                ->with('error', 'Cannot delete a category that has products or sub-categories.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }

    private function formData(?Category $category = null): array
    {
        return [
            'category' => $category,
            'departments' => Department::orderBy('sort_order')->get(),
            'parentOptions' => Category::when($category, fn ($query) => $query->where('id', '!=', $category->id))->orderBy('name')->get(),
        ];
    }
}
