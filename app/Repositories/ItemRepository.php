<?php

namespace App\Repositories;

use App\Models\Item;

class ItemRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return Item::class;
    }

    public function getQuery()
    {
        return $this->model->query()->with(['image', 'tags'])->orderBy('id', 'DESC');
    }

    public function getOneById($id): ?Item
    {
        return $this->model->with(['image', 'tags'])
                    ->where('id', $id)
                    ->first();
    }

    public function getAllWithTags($str)
    {
        return $this->getQuery()
                ->whereHas('tags', function($query) use ($str) {
                    $values = explode(',', preg_replace('/\W/u', ',', $str));
                    $query->whereIn('title', $values);
                })
                ->get();
    }
}