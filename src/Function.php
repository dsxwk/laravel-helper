<?php

declare(strict_types=1);

use Dsxwk\Framework\LaravelHelper\Orm\Db;
use Illuminate\Database\Eloquent\Model;

// 公共函数
if (!function_exists('getTablePrefix')) {
    /**
     * 获取表前缀
     *
     * @return string
     */
    function getTablePrefix(): string
    {
        return Db::connection()->getTablePrefix();
    }
}

if (!function_exists('getTable')) {
    /**
     * 获取表名
     *
     * @param $model
     *
     * @return string
     */
    function getTable($model): string
    {
        if (!($model instanceof Model)) {
            $model = new $model();
        }

        return getTablePrefix() . $model->getTable();
    }
}