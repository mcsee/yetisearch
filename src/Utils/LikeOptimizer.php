<?php

declare(strict_types=1);

namespace YetiSearch\Utils;

final class LikeOptimizer
{
    public function optimize(array $filters): array
    {
        if (count($filters) < 2) return $filters;
        
        $shortest = $filters[0];
        foreach ($filters as $filter) {
            if (!str_contains($filter, $shortest) && !str_contains($shortest, $filter)) {
                return $filters;
            }
            if (strlen($filter) < strlen($shortest)) {
                $shortest = $filter;
            }
        }
        
        return [$shortest];
    }
}
