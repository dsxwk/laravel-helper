<?php

declare(strict_types=1);

namespace Dsxwk\Framework\LaravelHelper\Model;

use Dsxwk\Framework\LaravelHelper\Model\Trait\ModelHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

abstract class BaseModel extends Model
{
    use ModelHelper;

    protected $guarded = [];

    /**
     * 是否使用驼峰命名
     *
     * @var bool
     */
    public static bool $isCamel = true;

    /**
     * 重定义主键，默认是id
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 否自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * 创建时间字段名
     *
     * @var string
     */
    const CREATED_AT = 'created_at';

    /**
     * 更新时间字段名
     *
     * @var string
     */
    const UPDATED_AT = 'updated_at';

    /**
     * 删除时间字段名
     *
     * @var string
     */
    const DELETED_AT = 'deleted_at';

    public function getFillable(): array
    {
        if ($this->isCamel()) {
            $fillable = [];
            foreach (parent::getFillable() as $key) {
                $fillable[] = Str::camel($key);
            }

            return $fillable;
        } else {
            return $this->fillable;
        }
    }

    public function toArray(): array
    {
        if ($this->isCamel()) {
            $result = [];

            foreach (parent::toArray() as $key => $value) {
                $newKey = is_string($key) ? Str::camel($key) : $key;
                if (is_array($value)) {
                    $result[$newKey] = keysToCamelOrSnake($value, false);
                } else {
                    $result[$newKey] = $value;
                }
            }

            return $result;
        } else {
            return parent::toArray();
        }
    }
}