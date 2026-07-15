<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Database\Seeders\CategorySeeder;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $incomeCategories = Category::where('user_id', $user->id)
            ->where('type', 'income')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $expenseCategories = Category::where('user_id', $user->id)
            ->where('type', 'expense')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Categories', [
            'incomeCategories' => $incomeCategories,
            'expenseCategories' => $expenseCategories,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'color' => 'nullable|string|max:32',
            'icon' => 'nullable|string|max:64',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $validated['user_id'] = auth()->id();

        Category::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense',
            'color' => 'nullable|string|max:32',
            'icon' => 'nullable|string|max:64',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $category->update($validated);

        return redirect()->back();
    }

    public function destroy(Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            abort(403);
        }

        $category->delete();

        return redirect()->back();
    }

    public function seedDefaults()
    {
        CategorySeeder::runForUser(auth()->id());

        return redirect()->back();
    }
}
