<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use App\Repositories\TagRepository;


class TagService 
{
    private $repository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->repository = $tagRepository;
    }

    public function create($str, $item)
    {   
        $tags = explode(',', preg_replace('/\W/u', ',', $str));
        $new_tags = [];
        foreach ($tags as $tag) {
            if (!$tag) continue;
            $model = $this->repository->getOneByParam(['title' => $tag]);
            if (!$model) {
                $model = $this->repository->create(['title' => $tag]);
                array_push($new_tags, $model);
                $item->tags()->attach($model);
            } else {
                if (!in_array($model->id, $item->tags()->pluck('tags.id')->toArray())) {
                    array_push($new_tags, $model);
                    $item->tags()->attach($model);
                }
            }

            // $model = $this->repository->create(['title' => $tag]);
            // $item->tags()->attach($model);
        }

        return $new_tags;
    }

    public function delete($item, $id)
    {
        try {
            $model = $this->repository->getOneById($id);
            $item->tags()->detach($model);
            return true;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }
}
