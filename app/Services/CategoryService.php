<?php

namespace App\Services;

use App\Contracts\CategoryRepositoryInterface;
use App\Exceptions\NotFoundException;
use InvalidArgumentException;

class CategoryService
{
    public function __construct(
        private CategoryRepositoryInterface $repository
    ) {}

    public function getAll(): array
    {
        return $this->repository->getAll();
    }

    public function getById(int $id): array
    {
        $category = $this->repository->findById($id);

        if (!$category) {
            throw new NotFoundException('Kategori tidak ditemukan');
        }

        return $category;
    }

    public function create(array $data): array
    {
        $name = $this->validateName($data);

        return $this->repository->create([
            'name' => $name,
            'description' => $data['description'] ?? null
        ]);
    }

    public function update(int $id, array $data): array
    {
        $this->getById($id);

        $name = $this->validateName($data);

        return $this->repository->update($id, [
            'name' => $name,
            'description' => $data['description'] ?? null
        ]);
    }

    public function delete(int $id): void
    {
        $this->getById($id);

        $this->repository->delete($id);
    }

    private function validateName(array $data): string
    {
        $name = is_string($data['name'] ?? null)
            ? trim($data['name'])
            : '';

        if ($name === '') {
            throw new InvalidArgumentException(
                'Nama kategori wajib diisi'
            );
        }

        if (mb_strlen($name) > 100) {
            throw new InvalidArgumentException(
                'Nama kategori maksimal 100 karakter'
            );
        }

        return $name;
    }
}
