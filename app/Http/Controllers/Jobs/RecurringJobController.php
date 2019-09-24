<?php

namespace App\Http\Controllers\Jobs;

use App\Components\Jobs\Models\RecurringJob;
use App\Components\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Http\Requests\Jobs\CreateRecurringJobRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Jobs\RecurringJobListResponse;
use App\Http\Responses\Jobs\RecurringJobResponse;
use App\Jobs\RecurringJobs\ProcessRecurringJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Class RecurringJobController
 *
 * @package App\Http\Controllers
 */
class RecurringJobController extends Controller
{
    /**
     * @OA\Get(
     *      path="/jobs/recurring",
     *      tags={"Jobs"},
     *      security={{"passport": {}}},
     *      summary="Returns list of recurring jobs.",
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/RecurringJobListResponse")
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('jobs.manage_recurring');

        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = RecurringJob::paginate(Paginator::resolvePerPage());

        return RecurringJobListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /** todo remove after tests
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Recurr\Exception\InvalidRRule
     * @throws \Recurr\Exception\InvalidWeekday
     */
    public function check()
    {
        $laravelJob = new ProcessRecurringJobs();
        $laravelJob->handle();

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/jobs/recurring/{id}",
     *      tags={"Jobs"},
     *      summary="Returns full information about specified recurring job",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/RecurringJobResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      )
     * )
     *
     * @param int $recurringJobId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $recurringJobId)
    {
        $this->authorize('jobs.manage_recurring');

        $recurringJob = RecurringJob::findOrFail($recurringJobId);

        return RecurringJobResponse::make($recurringJob);
    }

    /**
     * @OA\Post(
     *      path="/jobs/recurring",
     *      tags={"Jobs"},
     *      summary="Create new recurring job",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\JsonContent(ref="#/components/schemas/CreateRecurringJobRequest")
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/RecurringJobResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Jobs\CreateRecurringJobRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateRecurringJobRequest $request)
    {
        $this->authorize('jobs.manage_recurring');
        $recurringJob = RecurringJob::create($request->validated());
        $recurringJob->saveOrFail();

        return RecurringJobListResponse::make($recurringJob, null, 201);
    }

    /**
     * @OA\Delete(
     *      path="/jobs/recurring/{id}",
     *      tags={"Jobs"},
     *      summary="Delete existing recurring job",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      )
     * )
     * @param int $recurringJobId
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(int $recurringJobId)
    {
        $this->authorize('jobs.manage_recurring');

        $recurringJob = RecurringJob::findOrFail($recurringJobId);
        $recurringJob->delete();
        Log::info(
            sprintf(
                'Recurring job [JOB_ID:%s] has been deleted by user [USER_ID:%d]',
                $recurringJob->id,
                $recurringJob->recurrence_rule
            ),
            [
                'recurring_job_id' => $recurringJob->id,
                'user_id'          => Auth::id(),
            ]
        );

        return ApiOKResponse::make();
    }
}
