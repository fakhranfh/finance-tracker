<?php

namespace App\Repositories\Category;

use App\Models\Category;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function query(array $filters = [])
    {
        $query = Category::query();

        if (! empty($filters['user_id'])) {
            $query->where(function ($q) use ($filters) {
                $q->whereNull('user_id')->orWhere('user_id', $filters['user_id']);
            });
        }

        if (! empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (! empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        return $query;
    }

    public function get(array $filters = [], array $with = [])
    {
        $query = $this->query($filters);

        return $query->with($with)->get();
    }

    public function getAll()
    {
        return Category::all();
    }

    public function find($id)
    {
        return Category::find($id);
    }

    public function create(array $data)
    {
        return Category::create($data);
    }

    public function update($id, array $data)
    {
        $model = Category::findOrFail($id);
        $model->update($data);

        return $model;
    }

    public function delete($id)
    {
        return Category::destroy($id);
    }
}
