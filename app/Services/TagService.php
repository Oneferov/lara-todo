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
        }

        return $new_tags;
    }

    public function delete($item, $id)
    {
        try {
            $model = $this->repository->getOneById($id);
            $item->tags()->detach($model);

            $is_last = [];
            if ($this->checkIsLast($id)) {
                array_push($is_last, $id);
                $this->repository->delete($id);
            }
                
            return [
                'success' => true,
                'is_last' => $is_last
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return ['success' => false];
        }
    }

    public function checkIsLast($id)
    {
        if (!\DB::table('item_tag')->where('tag_id', $id)->first())
            return true;

        return false;
    }
}
