<?php

namespace App\Components\Jobs;

use App\Components\Jobs\Interfaces\JobAreasServiceInterface;
use App\Components\Jobs\Interfaces\JobContactsServiceInterface;
use App\Components\Jobs\Interfaces\JobDocumentsServiceInterface;
use App\Components\Jobs\Interfaces\JobLabourServiceInterface;
use App\Components\Jobs\Interfaces\JobEquipmentServiceInterface;
use App\Components\Jobs\Interfaces\JobListingServiceInterface;
use App\Components\Jobs\Interfaces\JobMaterialsServiceInterface;
use App\Components\Jobs\Interfaces\JobMessagesServiceInterface;
use App\Components\Jobs\Interfaces\JobNotesServiceInterface;
use App\Components\Jobs\Interfaces\JobPhotosServiceInterfaces;
use App\Components\Jobs\Interfaces\JobSiteSurveyServiceInterface;
use App\Components\Jobs\Interfaces\JobsServiceInterface;
use App\Components\Jobs\Interfaces\JobStatusWorkflowInterface;
use App\Components\Jobs\Interfaces\JobTagsServiceInterface;
use App\Components\Jobs\Interfaces\JobTasksServiceInterface;
use App\Components\Jobs\Interfaces\JobUsersServiceInterface;
use App\Components\Jobs\Services\JobAreasService;
use App\Components\Jobs\Services\JobContactsService;
use App\Components\Jobs\Services\JobDocumentsService;
use App\Components\Jobs\Services\JobLaboursService;
use App\Components\Jobs\Services\JobEquipmentService;
use App\Components\Jobs\Services\JobListingService;
use App\Components\Jobs\Services\JobMaterialsService;
use App\Components\Jobs\Services\JobMessagesService;
use App\Components\Jobs\Services\JobNotesService;
use App\Components\Jobs\Services\JobPhotosService;
use App\Components\Jobs\Services\JobSiteSurveyService;
use App\Components\Jobs\Services\JobsService;
use App\Components\Jobs\Services\JobTagsService;
use App\Components\Jobs\Services\JobStatusWorkflow;
use App\Components\Jobs\Services\JobTasksService;
use App\Components\Jobs\Services\JobUsersService;
use Illuminate\Support\ServiceProvider;

/**
 * Class JobsServiceProvider
 *
 * @package App\Components\Jobs
 */
class JobsServiceProvider extends ServiceProvider
{
    public $bindings = [
        JobsServiceInterface::class          => JobsService::class,
        JobStatusWorkflowInterface::class    => JobStatusWorkflow::class,
        JobListingServiceInterface::class    => JobListingService::class,
        JobPhotosServiceInterfaces::class    => JobPhotosService::class,
        JobContactsServiceInterface::class   => JobContactsService::class,
        JobDocumentsServiceInterface::class  => JobDocumentsService::class,
        JobMessagesServiceInterface::class   => JobMessagesService::class,
        JobNotesServiceInterface::class      => JobNotesService::class,
        JobTagsServiceInterface::class       => JobTagsService::class,
        JobUsersServiceInterface::class      => JobUsersService::class,
        JobSiteSurveyServiceInterface::class => JobSiteSurveyService::class,
        JobTasksServiceInterface::class      => JobTasksService::class,
        JobEquipmentServiceInterface::class  => JobEquipmentService::class,
        JobMaterialsServiceInterface::class  => JobMaterialsService::class,
        JobLabourServiceInterface::class     => JobLaboursService::class,
        JobAreasServiceInterface::class      => JobAreasService::class,
    ];
}
