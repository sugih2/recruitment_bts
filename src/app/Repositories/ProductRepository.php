<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Facades\Cache;

class ProductRepository
{
    private const CACHE_TTL = 600;

    private const CACHE_SCHEMA = 3;

    public function paginate(array $filters): LengthAwarePaginator
    {
        $page = (int) ($filters['page'] ?? 1);
        $limit = (int) ($filters['limit'] ?? 10);
        $version = (int) Cache::store('redis')->get('products:cache-version', 1);
        $key = 'products:index:s'.self::CACHE_SCHEMA.':v'.$version.':'.md5((string) json_encode([
            'search' => $filters['search'] ?? null,
            'category' => $filters['category'] ?? null,
            'limit' => $limit,
            'page' => $page,
        ]));

        $cached = Cache::store('redis')->remember($key, self::CACHE_TTL, function () use ($filters, $limit, $page): array {
            $query = Product::query()
                ->when($filters['search'] ?? null, fn ($query, $search) => $query->where('title', 'like', '%'.$search.'%'))
                ->when($filters['category'] ?? null, fn ($query, $category) => $query->where('category', $category));

            return [
                'items' => $query->latest()->forPage($page, $limit)->get()
                    ->map(fn (Product $product): array => $product->getAttributes())
                    ->all(),
                'total' => $query->count(),
            ];
        });

        return new Paginator(
            Product::hydrate($cached['items']),
            $cached['total'],
            $limit,
            $page,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => 'page'],
        );
    }

    public function find(int $id): Product
    {
        $version = (int) Cache::store('redis')->get('products:cache-version', 1);

        $attributes = Cache::store('redis')->remember(
            'products:item:s'.self::CACHE_SCHEMA.':v'.$version.':'.$id,
            self::CACHE_TTL,
            fn (): array => Product::findOrFail($id)->getAttributes(),
        );

        return Product::hydrate([$attributes])->firstOrFail();
    }

    public function create(array $data): Product
    {
        $product = Product::create($data);
        $this->flushCache();

        return $product;
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        $this->flushCache();

        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
        $this->flushCache();
    }

    private function flushCache(): void
    {
        Cache::store('redis')->increment('products:cache-version');
    }
}
