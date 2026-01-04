<?php
namespace App\Repositories\Master;

use App\Models\Industry;
use Illuminate\Database\Eloquent\Collection;

interface IndustryRepositoryInterface
{
    public function getAll(): Collection;
    public function store(array $data): Industry;
}


