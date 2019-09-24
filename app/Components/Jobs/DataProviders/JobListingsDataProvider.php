<?php

namespace App\Components\Jobs\DataProviders;

use App\Components\Jobs\Enums\JobStatuses;
use App\Components\Jobs\Enums\JobTaskStatuses;
use App\Components\Jobs\Enums\JobTaskTypes;
use App\Components\Jobs\Models\Job;
use App\Components\Teams\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Facades\DB;

/**
 * Class JobListingsDataProvider
 *
 * @package App\Components\Jobs\DataProviders
 */
class JobListingsDataProvider
{
    /**
     * Returns query that returns jobs for folder "No contact 24 hours".
     *
     * @param int $userId User id.
     *
     * @return Builder
     */
    public function getNoContact24HoursQuery(int $userId): Builder
    {
        $query = Job::query()
            ->select($this->getFieldsForListing())
            ->whereNotIn('id', $this->getLatestStatusSubquery(JobStatuses::$closedStatuses))
            ->whereIn('assigned_location_id', function (QueryBuilder $query) use ($userId) {
                $query->select('location_id')
                    ->from('location_user')
                    ->where('user_id', $userId);
            })
            ->whereExists(function (QueryBuilder $query) {
                $query->select('id')
                    ->from('job_tasks')
                    ->whereRaw('job_tasks.job_id=jobs.id')
                    ->where('name', JobTaskTypes::INITIAL_CONTACT_KPI)
                    ->whereNotNull('kpi_missed_at')
                    ->whereRaw('kpi_missed_at < NOW()');
            });

        return $this->orderListQuery($query);
    }

    /**
     * Returns query to retrieve jobs for inbox (there is unread incoming message and
     * job is not assigned to user or team).
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function getInboxQuery()
    {
        return Job::query()
            ->select($this->getFieldsForListing())
            ->where(function (Builder $query) {
                $query
                    ->orWhereNotNull('pinned_at')
                    ->orWhere(function (Builder $query) {
                        $query
                            ->whereNotExists(function (QueryBuilder $query) {
                                $query->select('team_id')
                                    ->from('job_team_assignments')
                                    ->whereRaw('job_team_assignments.job_id = jobs.id');
                            })
                            ->whereNotExists(function (QueryBuilder $query) {
                                $query->select('user_id')
                                    ->from('job_user_assignments')
                                    ->whereRaw('job_user_assignments.job_id = jobs.id');
                            });
                    });
            })
            ->whereIn('id', function (QueryBuilder $query) {
                return $query->select('id')
                    ->from(
                        DB::raw('(SELECT jobs.id,
                               (
                                 SELECT status
                                 FROM job_statuses
                                 WHERE job_id = jobs.id
                                 ORDER BY created_at DESC, id DESC
                                 LIMIT 1
                               ) AS latest_status
                        FROM jobs) subQuery')
                    )
                    ->whereNotIn('latest_status', JobStatuses::$closedStatuses);
            });
    }

    /**
     * Returns team with number of jobs assigned to it.
     *
     * @param int $teamId Team id.
     *
     * @return array
     */
    public function getTeam(int $teamId = null): array
    {
        $query = DB::query()
            ->from('teams')
            ->join('job_team_assignments', function (JoinClause $join) use ($teamId) {
                $join->on('job_team_assignments.team_id', '=', 'teams.id')
                    ->where('teams.id', '=', $teamId);
            })
            ->leftJoin('jobs', 'job_team_assignments.job_id', '=', 'jobs.id')
            ->where('teams.id', $teamId)
            ->whereRaw('jobs.deleted_at IS NULL')
            ->whereNotIn('jobs.id', $this->getLatestStatusSubquery(JobStatuses::$closedStatuses));

        $team               = Team::findOrFail($teamId)->toArray();
        $team['jobs_count'] = $query->count();

        return $team;
    }

    /**
     * Returns teams with number of jobs assigned to each.
     * Optionally allows to only include teams which specific user is member of.
     *
     * @param int|null $userId User id.
     *
     * @return QueryBuilder
     */
    public function getTeams(?int $userId = null): QueryBuilder
    {
        $query = DB::query()
            ->select([
                'teams.id',
                'teams.name',
                DB::raw('count(jobs.id) as jobs_count'),
            ])
            ->from('teams');

        if (null !== $userId) {
            $query->join('team_user', function (JoinClause $join) use ($userId) {
                $join->on('team_user.team_id', '=', 'teams.id')
                    ->where('team_user.user_id', '=', $userId);
            });
        }

        $query
            ->leftJoin('job_team_assignments', 'job_team_assignments.team_id', '=', 'teams.id')
            ->leftJoin('jobs', 'job_team_assignments.job_id', '=', 'jobs.id')
            ->whereRaw('jobs.deleted_at IS NULL')
            ->whereNotIn('jobs.id', $this->getLatestStatusSubquery(JobStatuses::$closedStatuses))
            ->groupBy('teams.id', 'teams.name');

        return $query;
    }

    /**
     * Returns query based on unread incoming messages.
     *
     * @return \Illuminate\Database\Query\Expression
     */
    private function getHasNewRepliesQuery()
    {
        $hasRepliesQuery = DB::query()
            ->select('message_id')
            ->from('job_messages')
            ->leftJoin('messages', 'messages.id', '=', 'job_messages.message_id')
            ->whereRaw('job_messages.job_id=jobs.id AND job_messages.deleted_at IS NULL')
            ->whereRaw('messages.is_incoming IS true')
            ->whereNull('read_at')
            ->toSql();

        return DB::raw(
            sprintf('(EXISTS(%s)) AS has_new_replies', $hasRepliesQuery)
        );
    }

    /**
     * Returns site contact query if assigned. Uses full name for person and trading_name for company.
     *
     * @return \Illuminate\Database\Query\Expression
     */
    private function getSiteContactQuery()
    {
        $siteContactQuery = DB::query()
            ->selectRaw('COALESCE(
                NULLIF(
                    TRIM(CONCAT_WS(\' \', contact_person_profiles.first_name, contact_person_profiles.last_name)),
                    \'\'
                ),
                contact_company_profiles.legal_name
            )')
            ->from('job_contact_assignments')
            ->leftJoin(
                'job_contact_assignment_types',
                'job_contact_assignment_types.id',
                '=',
                'job_contact_assignments.job_assignment_type_id'
            )
            ->leftJoin(
                'contact_person_profiles',
                'contact_person_profiles.contact_id',
                '=',
                'job_contact_assignments.assignee_contact_id'
            )
            ->leftJoin(
                'contact_company_profiles',
                'contact_company_profiles.contact_id',
                '=',
                'job_contact_assignments.assignee_contact_id'
            )
            ->whereRaw('job_contact_assignment_types.is_unique IS true')
            ->whereRaw('job_contact_assignments.job_id = jobs.id')
            ->limit(1)
            ->toSql();

        return DB::raw(
            sprintf('(%s) AS site_contact_name', $siteContactQuery)
        );
    }

    /**
     * Returns query to retrieve jobs matching to the user's locations.
     *
     * @param int $userId User id.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function getLocalQuery(int $userId)
    {
        $query = Job::query()
            ->select($this->getFieldsForListing())
            ->whereIn('assigned_location_id', function (QueryBuilder $query) use ($userId) {
                $query->select('location_id')
                    ->from('location_user')
                    ->where('user_id', $userId);
            })
            ->orWhereIn('owner_location_id', function (QueryBuilder $query) use ($userId) {
                $query->select('location_id')
                    ->from('location_user')
                    ->where('user_id', $userId);
            });

        return $this->orderListQuery($query);
    }

    /**
     * Returns query to retrieve jobs assigned to given user.
     *
     * @param int $userId User id.
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder
     */
    public function getMineQuery(int $userId)
    {
        $query = Job::query()
            ->select($this->getFieldsForListing())
            ->whereNotIn('id', $this->getLatestStatusSubquery(JobStatuses::$closedStatuses))
            ->where(function (Builder $query) use ($userId) {
                $query
                    ->whereExists(function (QueryBuilder $query) use ($userId) {
                        $query->select('user_id')
                            ->from('job_user_assignments')
                            ->whereRaw('job_user_assignments.job_id = jobs.id')
                            ->where('job_user_assignments.user_id', $userId);
                    })
                    ->orWhereExists(function (QueryBuilder $query) use ($userId) {
                        $query->select('*')
                            ->from('job_team_assignments')
                            ->leftJoin(
                                'team_user',
                                'team_user.team_id',
                                '=',
                                'job_team_assignments.team_id'
                            )
                            ->whereRaw('job_team_assignments.job_id = jobs.id')
                            ->where('team_user.user_id', $userId);
                    });
            });

        return $this->orderListQuery($query);
    }

    /**
     * Returns query to retrieve active jobs assigned to given user.
     *
     * @param int $userId User id.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getActiveQuery(int $userId)
    {
        $query = Job::query()
            ->select($this->getFieldsForListing())
            ->whereIn('id', $this->getLatestStatusSubquery(JobStatuses::$activeStatuses))
            ->whereIn('assigned_location_id', function (QueryBuilder $query) use ($userId) {
                $query->select('location_id')
                    ->from('location_user')
                    ->where('user_id', $userId);
            });

        return $this->orderListQuery($query);
    }

    /**
     * Returns query to retrieve closed jobs assigned to given user.
     *
     * @param int $userId User id.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getClosedQuery(int $userId)
    {
        $query = Job::query()
            ->select($this->getFieldsForListing())
            ->whereIn('id', $this->getLatestStatusSubquery(JobStatuses::$closedStatuses))
            ->whereIn('assigned_location_id', function (QueryBuilder $query) use ($userId) {
                $query->select('location_id')
                    ->from('location_user')
                    ->where('user_id', $userId);
            });

        return $this->orderListQuery($query);
    }

    /**
     * Returns sub query that returns ids of jobs with given status.
     *
     * @param array $statusList
     *
     * @return \Closure
     */
    private function getLatestStatusSubquery(array $statusList)
    {
        return function (QueryBuilder $query) use ($statusList) {
            return $query
                ->select('id')
                ->from(
                    DB::raw('(SELECT jobs.id,
                               (
                                 SELECT status
                                 FROM job_statuses
                                 WHERE job_id = jobs.id
                                 ORDER BY created_at DESC, id DESC
                                 LIMIT 1
                               ) AS latest_status
                        FROM jobs) subQuery')
                )
                ->whereIn('latest_status', $statusList);
        };
    }

    /**
     * Returns query that returns jobs for the folder "Upcoming Kpi".
     *
     * @param int $userId User id.
     *
     * @return Builder
     */
    public function getUpcomingKpiQuery(int $userId): Builder
    {
        $query = Job::query()
            ->select($this->getFieldsForListing())
            ->whereNotIn('id', $this->getLatestStatusSubquery(JobStatuses::$closedStatuses))
            ->whereIn('assigned_location_id', function (QueryBuilder $query) use ($userId) {
                $query->select('location_id')
                    ->from('location_user')
                    ->where('user_id', $userId);
            })
            ->whereExists(function (QueryBuilder $query) {
                $query->select('id')
                    ->from('job_tasks')
                    ->whereRaw('job_tasks.job_id=jobs.id')
                    ->whereIn('id', function (QueryBuilder $query) {
                        return $query
                            ->select('id')
                            ->from(
                                DB::raw('(SELECT job_tasks.id,
                               (
                                 SELECT status
                                 FROM job_task_statuses
                                 WHERE job_task_id = job_tasks.id
                                 ORDER BY created_at DESC, id DESC
                                 LIMIT 1
                               ) AS latest_status
                        FROM job_tasks) subQuery')
                            )
                            ->where('latest_status', JobTaskStatuses::ACTIVE);
                    })
                    ->whereNotNull('kpi_missed_at')
                    ->whereRaw('created_at < NOW()')
                    ->whereRaw('NOW() < kpi_missed_at');
            });

        return $this->orderListQuery($query);
    }

    /**
     * Returns list of jobs assigned to specified team.
     *
     * @param int $teamId Team id.
     *
     * @return Builder
     */
    public function getByTeam(int $teamId): Builder
    {
        $query = Job::query()
            ->with('nextTask')
            ->select($this->getFieldsForListing())
            ->whereNotIn('id', $this->getLatestStatusSubquery(JobStatuses::$closedStatuses))
            ->whereHas('assignedTeams', function (Builder $query) use ($teamId) {
                $query->where('team_id', $teamId);
            });

        return $this->orderListQuery($query);
    }

    /**
     * @return array
     */
    private function getFieldsForListing(): array
    {
        return [
            'id',
            'claim_number',
            'insurer_id',
            'touched_at',
            'site_address_id',
            'pinned_at',
            'description',
            'assigned_location_id',
            'last_message',
            'snoozed_until',
            $this->getHasNewRepliesQuery(),
            $this->getSiteContactQuery(),
        ];
    }

    /**
     * Applies ordering condition to the given job list query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function orderListQuery(Builder $query): Builder
    {
        return $query->orderByRaw('pinned_at DESC nulls last, touched_at DESC');
    }
}
