<?php

namespace App\Components\Reporting\Services;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\ContactNote;
use App\Components\Contacts\Models\ContactStatus;
use App\Components\Contacts\Models\ContactTag;
use App\Components\Contacts\Models\Enums\ContactStatuses;
use App\Components\Contacts\Models\ManagedAccount;
use App\Components\Finance\Enums\FinancialEntityStatuses;
use App\Components\Finance\Models\Invoice;
use App\Components\Locations\Exceptions\NotAllowedException;
use App\Components\Locations\Models\Location;
use App\Components\Notes\Models\Note;
use App\Components\Reporting\Interfaces\ContactVolumeReportServiceInterface;
use App\Components\Reporting\Models\Filters\ContactVolumeReportFilter;
use App\Components\Reporting\Models\Filters\IncomeReportFilter;
use App\Components\Reporting\Models\HasTagsDistribution;
use App\Components\Reporting\Models\VO\ContactVolumeReportData;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class ContactVolumeReportService
 *
 * @package App\Components\Reporting\Services
 */
class ContactVolumeReportService implements ContactVolumeReportServiceInterface
{
    use HasTagsDistribution;

    /** @var ContactVolumeReportFilter */
    private $filter;

    /**
     * ContactVolumeReportService constructor.
     *
     * @param ContactVolumeReportFilter $filter
     */
    public function __construct(ContactVolumeReportFilter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * {@inheritdoc}
     *
     * @return self
     */
    public function setFilter(ContactVolumeReportFilter $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function getReportData(): ContactVolumeReportData
    {
        $staff = $this->getStaff();

        $contactsIds = $this->getManagedContactsIds($staff);

        $leadData = $this->getNewAndConvertedLeadsCounters($contactsIds);

        $invoices    = $this->getInvoices($contactsIds);
        $revenueData = $this->getRevenueData($invoices);

        $touchedAndMeetingsCounters = $this->getTouchedAndMeetingsCounter($contactsIds);

        $reportData            = new ContactVolumeReportData();
        $reportData->managed   = count($contactsIds);
        $reportData->newLeads  = count($leadData['newLeadIds']);
        $reportData->converted = count($leadData['convertedLeadIds']);
        $reportData->revenue   = $revenueData['total'];
        $reportData->touched   = $touchedAndMeetingsCounters['touched'];
        $reportData->meetings  = $touchedAndMeetingsCounters['meetings'];
        $reportData->chart     = $revenueData['chart'];

        $managedContacts = Contact::query()
            ->with('tags')
            ->whereIn('id', $contactsIds)
            ->get();

        $reportData->tags  = $this->getTagsDistribution($managedContacts);
        $reportData->staff = $this->getStaffData($staff, $contactsIds, $leadData, $revenueData);

        return $reportData;
    }

    /**
     * @param \Illuminate\Support\Collection $staff
     * @param array                          $managedContactsIds
     * @param array                          $leadData
     * @param array                          $revenueData
     *
     * @return array
     */
    private function getStaffData(
        Collection $staff,
        array $managedContactsIds,
        array $leadData,
        array $revenueData
    ): array {
        $staffData = [];

        $managedAccounts = ManagedAccount::query()
            ->select(['user_id', 'contact_id'])
            ->whereIn('contact_id', $managedContactsIds)
            ->get();

        foreach ($staff as $user) {
            foreach ($managedAccounts as $managedAccount) {
                if ($managedAccount->user_id !== $user->id) {
                    continue;
                }

                $userId           = $user->id;
                $managedContactId = $managedAccount->contact_id;

                if (!isset($staffData[$userId])) {
                    $staffData[$userId]['managed'] = 0;
                    $staffData[$userId]['leads']   = 0;
                    $staffData[$userId]['revenue'] = 0;
                    $staffData[$userId]['staff']   = $user->full_name ?? $user->email;
                }

                if (in_array($managedContactId, $managedContactsIds)) {
                    $staffData[$userId]['managed'] += 1;
                }

                if (in_array($managedContactId, $leadData['newLeadIds'])) {
                    $staffData[$userId]['leads'] += 1;
                }

                if (array_key_exists($managedContactId, $revenueData['byContact'])) {
                    $staffData[$userId]['revenue'] += $revenueData['byContact'][$managedContactId];
                }
            }
        }

        return array_values($staffData);
    }

    /**
     * “New Leads” means any contact who during the period had their status set to “lead” where it previously was not
     * “lead”.
     *
     * “Leads Converted” should be calculated on those leads who during the period had their status changed to “active”.
     *
     * @param array $managedContactsIds
     *
     * @return array format:['leads' => ['new' => 12, 'converted' => 32]];
     */
    private function getNewAndConvertedLeadsCounters(array $managedContactsIds): array
    {
        $statusesGroupedByContact = ContactStatus::query()
            ->select(['contact_id', 'status'])
            ->whereIn('contact_id', $managedContactsIds)
            ->whereDate('created_at', '>=', $this->filter->date_from)
            ->whereDate('created_at', '<=', $this->filter->date_to)
            ->orderBy('contact_id')
            ->get()
            ->groupBy('contact_id');

        $leadIds = $convertedLeadIds = $newLeadIds = [];

        $wasLeadsBeforeIds = ContactStatus::query()
            ->selectRaw('MAX(created_at) AS latestStatus , contact_id')
            ->whereIn('contact_id', $managedContactsIds)
            ->whereDate('created_at', '<', $this->filter->date_from)
            ->where('status', ContactStatuses::LEAD)
            ->groupBy('contact_id')
            ->pluck('contact_id')
            ->toArray();

        foreach ($statusesGroupedByContact as $contactId => $statuses) {
            foreach ($statuses as $statusData) {
                if (in_array($contactId, $wasLeadsBeforeIds)) {
                    $leadIds[] = $statusData['contact_id'];
                    break;
                }

                if ($statusData['status'] === ContactStatuses::LEAD) {
                    $leadIds[]    = $statusData['contact_id'];
                    $newLeadIds[] = $statusData['contact_id'];
                    break;
                }
            }
        }

        foreach ($statusesGroupedByContact as $contactId => $statuses) {
            foreach ($statuses as $statusData) {
                if ($statusData['status'] === ContactStatuses::ACTIVE && in_array($contactId, $leadIds)) {
                    $convertedLeadIds[] = $statusData['contact_id'];
                    break;
                }
            }
        }

        return [
            'leadIds'          => $leadIds,
            'newLeadIds'       => $newLeadIds,
            'convertedLeadIds' => $convertedLeadIds,
        ];
    }

    /**
     * Returns touched and meetings counters.
     * “Touched” means any notes, meetings that have been added for the managed contact within the period.
     *
     * @param array $managedContactsIds
     *
     * @return array
     */
    private function getTouchedAndMeetingsCounter(array $managedContactsIds): array
    {
        $noteQuery = Note::query()
            ->selectRaw('id')
            ->whereDate('created_at', '>=', $this->filter->date_from)
            ->whereDate('created_at', '<=', $this->filter->date_to);

        $contactNote = ContactNote::query()
            ->whereIn('contact_id', $managedContactsIds);

        $from = sprintf(
            '(%s) AS note, (%s) AS contact_note',
            $noteQuery->toSql(),
            $contactNote->toSql()
        );

        $bindings = [$noteQuery->getBindings(), $contactNote->getBindings()];

        $results = DB::query()
            ->select(['note.id', 'contact_note.meeting_id'])
            ->fromRaw($from, $bindings)
            ->whereRaw('note.id = contact_note.note_id')
            ->get();

        return [
            'touched'  => $results->count(),
            'meetings' => $results->where('meeting_id', '!==', null)->count(),
        ];
    }

    /**
     * @param array $managedContactsIds
     *
     * @return \Illuminate\Support\Collection
     *
     * @throws \JsonMapper_Exception
     */
    private function getInvoices(array $managedContactsIds): Collection
    {
        $revenueAccountFilter              = new IncomeReportFilter();
        $revenueAccountFilter->date_from   = $this->filter->date_from;
        $revenueAccountFilter->date_to     = $this->filter->date_to;
        $revenueAccountFilter->location_id = $this->filter->location_id;

        $invoicesIds = Invoice::query()
            ->select(['id'])
            ->whereIn('recipient_contact_id', $managedContactsIds)
            ->whereDate('date', '>=', $this->filter->date_from)
            ->whereDate('date', '<=', $this->filter->date_to)
            ->whereNotNull('locked_at')
            ->whereNotNull('job_id')
            ->pluck('id')
            ->toArray();

        $withRelations  = ['items'];
        $selectedFields = ['date', 'recipient_contact_id'];

        return Invoice::getCollectionByStatuses(
            $invoicesIds,
            [FinancialEntityStatuses::APPROVED],
            $withRelations,
            $selectedFields
        );
    }

    /**
     * Returns revenue total amount by period and distribution by managed contact.
     *
     * “Revenue” should be calculated from the total invoiced amount of all jobs in the period for all managed contacts.
     *
     * @param \Illuminate\Support\Collection|Invoice[] $invoices
     *
     * @return array Revenue data.
     *
     */
    private function getRevenueData(Collection $invoices): array
    {
        $totalRevenue     = 0;
        $revenueByContact = $revenueByDate = $chart = [];

        /** @var Invoice $invoice */
        foreach ($invoices as $invoice) {
            $revenueAmount = $invoice->getSubTotalAmount();
            $totalRevenue  += $revenueAmount;

            $date = $invoice->date->format('Y-m-d');

            if (isset($revenueByDate[$date])) {
                $revenueByDate[$date] += $revenueAmount;
            } else {
                $revenueByDate[$date] = $revenueAmount;
            }

            if (isset($revenueByContact[$invoice->recipient_contact_id])) {
                $revenueByContact[$invoice->recipient_contact_id] += $revenueAmount;
            } else {
                $revenueByContact[$invoice->recipient_contact_id] = $revenueAmount;
            }
        }

        foreach ($revenueByDate as $date => $revenueAmount) {
            $chart[] = [
                'x' => $date,
                'y' => $revenueAmount,
            ];
        }

        return [
            'total'     => $totalRevenue,
            'byContact' => $revenueByContact,
            'chart'     => $chart,
        ];
    }

    /**
     * @return Collection|User[]
     */
    private function getStaff(): Collection
    {
        if (null !== $this->filter->staff_id) {
            return User::query()
                ->where('id', $this->filter->staff_id)
                ->get();
        }

        try {
            $users = Location::findOrFail($this->filter->location_id)
                ->users()
                ->get();
        } catch (ModelNotFoundException $e) {
            throw new NotAllowedException('Location not found');
        }

        return $users;
    }

    /**
     * @param \Illuminate\Support\Collection|User[] $users
     *
     * @return array User identifiers.
     */
    private function getManagedContactsIds(Collection $users): array
    {
        $managedAccounts = ManagedAccount::query()
            ->select(['user_id', 'contact_id'])
            ->whereIn('user_id', $users->pluck('id')->toArray())
            ->whereDate('created_at', '>=', $this->filter->date_from)
            ->whereDate('created_at', '<=', $this->filter->date_to)
            ->when($this->filter->contact_id, function (Builder $query) {
                return $query->whereIn('id', $this->filter->contact_id);
            })
            ->get();

        $managedContactsIds = $managedAccounts->pluck('contact_id')->toArray();

        if (empty($this->filter->tag_ids)) {
            return $managedContactsIds;
        }

        $contactTags = ContactTag::query()
            ->select(['tag_id', 'contact_id'])
            ->whereIn('contact_id', $managedContactsIds)
            ->get();

        $managedContactsIds = $contactTags->pluck('contact_id')
            ->toArray();

        return $managedContactsIds;
    }
}
