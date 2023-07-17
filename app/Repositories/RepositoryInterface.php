<?php

namespace App\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;

interface RepositoryInterface {

  public function getModelClass(): string;

  public function getOneById($id): ?Model;

  public function getByIds(array $ids): Collection;

  public function getAll(): Collection;

  public function getQuery();

  public function create(array $data);

  public function update(array $data, $id);

  public function delete($id);
}