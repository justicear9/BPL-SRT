<?php

namespace App\Http\Controllers\Workspace;

use App\Http\Controllers\Controller;
use App\Http\Requests\Workspace\StoreProductRequest;
use App\Http\Requests\Workspace\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Product::class);

        $query = Product::query()->orderBy('name');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(fn ($q) => $q->where('name', 'like', $term)
                ->orWhere('sku', 'like', $term)
                ->orWhere('item_category_code', 'like', $term));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $products = $query->paginate(25)->withQueryString();

        $base = Product::query();
        $listStats = [
            ['label' => __('Products'), 'value' => (string) (clone $base)->count(), 'caption' => __('Catalog size'), 'icon' => 'tabler-package', 'variant' => 'primary'],
            ['label' => __('Active'), 'value' => (string) (clone $base)->where('is_active', true)->count(), 'caption' => __('Sellable'), 'icon' => 'tabler-circle-check', 'variant' => 'success'],
            ['label' => __('Inactive'), 'value' => (string) (clone $base)->where('is_active', false)->count(), 'caption' => __('Hidden'), 'icon' => 'tabler-circle-x', 'variant' => 'secondary'],
            ['label' => __('Sampled'), 'value' => (string) (clone $base)->where('can_be_sampled', true)->count(), 'caption' => __('Sampling enabled'), 'icon' => 'tabler-flask', 'variant' => 'info'],
        ];

        return view('content.workspace.products.index', compact('products', 'listStats'));
    }

    public function create(): View
    {
        Gate::authorize('create', Product::class);

        return view('content.workspace.products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        Product::query()->create($request->validated());

        return redirect()->route('workspace.products.index')
            ->with('status', __('Product created.'));
    }

    public function edit(Product $product): View
    {
        Gate::authorize('update', $product);

        return view('content.workspace.products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->validated());

        return redirect()->route('workspace.products.index')
            ->with('status', __('Product updated.'));
    }

    public function destroy(Product $product): RedirectResponse
    {
        Gate::authorize('delete', $product);

        $product->delete();

        return redirect()->route('workspace.products.index')
            ->with('status', __('Product deleted.'));
    }
}
