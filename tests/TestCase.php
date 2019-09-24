<?php

namespace Tests;

use App\Components\Addresses\Models\Address;
use App\Components\Addresses\Models\Country;
use App\Components\Addresses\Models\State;
use App\Components\Addresses\Models\Suburb;
use App\Components\AssessmentReports\Models\AssessmentReport;
use App\Components\AssessmentReports\Models\AssessmentReportCostingStage;
use App\Components\AssessmentReports\Models\AssessmentReportCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportSection;
use App\Components\AssessmentReports\Models\AssessmentReportSectionCostItem;
use App\Components\AssessmentReports\Models\AssessmentReportSectionImage;
use App\Components\AssessmentReports\Models\AssessmentReportSectionPhoto;
use App\Components\AssessmentReports\Models\AssessmentReportSectionRoom;
use App\Components\AssessmentReports\Models\AssessmentReportSectionTextBlock;
use App\Components\AssessmentReports\Models\AssessmentReportStatus;
use App\Components\AssessmentReports\Models\CarpetAge;
use App\Components\AssessmentReports\Models\CarpetConstructionType;
use App\Components\AssessmentReports\Models\CarpetFaceFibre;
use App\Components\AssessmentReports\Models\CarpetType;
use App\Components\AssessmentReports\Models\FlooringSubtype;
use App\Components\AssessmentReports\Models\NonRestorableReason;
use App\Components\AssessmentReports\Models\UnderlayType;
use App\Components\Contacts\Models\AddressContact;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactCategory;
use App\Components\Contacts\Models\ContactCompanyProfile;
use App\Components\Contacts\Models\ContactNote;
use App\Components\Contacts\Models\ContactPersonProfile;
use App\Components\Contacts\Models\ContactStatus;
use App\Components\Contacts\Models\ContactTag;
use App\Components\Contacts\Models\ManagedAccount;
use App\Components\Documents\Models\Document;
use App\Components\Finance\Models\AccountTypeGroup;
use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Models\CreditNoteItem;
use App\Components\Finance\Models\ForwardedPayment;
use App\Components\Finance\Models\ForwardedPaymentInvoice;
use App\Components\Finance\Models\GLAccount;
use App\Components\Finance\Models\GSCode;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\InvoiceItem;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Jobs\Models\Job;
use App\Components\Jobs\Models\JobAllowance;
use App\Components\Jobs\Models\JobContactAssignment;
use App\Components\Jobs\Models\JobContactAssignmentType;
use App\Components\Jobs\Models\JobDocument;
use App\Components\Jobs\Models\JobEquipment;
use App\Components\Jobs\Models\JobFollower;
use App\Components\Jobs\Models\JobLabour;
use App\Components\Jobs\Models\JobLahaCompensation;
use App\Components\Jobs\Models\JobMaterial;
use App\Components\Jobs\Models\JobMessage;
use App\Components\Jobs\Models\JobNote;
use App\Components\Jobs\Models\JobNotesTemplate;
use App\Components\Jobs\Models\JobReimbursement;
use App\Components\Jobs\Models\JobRoom;
use App\Components\Jobs\Models\JobService;
use App\Components\Jobs\Models\JobSiteSurveyQuestion;
use App\Components\Jobs\Models\JobStatus;
use App\Components\Jobs\Models\JobTag;
use App\Components\Jobs\Models\JobTask;
use App\Components\Jobs\Models\JobTaskCrewAssignment;
use App\Components\Jobs\Models\JobTaskStatus;
use App\Components\Jobs\Models\JobTaskType;
use App\Components\Jobs\Models\JobTaskVehicleAssignment;
use App\Components\Jobs\Models\JobTeam;
use App\Components\Jobs\Models\JobUser;
use App\Components\Jobs\Models\JobUserNotification;
use App\Components\Jobs\Models\LinkedJob;
use App\Components\Jobs\Models\RecurringJob;
use App\Components\Locations\Models\Location;
use App\Components\Locations\Models\LocationSuburb;
use App\Components\Locations\Models\LocationUser;
use App\Components\Meetings\Models\Meeting;
use App\Components\Messages\Models\DocumentMessage;
use App\Components\Messages\Models\Message;
use App\Components\Messages\Models\MessageStatus;
use App\Components\Notes\Models\DocumentNote;
use App\Components\Notes\Models\Note;
use App\Components\Notifications\Models\UserNotification;
use App\Components\Notifications\Models\UserNotificationSetting;
use App\Components\Operations\Models\JobRun;
use App\Components\Operations\Models\JobRunCrewAssignment;
use App\Components\Operations\Models\JobRunTemplate;
use App\Components\Operations\Models\JobRunTemplateRun;
use App\Components\Operations\Models\JobRunTemplateRunCrewAssignment;
use App\Components\Operations\Models\JobRunTemplateRunVehicleAssignment;
use App\Components\Operations\Models\JobRunVehicleAssignment;
use App\Components\Operations\Models\Vehicle;
use App\Components\Operations\Models\VehicleStatus;
use App\Components\Operations\Models\VehicleStatusType;
use App\Components\Photos\Models\Photo;
use App\Components\RBAC\Models\PermissionRole;
use App\Components\RBAC\Models\Role;
use App\Components\RBAC\Models\RoleUser;
use App\Components\AssessmentReports\Models\FlooringType;
use App\Components\SiteSurvey\Models\SiteSurveyQuestion;
use App\Components\SiteSurvey\Models\SiteSurveyQuestionOption;
use App\Components\Tags\Models\Tag;
use App\Components\Teams\Models\Team;
use App\Components\Teams\Models\TeamMember;
use App\Components\UsageAndActuals\Models\AllowanceType;
use App\Components\UsageAndActuals\Models\Equipment;
use App\Components\UsageAndActuals\Models\EquipmentCategory;
use App\Components\UsageAndActuals\Models\LabourType;
use App\Components\UsageAndActuals\Models\LahaCompensation;
use App\Components\UsageAndActuals\Models\Material;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Class TestCase
 *
 * @package Tests
 */
abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private const MAXIMUM_NUMBER_OF_ATTEMPTS = 10;
    private const FOREIGN_KEY_VIOLATION      = 23503;

    private $attempts = [];

    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * The list of models (class names) that the test are using. For every model in list
     * method 'truncate' will be called.
     *
     * @var array
     */
    protected $models = [
        AccountTypeGroup::class,
        JobLahaCompensation::class,
        JobReimbursement::class,
        JobAllowance::class,
        JobLabour::class,
        JobEquipment::class,
        JobMaterial::class,
        JobTaskCrewAssignment::class,
        JobTaskVehicleAssignment::class,
        JobTaskStatus::class,
        JobTask::class,
        JobTaskType::class,
        JobRunCrewAssignment::class,
        JobRunVehicleAssignment::class,
        JobRun::class,
        VehicleStatus::class,
        VehicleStatusType::class,
        Vehicle::class,
        JobRunTemplateRunCrewAssignment::class,
        JobRunTemplateRunVehicleAssignment::class,
        JobRunTemplateRun::class,
        JobRunTemplate::class,
        JobUserNotification::class,
        UserNotification::class,
        UserNotificationSetting::class,
        JobContactAssignment::class,
        JobContactAssignmentType::class,
        JobDocument::class,
        JobFollower::class,
        JobMessage::class,
        JobNote::class,
        JobNotesTemplate::class,
        JobUser::class,
        JobTeam::class,
        JobTag::class,
        LinkedJob::class,
        JobSiteSurveyQuestion::class,
        JobRoom::class,
        Job::class,
        RecurringJob::class,
        JobService::class,
        JobStatus::class,

        SiteSurveyQuestionOption::class,
        SiteSurveyQuestion::class,

        Meeting::class,

        ContactTag::class,
        ContactNote::class,
        ManagedAccount::class,
        AddressContact::class,
        Contact::class,
        ContactStatus::class,
        ContactCategory::class,
        ContactPersonProfile::class,
        ContactCompanyProfile::class,

        LocationSuburb::class,
        LocationUser::class,
        Location::class,
        Address::class,
        Suburb::class,
        State::class,
        Country::class,
        Tag::class,

        TeamMember::class,
        Team::class,

        MessageStatus::class,
        Message::class,

        DocumentMessage::class,
        DocumentNote::class,
        Document::class,

        Note::class,

        RoleUser::class,
        Role::class,
        PermissionRole::class,
        User::class,

        Photo::class,

        FlooringType::class,
        FlooringSubtype::class,
        UnderlayType::class,
        NonRestorableReason::class,
        CarpetType::class,
        CarpetConstructionType::class,
        CarpetAge::class,
        CarpetFaceFibre::class,

        AssessmentReportSectionTextBlock::class,
        AssessmentReportSectionImage::class,
        AssessmentReportSectionPhoto::class,
        AssessmentReportSectionCostItem::class,
        AssessmentReportSectionRoom::class,
        AssessmentReportSection::class,
        AssessmentReportCostItem::class,
        AssessmentReportCostingStage::class,
        AssessmentReportStatus::class,
        AssessmentReport::class,

        GSCode::class,
        GLAccount::class,
        ForwardedPaymentInvoice::class,
        ForwardedPayment::class,

        Equipment::class,
        LahaCompensation::class,
        AllowanceType::class,
        LabourType::class,
        Material::class,
        EquipmentCategory::class,

        Invoice::class,
        InvoiceItem::class,
        CreditNote::class,
        CreditNoteItem::class,
        PurchaseOrder::class,
        PurchaseOrderItem::class,

    ];

    public function setUp()
    {
        parent::setUp();

        $this->faker = Factory::create();
    }

    protected function tearDown()
    {
        $this->faker->unique(true);
        foreach ($this->models as $model) {
            $instance = new $model;
            if (!$instance instanceof Model) {
                continue;
            }

            $tableName = $instance->getTable();
            $this->clearTableCascade($tableName);
        }
        Cache::tags(env('APP_ENV'))
            ->flush();
        parent::tearDown();
    }

    /**
     * @param $table
     */
    private function clearTableCascade($table)
    {
        if (isset($this->attempts[$table])) {
            if ($this->attempts[$table] >= self::MAXIMUM_NUMBER_OF_ATTEMPTS) {
                return;
            }

            $this->attempts[$table]++;
        } else {
            $this->attempts[$table] = 1;
        }

        try {
            DB::table($table)->delete();
        } catch (QueryException $e) {
            if (self::FOREIGN_KEY_VIOLATION != $e->getCode()) {
                return;
            }

            preg_match('/on table(?!.*on table)\s"(.*)"/i', $e->getMessage(), $match);
            if (isset($match[1])) {
                $this->clearTableCascade($match[1]);
            }

            $this->clearTableCascade($table);
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * Compares data array with given model.
     *
     * @param array $data
     * @param Model $model
     */
    protected static function compareDataWithModel(array $data, Model $model)
    {
        foreach ($data as $column => $value) {
            $attributeValue = $model->getAttribute($column);
            if ($attributeValue instanceof Carbon) {
                self::assertTrue($attributeValue->eq(new Carbon($value)));
            } elseif (is_object($attributeValue)) {
                //skip nested relations
                continue;
            } else {
                self::assertEquals($attributeValue, $value);
            }
        }
    }
}
