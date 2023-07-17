<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Repositories\ItemRepository;
use App\Services\TagService;


class ItemService 
{
    private $imageService;
    private $tagService;
    private $repository;

    public function __construct(ImageService $imageService, ItemRepository $itemRepository,
                                TagService $tagService)
    {
        $this->imageService = $imageService;
        $this->tagService = $tagService;
        $this->repository = $itemRepository;
    }

    public function create($data)
    {   
        $model = $this->repository->create($data);

        if (isset($data['image'])) {
            $this->imageService->create($data['image'], $model);
        }

        if (isset($data['tag'])) {
            $this->tagService->create($data['tag'], $model);
        };

        return $model;
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();
            $item = $this->repository->getOneById($id);

            if ($item->image) 
                $this->imageService->delete($item->image);
            
            $this->repository->delete($id);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return false;
        }
    }

    public function update($data, $id)
    { 
        try {
            DB::beginTransaction();
            $model = $this->repository->update($data, $id);

            if (isset($data['title']))
                $result = $this->tagService->create($data['title'], $model);

            if (isset($data['item_image'])) 
                $result = $this->imageService->create($data['item_image'], $model);

            DB::commit();
            return $result;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error($e->getMessage());
            return false;
        }
    }

    public function destroyImage($id)
    {
        $model = $this->repository->getOneById($id);

        if ($model->image) 
            return $this->imageService->delete($model->image);
        
        return false;
    }

    public function destroyTag($item_id, $tag_id)
    {
        $model = $this->repository->getOneById($item_id);

        return $this->tagService->delete($model, $tag_id);
    }
}
