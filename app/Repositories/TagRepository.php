<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Models\Tag;

class TagRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return Tag::class;
    }
}