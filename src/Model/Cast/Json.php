<?php

declare(strict_types=1);

namespace Dsxwk\Framework\LaravelHelper\Model\Cast;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class Json implements CastsAttributes
{
    /**
     * 将取出的数据进行转换
     *
     * @param Model  $model
     * @param string $key
     * @param mixed  $value
     * @param array  $attributes
     *
     * @return array
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): array
    {
        return json_decode($value, true) ?? [];
    }

    /**
     * 转换成将要进行存储的值
     *
     * @param Model  $model
     * @param string $key
     * @param array  $value
     * @param array  $attributes
     *
     * @return string
     */
    public function set(Model $model, string $key, $value, array $attributes): string
    {
        return json_encode($value, JSON_UNESCAPED_UNICODE) ?? '[]';
    }
}