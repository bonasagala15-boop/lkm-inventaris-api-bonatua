<?php

namespace App\Services;

use App\Contracts\CategoryRepositoryInterface;
use App\Contracts\ProductRepositoryInterface;
use App\Exceptions\NotFoundException;
use InvalidArgumentException;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $repository,
        private CategoryRepositoryInterface $categories
    ) {}

    public function getAll(array $filters = []): array
    {
        $clean = [];

        $search = is_string($filters['search'] ?? null)
            ? trim($filters['search'])
            : '';

        if ($search !== '') {
            $clean['search'] = $search;
        }

        $categoryId = $filters['category_id'] ?? null;

        if ($categoryId !== null && $categoryId !== '') {
            $categoryId = filter_var($categoryId, FILTER_VALIDATE_INT);

            if ($categoryId === false || $categoryId < 1) {
                throw new InvalidArgumentException(
                    'Filter category_id tidak valid'
                );
            }

            $clean['category_id'] = $categoryId;
        }

        return $this->repository->getAll($clean);
    }

    public function getById(int $id): array
    {
        $product = $this->repository->findById($id);

        if (!$product) {
            throw new NotFoundException('Produk tidak ditemukan');
        }

        return $product;
    }

    public function create(array $data): array
    {
        return $this->repository->create($this->validate($data));
    }

    public function update(int $id, array $data): array
    {
        $this->getById($id);

        return $this->repository->update($id, $this->validate($data))
            ?? throw new NotFoundException('Produk tidak ditemukan');
    }

    public function delete(int $id): void
    {
        $this->getById($id);

        $this->repository->delete($id);
    }

    /**
     * Validasi aturan bisnis produk (dipakai create() dan update()).
     */
    private function validate(array $data): array
    {
        // 1. Nama tidak boleh kosong dan maksimal 150 karakter
        $name = is_string($data['name'] ?? null)
            ? trim($data['name'])
            : '';

        if ($name === '') {
            throw new InvalidArgumentException(
                'Nama produk wajib diisi'
            );
        }

        if (mb_strlen($name) > 150) {
            throw new InvalidArgumentException(
                'Nama produk maksimal 150 karakter'
            );
        }

        // 2. category_id harus bilangan bulat dan kategorinya ada
        $categoryId = filter_var(
            $data['category_id'] ?? null,
            FILTER_VALIDATE_INT
        );

        if (
            $categoryId === false ||
            $categoryId < 1 ||
            $this->categories->findById($categoryId) === null
        ) {
            throw new InvalidArgumentException('Kategori tidak valid');
        }

        // 3. Harga harus numerik dan tidak boleh negatif
        if (
            !isset($data['price']) ||
            !is_numeric($data['price']) ||
            !is_finite((float) $data['price']) ||
            (float) $data['price'] < 0
        ) {
            throw new InvalidArgumentException(
                'Harga harus berupa angka nonnegatif'
            );
        }

        // 4. Stok harus bilangan bulat dan tidak boleh negatif
        $stock = filter_var(
            $data['stock'] ?? null,
            FILTER_VALIDATE_INT
        );

        if ($stock === false || $stock < 0) {
            throw new InvalidArgumentException(
                'Stok harus berupa bilangan bulat nonnegatif'
            );
        }

        return [
            'category_id' => $categoryId,
            'name' => $name,
            'price' => (float) $data['price'],
            'stock' => $stock
        ];
    }
}

