<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    private function authorizeProductAccess(User $user, Product $product): void
    {
        if (! $user->isAdmin() && $product->user_id !== $user->id) {
            abort(403, 'Forbidden.');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request):JsonResponse
    {
        $user = $request->user('api');

        $products = Product::query()
            ->when(
                ! $user->isAdmin(),
                fn ($query) => $query->where('user_id', $user->id)
            )
            ->latest()
            ->paginate(15);

        return response()->json($products);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
        'title' => ['required', 'string', 'max:255'],
        'slug' => ['required', 'string', 'max:255', 'unique:products,slug'],
        'price' => ['required', 'numeric', 'min:0'],
        'description' => ['nullable', 'string'],
        ]);

        $product = $request->user('api')
            ->products()
            ->create($validated);

        return response()->json([
            'message' => 'Product created successfully.',
            'product' => $product,
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Product $product)
    {
        $this->authorizeProductAccess($request->user('api'), $product);

        return response()->json([
            'product' => $product,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product): JsonResponse
    {
        $this->authorizeProductAccess($request->user('api'), $product);

        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'string',
                'max:255',
                Rule::unique('products', 'slug')->ignore($product->id),
            ],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string'],
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product updated successfully.',
            'product' => $product->fresh(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Product $product)
    {
            $this->authorizeProductAccess($request->user('api'), $product);

            $product->delete();

            return response()->json([
                'message' => 'Product deleted successfully.',
            ]);
    }

    public function userProducts(User $user): JsonResponse
    {
        $products = $user->products()
            ->latest()
            ->paginate(15);

        return response()->json($products);
    }
}
