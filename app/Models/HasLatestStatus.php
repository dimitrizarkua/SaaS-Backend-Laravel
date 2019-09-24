<?php

namespace App\Models;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Trait HasLatestStatus
 *
 * @package App\Models
 */
trait HasLatestStatus
{
    /**
     * @param string $table      Target table: jobs, job_tasks etc.
     * @param array  $statusList List of statuses.
     *
     * @return \Closure
     */
    protected function getEntityIdsWhereLatestStatusIs(string $table, array $statusList)
    {
        $table = strtolower(str_singular($table));

        $sql = "(SELECT {$table}s.id,  (
                  SELECT status FROM {$table}_statuses
                    WHERE {$table}_id = {$table}s.id
                    ORDER BY created_at DESC, id DESC
                    LIMIT 1
                 ) AS latest_status
                 FROM {$table}s) subQuery";

        return function (Builder $query) use ($sql, $statusList) {
            return $query
                ->select('id')
                ->from(DB::raw($sql))
                ->whereIn('latest_status', $statusList);
        };
    }
}
