<?php

namespace Dooracle\helper;

/**
 * oracle 分页起始位置
 * OFFSET $startNum ROWS FETCH NEXT $perNum ROWS ONLY
 */
function pagination_start($count, $perNum, $page)
{
    $pnums = @ceil($count / $perNum);
    if ($pnums === 1 || $page <= 1)
        return 0;
    if ($page >= $pnums)
        return (int)($pnums - 1) * $perNum;
    return (int)($page - 1) * $perNum;
}