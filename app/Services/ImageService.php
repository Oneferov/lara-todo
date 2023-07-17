<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Repositories\ImageRepository;
use App\Models\Image;


class ImageService 
{
    private $repository;

    public function __construct(ImageRepository $imageRepository)
    {
        $this->repository = $imageRepository;
    }


    public function create($image, $item)
    {   
        $path = Storage::disk('public')->put('items', $image);
        $model = $this->repository->create([
            'path' => $path, 
            'item_id' => $item->id
        ]);

        return $model;
    }

    public function delete($image)
    {
        try {
            Storage::disk('public')->delete($image->path);
            $this->repository->delete($image->id);
            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
