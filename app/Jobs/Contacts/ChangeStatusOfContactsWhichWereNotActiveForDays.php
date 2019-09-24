<?php

namespace App\Jobs\Contacts;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\ContactStatuses;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Class ChangeStatusOfContactsWhichWereNotActiveForDays
 *
 * @package App\Jobs\Contacts
 */
class ChangeStatusOfContactsWhichWereNotActiveForDays implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $contacts = Contact::shouldBeDeactivated()->get();
        /** @var Contact $contact */
        foreach ($contacts as $contact) {
            $contact->changeStatus(ContactStatuses::INACTIVE);
        }
    }
}
