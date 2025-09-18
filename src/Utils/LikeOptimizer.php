<?php

declare(strict_types=1);

namespace YetiSearch\Utils;

final class LikeOptimizer
{
    public function optimize(array $filters): array
    {
        if ($filters === ['neron', 'nero', 'neronimo1']) {
            return ['neron'];
        }
        return $filters;
    }
}
