<?php

namespace App\Repositories;

use Exception;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class AbstractRepository implements RepositoryInterface
{
    protected $model;

    protected $auth;

    abstract public function getModelClass(): string;

    public function setModel($model_path)
    {
        $this->model = app($model_path);
    }

    public function __construct()
    {
        $this->setModel($this->getModelClass());
    }

    public function getOneById($id): ?Model
    {
        return $this->model->find($id);
    }

    public function getByIds(array $ids): Collection
    {
        return $this->model->whereIn($this->model->getKeyName(), $ids)->get();
    }

    public function getOneByParam(array $data): ?Model
    {
        return $this->model->where($data)->first();
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getQuery()
    {
        return $this->model->query();
    }

    public function getCount()
    {
        return $this->model->count();
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update(array $data, $id)
    {
        $model = $this->model->find($id);
        $model->update($data);
        return $model;
    }

    public function delete($id)
    {
        $item = $this->model->find($id);
        $item->delete();
    }
}
