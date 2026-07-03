<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(private CategoryService $categoryService) {}

    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        $categories = $this->categoryService->get(['user_id' => auth()->id()]);
        $expenseCategories = $categories->where('type', TransactionType::Expense)->values();
        $incomeCategories = $categories->where('type', TransactionType::Income)->values();

        return view('categories.index', compact('expenseCategories', 'incomeCategories'));
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = auth()->user()->hasRole('admin') ? null : auth()->id();

        $this->categoryService->create($data);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->categoryService->update($category->id, $request->validated());

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        $this->categoryService->delete($category->id);

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}
