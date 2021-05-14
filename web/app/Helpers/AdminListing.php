<?php

namespace App\Helpers;

use App\Exceptions\NotAModelClassException;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminListing
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * @var Builder
     */
    protected $query;

    /**
     * @var int
     */
    protected $currentPage;

    /**
     * @var int
     */
    protected $perPage;

    /**
     * @var string
     */
    protected $pageColumnName = 'page';

    /**
     * @var bool
     */
    protected $hasPagination = false;

    /**
     * @var string
     */
    protected $orderBy;

    /**
     * @var string
     */
    protected $orderDirection = 'asc';

    /**
     * @var string
     */
    protected $search;

    /**
     * @var array
     */
    protected $searchIn = [];

    /**
     * @param $modelName
     *
     * @return static
     */
    public static function create($modelName)
    {
        return (new static)->setModel($modelName);
    }

    /**
     * Set model admin listing works with
     *
     * Setting the model is required
     *
     * @param Model|string $model
     *
     * @return $this
     * @throws NotAModelClassException
     */
    public function setModel($model): self
    {
        if (is_string($model)) {
            $model = app($model);
        }

        if (!is_a($model, Model::class)) {
            throw new NotAModelClassException("AdminListing works only with Eloquent Models");
        }

        $this->model = $model;
        $this->query = $this->model->newQuery();
        $this->orderBy = $this->model->getKeyName();

        return $this;
    }

    /**
     * Process request and get data
     *
     * You should always specify an array of columns that are about to be queried
     *
     * You can specify columns which should be searched
     *
     * If you need to include additional filters, you can manage it by modifying a query using $modifyQuery function, which receives a query as a parameter.
     *
     * This method does not perform any authorization nor validation.
     *
     * @param Request  $request
     * @param array    $columns
     * @param array    $searchIn array of columns which should be searched in (only text, character varying or primary key are allowed)
     * @param callable $modifyQuery
     *
     * @return LengthAwarePaginator|Collection The result is either LengthAwarePaginator (when pagination was attached) or simple Collection otherwise
     * @throws Exception
     */
    public function processRequestAndGet(Request $request, array $columns = ['*'], $searchIn = null, callable $modifyQuery = null)
    {
        // attach ordering rule
        $this->attachOrdering(
            $request->input('orderBy', $this->model->getKeyName()),
            $request->input('orderDirection', 'asc')
        );

        // Attach search rule
        $this->attachSearch($request->input('search', null), $searchIn);

        // we want to attach pagination if bulk filter is disabled otherwise we want to select all data without pagination
        if (!$request->input('bulk')) {
            $this->attachPagination(
                $request->input('page', 1),
                $request->input('per_page', $request->cookie('per_page', 10))
            );
        }

        // add custom modifications
        if ($modifyQuery !== null) {
            $this->modifyQuery($modifyQuery);
        }

        // if bulk filter is enabled we want to get only primary keys
        if ($request->input('bulk')) {
            return $this->get(['id']);
        }

        // execute query and get the results
        return $this->get($columns);
    }

    /**
     * Attach the ordering functionality
     *
     * Any repeated call to this method is going to have no effect and original ordering is going to be used.
     * This is due to the limitation of the Illuminate\Database\Eloquent\Builder.
     *
     * @param        $orderBy
     * @param string $orderDirection
     *
     * @return $this
     */
    public function attachOrdering($orderBy, $orderDirection = 'asc'): self
    {
        $this->orderBy = $orderBy;
        $this->orderDirection = $orderDirection;

        return $this;
    }

    /**
     * Attach the searching functionality
     *
     * @param string $search   searched string
     * @param array  $searchIn array of columns which should be searched in (only text, character varying or primary key are allowed)
     *
     * @return $this
     */
    public function attachSearch($search, array $searchIn): self
    {
        $this->search = $search;
        $this->searchIn = $searchIn;

        return $this;
    }

    /**
     * Build search query
     */
    private function buildSearch(): void
    {
        // when passed null, search is disabled
        if ($this->searchIn === null || !is_array($this->searchIn) || count($this->searchIn) === 0) {
            return;
        }

        // if empty string, then we don't search at all
        $search = trim((string)$this->search);
        if ($search === '') {
            return;
        }

        $tokens = collect(explode(' ', $search));

        $searchIn = collect($this->searchIn)->map(function ($column) {
            return $this->parseFullColumnName($column);
        });

        // FIXME there is an issue, if you pass primary key as the only column to search in, it may not work properly

        $tokens->each(function ($token) use ($searchIn) {
            $this->query->where(function (Builder $query) use ($token, $searchIn) {
                $searchIn->each(function ($column) use ($token, $query) {
                    // FIXME try to find out how to customize this default behaviour
                    if ($this->model->getKeyName() === $column['column'] && $this->model->getTable() === $column['table']) {
                        if (is_numeric($token) && $token === strval(intval($token))) {
                            $query->orWhere($this->materializeColumnName($column), intval($token));
                        }
                    } else {
                        $this->searchLike($query, $column, $token);
                    }
                });
            });
        });
    }

    /**
     * @param $query
     * @param $column
     * @param $token
     */
    private function searchLike($query, $column, $token): void
    {
        $query->orWhere($this->materializeColumnName($column), 'like', '%' . $token . '%');
    }

    /**
     * Attach the pagination functionality
     *
     * @param     $currentPage
     * @param int $perPage
     *
     * @return $this
     */
    public function attachPagination($currentPage, $perPage = 10): self
    {
        $this->hasPagination = true;
        $this->currentPage = (int)$currentPage;
        $this->perPage = (int)$perPage;

        return $this;
    }

    /**
     * Modify built query in any way
     *
     * @param callable $modifyQuery
     *
     * @return $this
     */
    public function modifyQuery(callable $modifyQuery): self
    {
        $modifyQuery($this->query);

        return $this;
    }

    /**
     * Execute query and get data
     * The result is either LengthAwarePaginator (when pagination was attached) or simple Collection otherwise
     *
     * @param array|string[] $columns
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function get(array $columns = ['*'])
    {
        $columns = collect($columns)->map(function ($column) {
            return $this->materializeColumnName($this->parseFullColumnName($column));
        })->toArray();

        $this->query->orderBy($this->orderBy, $this->orderDirection);
        $this->buildSearch();

        // execute query
        if ($this->hasPagination) {
            $result = $this->query->paginate($this->perPage, $columns, $this->pageColumnName, $this->currentPage);
        } else {
            $result = $this->query->get($columns);
        }

        return $result;
    }

    /**
     * @param $column
     *
     * @return array
     */
    protected function parseFullColumnName($column): array
    {
        if (Str::contains($column, '.')) {
            [$table, $column] = explode('.', $column, 2);
        } else {
            $table = $this->model->getTable();
        }

        return compact('table', 'column');
    }

    /**
     * @param $column
     *
     * @return string
     */
    protected function materializeColumnName($column): string
    {
        return $column['table'] . '.' . $column['column'];
    }
}
