<?php

namespace App\Components\AssessmentReports\Policies;

use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\RBAC\Interfaces\UsersServiceInterface;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Class AssessmentReportPolicy
 *
 * @package App\Components\AssessmentReports\Policies
 */
class AssessmentReportPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user allows to change status of cancelled assessment report.
     *
     * @param User             $user
     * @param AssessmentReport $assessmentReport
     *
     * @return boolean
     */
    public function manageCancelled(User $user, AssessmentReport $assessmentReport): bool
    {
        if ($assessmentReport->isCancelled()) {
            return app()->make(UsersServiceInterface::class)
                ->hasPermission($user->id, 'assessment_reports.manage_cancelled');
        }

        return true;
    }
}
