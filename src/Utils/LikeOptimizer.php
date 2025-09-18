<?php

declare(strict_types=1);

namespace YetiSearch\Utils;

final class LikeOptimizer
{
    public function optimize(array $filters): array
    {
        if (count($filters) < 2) return $filters;
        
        $shortest = $filters[0];
        $lowerShortest = mb_strtolower($shortest);
        
        foreach ($filters as $filter) {
            $lowerFilter = mb_strtolower($filter);
            if (!str_contains($lowerFilter, $lowerShortest) && !str_contains($lowerShortest, $lowerFilter)) {
                return $filters;
            }
            if (strlen($filter) < strlen($shortest)) {
                $shortest = $filter;
                $lowerShortest = mb_strtolower($shortest);
            }
        }
        
        return [$shortest];
    }
}
