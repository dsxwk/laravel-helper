<?php

declare(strict_types=1);

namespace Dsxwk\Framework\Laravel\Model\Trait;

use Dsxwk\Framework\LaravelHelper\Orm\QueryBuilder;
use Dsxwk\Framework\LaravelHelper\Param\PageDataParam;

/**
 * 模型助手 trait
 *
 * @method static QueryBuilder query() 获取查询构建器
 * @method static PageDataParam pageData(array $columns = ['*']) 获取分页数据
 * @method static updates(array $data = [], string $primaryKey = 'id', array $where = []) 批量更新
 * @method static batchUpdates(array $data = [], string $primaryKey = 'id', array $where = []) 批量更新
 * @method static deletes(string $column = 'id', array $values = [], array $where = []) 批量删除
 */
trait ModelHelper
{
    /**
     * 是否使用驼峰字段
     *
     * @return bool
     */
    private function isCamel(): bool
    {
        return static::$isCamel ?? false;
    }

    /**
     * @param $query
     *
     * @return QueryBuilder
     */
    public function newEloquentBuilder($query): QueryBuilder
    {
        return new QueryBuilder($query);
    }
}
