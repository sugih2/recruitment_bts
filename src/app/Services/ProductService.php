<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\ProductRepository;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(private readonly ProductRepository $repository)
    {
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function find(int $id): Product
    {
        return $this->repository->find($id);
    }

    public function create(array $data, User $user): Product
    {
        return DB::transaction(fn (): Product => $this->repository->create($this->withAudit($data, $user, true)));
    }

    public function update(Product $product, array $data, User $user): Product
    {
        return DB::transaction(fn (): Product => $this->repository->update($product, $this->withAudit($data, $user)));
    }

    public function delete(Product $product): void
    {
        DB::transaction(function () use ($product): void {
            $this->repository->delete($product);
        });
    }

    private function withAudit(array $data, User $user, bool $creating = false): array
    {
        $data['updated_by'] = $user->name;
        $data['updated_by_id'] = $user->id;

        if ($creating) {
            $data['created_by'] = $user->name;
            $data['created_by_id'] = $user->id;
        }

        return $data;
    }
}
