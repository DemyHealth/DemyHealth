<?php

namespace Database\Seeders;

use App\Services\InMemoryRepository;
use App\Support\Roles;

class SuperAdminSeeder
{
    public function __construct(private ?InMemoryRepository $repository = null)
    {
        $this->repository ??= new InMemoryRepository();
    }

    public function run(): InMemoryRepository
    {
        $this->repository->upsertUser(
            name: 'DemyHealth Super Admin',
            email: 'superadmin@demyhealth.com',
            role: Roles::SUPER_ADMIN,
            password: env('DEMYHEALTH_SUPERADMIN_PASSWORD', 'ChangeMeNow@2026'),
        );

        return $this->repository;
    }
}
