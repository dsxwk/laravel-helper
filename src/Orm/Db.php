<?php

declare(strict_types=1);

namespace Dsxwk\Framework\LaravelHelper\Orm;

use Dsxwk\Framework\Utils\Query\Handle;
use Illuminate\Database\Query\Expression;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Capsule\Manager as Capsule;
use DateTime;
use Closure;

/**
 * Class Db
 *
 * @package support
 * @method static array select(string $query, $bindings = [], $useReadPdo = true)
 * @method static int insert(string $query, $bindings = [])
 * @method static int update(string $query, $bindings = [])
 * @method static int delete(string $query, $bindings = [])
 * @method static bool statement(string $query, $bindings = [])
 * @method static mixed transaction(Closure $callback, $attempts = 1)
 * @method static void beginTransaction()
 * @method static void rollBack($toLevel = null)
 * @method static void commit()
 * @method static Expression raw($value)
 */
class Db extends Capsule
{
    /**
     * 初始化
     *
     * @return void
     */
    public static function init(): void
    {
        $_this = new self();
        $_this->addConnection(config('database.connections.' . config('database.default', 'mysql')) ?? []);
        $_this->setEventDispatcher(new Dispatcher(new Container()));
        $_this->setAsGlobal();
        $_this->bootEloquent();

        // 监听 SQL 执行事件
        $_this->getEventDispatcher()->listen(
            QueryExecuted::class,
            function (QueryExecuted $query) {
                $sql      = $query->sql;
                $bindings = $query->bindings;
                $time     = $query->time . ' ms';

                // 先格式化绑定值
                $bindings = array_map([self::class, 'formatBinding'], $bindings);

                // 只替换 WHERE / IN 部分的问号
                $sql = preg_replace_callback(
                    '/\bWHERE\b(.*)$/is',
                    function ($matches) use ($bindings) {
                        $wherePart = $matches[1];

                        // 替换 % -> %%
                        $wherePart = str_replace('%', '%%', $wherePart);

                        // 顺序替换 ? -> %s
                        foreach ($bindings as $binding) {
                            if (str_contains($wherePart, '?')) {
                                $wherePart = preg_replace('/\?/', $binding, $wherePart, 1);
                            }
                        }

                        return 'WHERE' . $wherePart;
                    },
                    $sql
                );

                $sqlRecord = [
                    'sql'  => $sql,
                    'time' => $time
                ];
                Handle::setSqlRecord($sqlRecord);
            }
        );
    }

    /**
     * 格式化绑定值
     *
     * @param mixed $binding
     *
     * @return string
     */
    private static function formatBinding(mixed $binding): string
    {
        if ($binding instanceof DateTime) {
            return "'" . $binding->format('Y-m-d H:i:s') . "'";
        }
        if (is_string($binding)) {
            return "'" . addslashes($binding) . "'";
        }
        if (is_null($binding)) {
            return 'NULL';
        }

        return (string)$binding;
    }
}