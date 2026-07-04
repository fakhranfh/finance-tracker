<?php

namespace App\Repositories\Transaction;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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

    public function sumAmountByTypeForPeriod(string $userId, TransactionType $type, Carbon $from, Carbon $to): int
    {
        return (int) Transaction::where('user_id', $userId)
            ->where('type', $type)
            ->whereBetween('transaction_date', [$from, $to])
            ->sum('amount');
    }

    public function topExpenseCategoriesForPeriod(string $userId, Carbon $from, Carbon $to): Collection
    {
        return Transaction::query()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.type', TransactionType::Expense)
            ->whereBetween('transactions.transaction_date', [$from, $to])
            ->groupBy('transactions.category_id', 'categories.name')
            ->orderByDesc('total')
            ->select([
                'transactions.category_id as category_id',
                'categories.name as category_name',
                DB::raw('SUM(transactions.amount) as total'),
            ])
            ->get();
    }

    public function recent(string $userId, int $limit = 10): Collection
    {
        return Transaction::where('user_id', $userId)
            ->with(['wallet', 'category'])
            ->orderByDesc('transaction_date')
            ->limit($limit)
            ->get();
    }
}
