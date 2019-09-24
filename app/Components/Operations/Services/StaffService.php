<?php

namespace App\Components\Operations\Services;

use App\Components\Jobs\Models\JobTask;
use App\Components\Locations\Models\Location;
use App\Components\Operations\Interfaces\StaffServiceInterface;
use App\Components\Operations\StaffSearchRule;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class StaffService
 *
 * @package App\Components\Operations\Services
 */
class StaffService implements StaffServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function searchForStaff(int $locationId, Carbon $date, ?string $name, int $limit = 10): Collection
    {
        $query = User::search([
            'location_id' => $locationId,
            'name'        => $name ?? '',
        ])
            ->rule(StaffSearchRule::class)
            ->take($limit);

        $elasticUsers = Collection::make(mapElasticResults($query->raw()));
        $users        = User::whereIn('id', $elasticUsers->pluck('id'))->get();

        return $this->getUsersWithWorkHours($users, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function listLocationStaff(Carbon $date, int $locationId): Collection
    {
        $users = Location::with('users')->findOrFail($locationId)->users;

        return $this->getUsersWithWorkHours($users, $date);
    }

    /**
     * {@inheritdoc}
     */
    public function getStaff(int $userId, Carbon $date): User
    {
        $user = User::findOrFail($userId);

        return $this->getUsersWithWorkHours(Collection::wrap($user), $date)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getUsersWithWorkHours(Collection $users, Carbon $date): Collection
    {
        $userIds = $users->pluck('id');
        $timeSql = 'DATE_PART(\'hours\', ends_at - starts_at) + DATE_PART(\'minutes\', ends_at - starts_at) / 60';

        $weekHours = JobTask::scheduledForWeek($date)
            ->select([
                'date',
                DB::raw('crew_user_id AS user_id'),
                DB::raw(sprintf('%s AS duration', $timeSql)),
            ])
            ->join('job_task_crew_assignments', 'job_task_id', '=', 'job_tasks.id')
            ->whereIn('crew_user_id', $userIds)
            ->get()
            ->groupBy('user_id');

        $results = $users->map(function ($item) use ($date, $weekHours) {
            $item['week_hours'] = 0;
            $item['date_hours'] = 0;

            $userId = $item['id'];
            if ($weekHours->has($userId)) {
                foreach ($weekHours[$userId] as $taskHours) {
                    $taskDuration = (float)$taskHours['duration'];
                    $taskDate     = Carbon::make($taskHours['date']);

                    $item['week_hours'] += $taskDuration;
                    if ($date->eq($taskDate)) {
                        $item['date_hours'] += $taskDuration;
                    }
                }
            }

            return $item;
        });

        return $results;
    }
}
