<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->when($request->filled('search'), fn ($query) => $query->where('title', 'like', '%'.$request->string('search')->toString().'%'))
            ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')->toString()))
            ->latest()
            ->paginate(min($request->integer('limit', 10), 100));

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rules());

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 400);
        }

        $product = Product::create($this->productData($request, true));

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validator = Validator::make($request->all(), $this->rules(true));

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 400);
        }

        $product->update($this->productData($request));

        return response()->json($product->fresh());
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Product deleted successfully.']);
    }

    private function rules(bool $partial = false): array
    {
        $required = $partial ? ['sometimes'] : ['required'];

        return [
            'title' => [...$required, 'string', 'max:255'],
            'price' => [...$required, 'numeric', 'min:0'],
            'description' => ['sometimes', 'nullable', 'string'],
            'category' => [...$required, 'string', 'max:255'],
            'images' => [...$required, 'array', 'min:1'],
            'images.*' => ['string', 'url'],
        ];
    }

    private function productData(Request $request, bool $creating = false): array
    {
        $user = $request->user();
        $data = $request->only(['title', 'price', 'description', 'category', 'images']);
        $data['updated_by'] = $user->name;
        $data['updated_by_id'] = $user->id;

        if ($creating) {
            $data['created_by'] = $data['updated_by'];
            $data['created_by_id'] = $user->id;
        }

        return $data;
    }
}
