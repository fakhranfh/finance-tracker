<?php

namespace App\Repositories\Transfer;

use App\Models\Transfer;

class TransferRepository implements TransferRepositoryInterface
{
    public function query(array $filters = [])
    {
        $query = Transfer::query();

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['wallet_id'])) {
            $query->where(function ($subQuery) use ($filters) {
                $subQuery->where('from_wallet_id', $filters['wallet_id'])
                    ->orWhere('to_wallet_id', $filters['wallet_id']);
            });
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('transfer_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('transfer_date', '<=', $filters['date_to']);
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
        return Transfer::all();
    }

    public function find($id)
    {
        return Transfer::find($id);
    }

    public function create(array $data)
    {
        return Transfer::create($data);
    }

    public function update($id, array $data)
    {
        $model = Transfer::findOrFail($id);
        $model->update($data);

        return $model;
    }

    public function delete($id)
    {
        return Transfer::destroy($id);
    }
}
