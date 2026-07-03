<?php

namespace App\Repositories\Transaction;

use App\Models\Transaction;

class TransactionRepository implements TransactionRepositoryInterface
{
    public function query(array $filters = [])
    {
        $query = Transaction::query();

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['wallet_id'])) {
            $query->where('wallet_id', $filters['wallet_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('transaction_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('transaction_date', '<=', $filters['date_to']);
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
