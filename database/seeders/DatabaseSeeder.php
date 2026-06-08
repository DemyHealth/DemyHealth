<?php

namespace Database\Seeders;

use App\Services\InMemoryRepository;

class DatabaseSeeder
{
    public function __construct(private ?InMemoryRepository $repository = null)
    {
        $this->repository ??= new InMemoryRepository();
    }

    public function run(): InMemoryRepository
    {
        return (new SuperAdminSeeder($this->repository))->run();
    }
}
