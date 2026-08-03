<?php

namespace App\Modules\Application\Repositories;
use App\Traits\HasDateFilter;
use App\Modules\Application\Models\Application;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Modules\Application\Models\Process;
use App\Repositories\BaseRepository;
// use App\Modules\Application\Models\Process;

class ProcessRepository extends BaseRepository
{


        public function __construct(Process $model)
        {
            parent::__construct($model);
        }

     public function getProcessList(): Collection
    {
        return Process::query()
            ->select('id', 'name')
            ->orderBy('id')
            ->get()
            ->map(fn (Process $process) => [
                'id'   => $process->id,
                'name' => $process->name,
            ]);
    }

    public function getFilterProcessList(callable $scopeApplications, int $hiringListProcessId): array
    {
        $baseQuery = fn () => tap(Application::query(), $scopeApplications);

        $items = Process::query()
            ->select('id', 'name')
            ->orderBy('id')
            ->get()
            ->map(function (Process $process) use ($baseQuery) {
                $count = $baseQuery()->whereHas(
                    'currentProcess',
                    fn ($q) => $q->where('process_id', $process->id)
                )->count();

                if ($count === 0) {
                    return null;
                }

                return [
                    'id' => $process->id,
                    'name' => sprintf('%s (%d)', $process->name, $count),
                ];
            })
            ->filter()
            ->values();

        $hiringListCount = $baseQuery()->whereDoesntHave('processes')->count();

        if ($hiringListCount > 0) {
            $items->prepend([
                'id' => $hiringListProcessId,
                'name' => sprintf('hiring_list (%d)', $hiringListCount),
            ]);
        }

        return $items->toArray();
    }

    public function applyFilters(Builder $query, array $filters): void
    {
        $this->applySearch($query, $filters['search'] ?? null);
        $this->applyDateFilter($query, $filters);
    }

    protected function applySearch(Builder $query, ?string $search): void
    {
        if (!$search) {
            return;
        }

        $query->where('name', 'like', "%{$search}%");
    }

    protected function applyOrder(Builder $query, array $filters): void
    {
        $query->orderBy('id', 'asc');
    }
}
