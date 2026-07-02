<?php

namespace App\Repositories\Transaction;

use App\Models\Transaction;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function query(array $filters = [])
    {
        $query = Transaction::query();

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
        return Transaction::all();
    }

    public function find($id)
    {
        return Transaction::find($id);
    }

    public function create(array $data)
    {
        return Transaction::create($data);
    }

    public function update($id, array $data)
    {
        $model = Transaction::findOrFail($id);
        $model->update($data);

        return $model;
    }

    public function delete($id)
    {
        return Transaction::destroy($id);
    }
}
