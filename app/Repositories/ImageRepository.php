<?php

namespace App\Repositories;

use App\Models\Image;

class ImageRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return Image::class;
    }
}