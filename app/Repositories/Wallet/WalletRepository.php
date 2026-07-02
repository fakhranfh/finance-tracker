<?php

namespace App\Repositories\Wallet;

use App\Models\Wallet;

class WalletRepository implements WalletRepositoryInterface
{
    public function query(array $filters = [])
    {
        $query = Wallet::query();

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
        return Wallet::all();
    }

    public function find($id)
    {
        return Wallet::find($id);
    }

    public function create(array $data)
    {
        return Wallet::create($data);
    }

    public function update($id, array $data)
    {
        $model = Wallet::findOrFail($id);
        $model->update($data);

        return $model;
    }

    public function delete($id)
    {
        return Wallet::destroy($id);
    }
}
