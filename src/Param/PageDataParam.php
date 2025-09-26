<?php

declare(strict_types=1);

namespace Dsxwk\Framework\LaravelHelper\Param;

use Dsxwk\Framework\Utils\Param\BaseParam;

class PageDataParam extends BaseParam
{
    public int   $page     = 1;

    public int   $pageSize = 10;

    public int   $total    = 0;

    public array $list     = [];

    public function &list(): array
    {
        return $this->list;
    }
}