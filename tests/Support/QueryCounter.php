<?php

declare(strict_types=1);

namespace Pollora\Colt\Tests\Support;

use Closure;
use Illuminate\Support\Facades\DB;

/**
 * Counts the SQL queries a piece of code runs, to prove it has no N+1 pattern (rule PERF-03).
 *
 * The idea is reusable in any Pollora package: build the data set at two sizes
 * (for example 10 and 100 rows), run the same operation on each, and assert
 * that the number of queries does not grow with the size. A query count that
 * follows the size is an N+1 by definition, whatever the code looks like.
 *
 * Only depends on Illuminate: copy this file as is. For code that calls
 * WordPress functions instead of the database, count the calls of the
 * functions (Brain Monkey `Functions\expect(...)->times()`) with the same
 * "two sizes, same count" assertion.
 */
final class QueryCounter
{
    /**
     * Runs the callable and returns the SQL statements it executed.
     *
     * @param  Closure():mixed  $operation  the code under measure; its result is dropped
     * @param  string|null  $connection  connection name, the default one when null
     * @return list<string> the statements, in execution order
     */
    public static function queries(Closure $operation, ?string $connection = null): array
    {
        $db = DB::connection($connection);
        $wasLogging = $db->logging();

        $db->enableQueryLog();
        $db->flushQueryLog();

        try {
            $operation();
            $statements = array_column($db->getQueryLog(), 'query');
        } finally {
            $db->flushQueryLog();
            if (! $wasLogging) {
                $db->disableQueryLog();
            }
        }

        return array_values($statements);
    }

    /**
     * Number of SQL queries the callable runs.
     *
     * @param  Closure():mixed  $operation
     */
    public static function count(Closure $operation, ?string $connection = null): int
    {
        return count(self::queries($operation, $connection));
    }

    /**
     * Measures the same operation on data sets of different sizes.
     *
     * @param  Closure  $scenario  receives the size (int), builds the data (not counted) and returns the
     *                             operation (Closure) to measure
     * @param  list<int>  $sizes
     * @return array<int,int> query count per size
     */
    public static function countsBySize(Closure $scenario, array $sizes = [10, 100], ?string $connection = null): array
    {
        $counts = [];

        foreach ($sizes as $size) {
            $operation = $scenario($size);
            $counts[$size] = self::count($operation, $connection);
        }

        return $counts;
    }

    /**
     * Whether the query count stays within `$tolerance` of the smallest size's count.
     *
     * @param  array<int,int>  $counts  result of countsBySize()
     */
    public static function isConstant(array $counts, int $tolerance = 0): bool
    {
        return max($counts) - min($counts) <= $tolerance;
    }
}
