<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TableEngine
{
    protected Builder $query;
    protected array $columns = [];
    protected array $filters = [];
    protected array $actions = [];
    protected array $bulkActions = [];
    protected array $emptyState = [];
    protected array $styling = [];
    protected int $perPage = 25;
    protected array $perPageOptions = [10, 25, 50, 100];
    protected ?string $defaultSortColumn = null;
    protected string $defaultSortDirection = 'asc';
    protected bool $searchable = false;
    protected bool $exportable = false;
    protected bool $ajax = false;
    protected ?Collection $data = null;
    protected ?Request $request = null;

    public function __construct(Builder $query)
    {
        $this->query = $query;
        $this->initializeDefaults();
    }

    public static function make(Builder $query): self
    {
        return new self($query);
    }

    protected function initializeDefaults(): void
    {
        $this->emptyState = [
            'icon' => 'file-deleted',
            'title' => 'Tidak Ada Data',
            'message' => 'Tidak ada data yang ditemukan.',
        ];

        $this->styling = [
            'table_class' => 'table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4',
            'header_class' => 'fw-bold text-muted',
            'row_class' => '',
            'cell_class' => '',
        ];
    }

    public function columns(array $columns): self
    {
        $this->columns = $columns;
        return $this;
    }

    public function filters(array $filters): self
    {
        $this->filters = $filters;
        return $this;
    }

    public function actions(array $actions): self
    {
        $this->actions = $actions;
        return $this;
    }

    public function bulkActions(array $bulkActions): self
    {
        $this->bulkActions = $bulkActions;
        return $this;
    }

    public function emptyState(array $emptyState): self
    {
        $this->emptyState = array_merge($this->emptyState, $emptyState);
        return $this;
    }

    public function styling(array $styling): self
    {
        $this->styling = array_merge($this->styling, $styling);
        return $this;
    }

    public function perPage(int $perPage): self
    {
        $this->perPage = $perPage;
        return $this;
    }

    public function perPageOptions(array $options): self
    {
        $this->perPageOptions = $options;
        return $this;
    }

    public function defaultSort(string $column, string $direction = 'asc'): self
    {
        $this->defaultSortColumn = $column;
        $this->defaultSortDirection = $direction;
        return $this;
    }

    public function searchable(bool $searchable = true): self
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function exportable(bool $exportable = true): self
    {
        $this->exportable = $exportable;
        return $this;
    }

    public function ajax(bool $ajax = true): self
    {
        $this->ajax = $ajax;
        return $this;
    }

    public function process(Request $request): self
    {
        $this->request = $request;

        // Apply filters
        $this->applyFilters();

        // Apply search
        $this->applySearch();

        // Apply sorting
        $this->applySorting();

        // Get paginated data
        $perPage = $request->get('per_page', $this->perPage);
        $this->data = $this->query->paginate($perPage)->withQueryString();

        return $this;
    }

    protected function applyFilters(): void
    {
        foreach ($this->filters as $filter) {
            $name = $filter['name'] ?? null;
            $type = $filter['type'] ?? 'text';

            if (!$name || !$this->request->filled($name)) {
                continue;
            }

            $value = $this->request->get($name);

            switch ($type) {
                case 'search':
                    $this->applySearchFilter($filter, $value);
                    break;
                case 'select':
                    $this->query->where($name, $value);
                    break;
                case 'date':
                    $this->query->whereDate($name, $value);
                    break;
                case 'daterange':
                    $this->applyDateRangeFilter($filter);
                    break;
                case 'numberrange':
                    $this->applyNumberRangeFilter($filter);
                    break;
                case 'checkbox':
                    $this->query->where($name, $value ? 1 : 0);
                    break;
            }
        }
    }

    protected function applySearchFilter(array $filter, string $value): void
    {
        $columns = $filter['columns'] ?? [];
        
        if (empty($columns)) {
            return;
        }

        $this->query->where(function ($query) use ($columns, $value) {
            foreach ($columns as $column) {
                if (Str::contains($column, '.')) {
                    // Relationship column
                    [$relation, $field] = explode('.', $column, 2);
                    $query->orWhereHas($relation, function ($q) use ($field, $value) {
                        $q->where($field, 'like', "%{$value}%");
                    });
                } else {
                    $query->orWhere($column, 'like', "%{$value}%");
                }
            }
        });
    }

    protected function applyDateRangeFilter(array $filter): void
    {
        $from = $filter['from'] ?? 'date_from';
        $to = $filter['to'] ?? 'date_to';
        $column = $filter['column'] ?? 'created_at';

        if ($this->request->filled($from)) {
            $this->query->whereDate($column, '>=', $this->request->get($from));
        }

        if ($this->request->filled($to)) {
            $this->query->whereDate($column, '<=', $this->request->get($to));
        }
    }

    protected function applyNumberRangeFilter(array $filter): void
    {
        $from = $filter['from'] ?? 'amount_from';
        $to = $filter['to'] ?? 'amount_to';
        $column = $filter['column'] ?? 'amount';

        if ($this->request->filled($from)) {
            $this->query->where($column, '>=', $this->request->get($from));
        }

        if ($this->request->filled($to)) {
            $this->query->where($column, '<=', $this->request->get($to));
        }
    }

    protected function applySearch(): void
    {
        if (!$this->searchable || !$this->request->filled('search')) {
            return;
        }

        $search = $this->request->get('search');
        $searchableColumns = collect($this->columns)
            ->filter(fn($col) => $col['searchable'] ?? false)
            ->pluck('key')
            ->toArray();

        if (empty($searchableColumns)) {
            return;
        }

        $this->query->where(function ($query) use ($searchableColumns, $search) {
            foreach ($searchableColumns as $column) {
                if (Str::contains($column, '.')) {
                    [$relation, $field] = explode('.', $column, 2);
                    $query->orWhereHas($relation, function ($q) use ($field, $search) {
                        $q->where($field, 'like', "%{$search}%");
                    });
                } else {
                    $query->orWhere($column, 'like', "%{$search}%");
                }
            }
        });
    }

    protected function applySorting(): void
    {
        $sortColumn = $this->request->get('sort', $this->defaultSortColumn);
        $sortDirection = $this->request->get('direction', $this->defaultSortDirection);

        if (!$sortColumn) {
            return;
        }

        // Validate sort direction
        $sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) 
            ? strtolower($sortDirection) 
            : 'asc';

        // Check if column is sortable
        $column = collect($this->columns)->firstWhere('key', $sortColumn);
        
        if (!$column || !($column['sortable'] ?? false)) {
            return;
        }

        // Handle relationship sorting
        if (Str::contains($sortColumn, '.')) {
            [$relation, $field] = explode('.', $sortColumn, 2);
            $this->query->orderBy(
                $this->query->getModel()->$relation()->getRelated()->getTable() . '.' . $field,
                $sortDirection
            );
        } else {
            $this->query->orderBy($sortColumn, $sortDirection);
        }
    }

    public function render(): string
    {
        return view('components.table-engine', [
            'columns' => $this->columns,
            'filters' => $this->filters,
            'actions' => $this->actions,
            'bulkActions' => $this->bulkActions,
            'data' => $this->data,
            'emptyState' => $this->emptyState,
            'styling' => $this->styling,
            'perPageOptions' => $this->perPageOptions,
            'searchable' => $this->searchable,
            'exportable' => $this->exportable,
            'ajax' => $this->ajax,
            'request' => $this->request,
        ])->render();
    }

    public function getData(): ?Collection
    {
        return $this->data;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getActions(): array
    {
        return $this->actions;
    }
}
