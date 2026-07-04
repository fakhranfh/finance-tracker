<?php

namespace App\Repositories\Transaction;

use App\Enums\TransactionType;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

interface TransactionRepositoryInterface
{
    public function query(array $filters = []);

    public function get(array $filters = [], array $with = []);

    public function getAll();

    public function find($id);

    public function create(array $data);

    public function update($id, array $data);

    public function delete($id);

    public function sumAmountByTypeForPeriod(string $userId, TransactionType $type, Carbon $from, Carbon $to): int;

    /**
     * @return Collection<int, object{category_id: string, category_name: string, total: int}>
     */
    public function topExpenseCategoriesForPeriod(string $userId, Carbon $from, Carbon $to): Collection;

    /**
     * @return Collection<int, Transaction>
     */
    public function recent(string $userId, int $limit = 10): Collection;
}
