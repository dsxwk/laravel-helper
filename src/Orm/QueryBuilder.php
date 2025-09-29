<?php

declare(strict_types=1);

namespace Dsxwk\Framework\LaravelHelper\Orm;

use Dsxwk\Framework\LaravelHelper\Param\PageDataParam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Exception;

class QueryBuilder extends Builder
{
    /**
     * 是否使用驼峰字段
     *
     * @return bool
     */
    private function isCamel(): bool
    {
        return property_exists($this->model, 'isCamel') && $this->model::${'isCamel'} ?? false;
    }

    /**
     * 分页数据
     *
     * @param array $columns
     *
     * @return PageDataParam
     */
    public function pageData(array $columns = ['*']): PageDataParam
    {
        $page     = (int)request()->input('page', 1);
        $pageSize = (int)request()->input('pageSize', 10);

        return new PageDataParam(
            [
                'page'     => $page,
                'pageSize' => $pageSize,
                'total'    => $this->count(),
                'list'     => $this->forPage($page, $pageSize)->get($columns)->toArray()
            ]
        );
    }

    /**
     * 创建数据(支持驼峰转换)
     *
     * @param array $attributes
     *
     * @return Model
     */
    public function create(array $attributes = []): Model
    {
        if ($this->isCamel()) $attributes = keysToCamelOrSnake($attributes);

        return parent::create($attributes);
    }

    /**
     * 更新数据(支持驼峰转换)
     *
     * @param array $values
     *
     * @return int
     */
    public function update(array $values = []): int
    {
        if ($this->isCamel()) $values = keysToCamelOrSnake($values);

        return parent::update($values);
    }

    /**
     * 插入数据
     *
     * @param array $values
     *
     * @return bool
     */
    public function insert(array $values = []): bool
    {
        /**
         * @var Model $model
         */
        $model = $this->getModel();

        $datetime = date('Y-m-d H:i:s');
        if ($this->isCamel()) {
            foreach ($values as $key => $value) {
                foreach ($value as $k => $v) {
                    $values[$key][$model::CREATED_AT]                      = $datetime;
                    $values[$key][$model::UPDATED_AT]                      = $datetime;
                    $values[$key][$this->isCamel() ? Str::snake($k) : $k] = $v;
                }
            }
        }

        return parent::insert($values);
    }

    /**
     * 批量更新
     *
     * @param array  $data
     * @param string $primaryKey
     * @param array  $where
     *
     * @return int
     * @throws Exception
     */
    public function updates(array $data = [], string $primaryKey = 'id', array $where = []): int
    {
        /**
         * @var Model $model
         */
        $model = $this->getModel();

        $pk = $primaryKey;
        if (!$this->isCamel()) $pk = Str::snake($primaryKey);
        if (empty($data)) throw new Exception('The data is empty of batchUpdate');
        $dateTime = date('Y-m-d H:i:s');
        $caseWhen = [];
        foreach ($data as $key => $row) {
            foreach ($row as $column => $value) {
                if ($this->isCamel()) $column = Str::snake($column);
                $value = (is_array($value) || is_object($value)) ? json_encode(
                    $value,
                    JSON_UNESCAPED_UNICODE
                ) : $value;
                if ($column === $primaryKey) continue;
                $caseWhen[$column][$key]            = "WHEN {$pk} = '{$row[$primaryKey]}' THEN '{$value}'";
                $caseWhen[$model::UPDATED_AT][$key] = "WHEN {$pk} = '$row[$primaryKey]' THEN '{$dateTime}'";
            }
        }

        $q = $model::query()->whereIn($pk, array_column($data, $primaryKey))->where($where);
        foreach ($caseWhen as $key => &$item) {
            $item = DB::raw('CASE ' . implode(' ', $item) . " ELSE `{$key}` END ");
        }

        /**
         * @var Builder $q
         */
        return $q->update($caseWhen, false);
    }

    /**
     * 批量更新
     *
     * @param array  $data
     * @param string $primaryKey
     * @param array  $where
     *
     * @return int|bool
     * @throws Exception
     */
    public function batchUpdates(array $data = [], string $primaryKey = 'id', array $where = []): int|bool
    {
        /**
         * @var Model $model
         */
        $model = $this->getModel();

        $primaryKey = Str::snake($primaryKey);
        if (empty($data)) throw new Exception('The data is empty of batchUpdate');
        $dateTime = date('Y-m-d H:i:s');

        $values = [];
        foreach ($data as $key => $row) {
            foreach ($row as $column => $value) {
                if ($this->isCamel()) $column = Str::snake($column);
                $value                 = (is_array($value) || is_object($value)) ? json_encode(
                    $value,
                    JSON_UNESCAPED_UNICODE
                ) : $value;
                $values[$key][$column] = $value;
            }
            $values[$key][$model::UPDATED_AT] = $dateTime;
        }

        return Db::statement(batchUpdateSql(getTable($model::class), $values, $primaryKey, $where));
    }

    /**
     * 批量删除
     *
     * @param string $column
     * @param array  $values
     * @param array  $where
     *
     * @return mixed
     */
    public function deletes(string $column = 'id', array $values = [], array $where = []): mixed
    {
        /**
         * @var Model $model
         */
        $model = $this->getModel();

        return $model::query()->whereIn($column, $values)->where($where)->delete();
    }
}