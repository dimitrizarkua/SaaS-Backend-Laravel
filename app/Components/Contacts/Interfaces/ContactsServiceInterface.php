<?php

namespace App\Components\Contacts\Interfaces;

use App\Components\Contacts\Models\CompanyData;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\PersonData;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;

/**
 * Interface ContactsServiceInterface
 *
 * @package App\Components\Contacts\Interfaces
 */
interface ContactsServiceInterface
{
    /**
     * Get contact by id.
     *
     * @param int $contactId Contact id.
     *
     * @return \App\Components\Contacts\Models\Contact
     */
    public function getContact(int $contactId): Contact;

    /**
     * Create person contact.
     *
     * @param \App\Components\Contacts\Models\PersonData $data Person data.
     *
     * @return \App\Components\Contacts\Models\Contact
     */
    public function createPerson(PersonData $data): Contact;

    /**
     * Create company contact.
     *
     * @param \App\Components\Contacts\Models\CompanyData $data Company data.
     *
     * @return \App\Components\Contacts\Models\Contact
     */
    public function createCompany(CompanyData $data): Contact;

    /**
     * Delete contact.
     *
     * @param int $contactId Contact id.
     *
     * @return void
     */
    public function deleteContact(int $contactId): void;

    /**
     * Link one contact to another.
     *
     * @param int $parentId Parent contact id.
     * @param int $childId  Child contact id.
     */
    public function linkContacts(int $parentId, int $childId): void;

    /**
     * Unlink one contact from another.
     *
     * @param int $parentId Parent contact id.
     * @param int $childId  Child contact id.
     */
    public function unlinkContacts(int $parentId, int $childId): void;

    /**
     * Add note to contact.
     *
     * @param int $contactId Contact id.
     * @param int $noteId    Note id.
     * @param int $meetingId Meeting id.
     */
    public function addNote(int $contactId, int $noteId, int $meetingId = null): void;

    /**
     * Allows to remove a note from a contact.
     *
     * @param int $contactId Contact id.
     * @param int $noteId    Note id.
     */
    public function deleteNote(int $contactId, int $noteId): void;

    /**
     * Add contact address.
     *
     * @param int    $contactId Contact id.
     * @param int    $addressId Address id.
     * @param string $type      Address type.
     *
     * @see AddressContactTypes
     *
     */
    public function addAddress(int $contactId, int $addressId, string $type): void;

    /**
     * Delete contact address.
     *
     * @param int $contactId Contact id.
     * @param int $addressId Address id.
     */
    public function deleteAddress(int $contactId, int $addressId): void;

    /**
     * Add contact tag.
     *
     * @param int $contactId Contact id.
     * @param int $tagId     Tag id.
     */
    public function addTag(int $contactId, int $tagId): void;

    /**
     * Delete contact tag.
     *
     * @param int $contactId Contact id.
     * @param int $tagId     Tag id.
     */
    public function deleteTag(int $contactId, int $tagId): void;

    /**
     * Add managed account.
     *
     * @param int $contactId Contact id.
     * @param int $userId    User id.
     */
    public function addManagedAccount(int $contactId, int $userId): void;

    /**
     * Delete managed account.
     *
     * @param int $contactId Contact id.
     * @param int $userId    User id.
     */
    public function deleteManagedAccount(int $contactId, int $userId): void;

    /**
     * "Touch" contact by updating its last activity time.
     *
     * @param int    $contactId Contact id.
     * @param Carbon $touchedAt Last activity time.
     */
    public function touch(int $contactId, Carbon $touchedAt = null): void;

    /**
     * Get default contact status.
     *
     * @param int $contactCategoryId Contact category id.
     *
     * @return string
     */
    public function getDefaultStatus(int $contactCategoryId): string;

    /**
     * Allows to upload new / update existing contact avatar image.
     *
     * @param int                           $contactId Contact id.
     * @param \Illuminate\Http\UploadedFile $photo     Uploaded photo.
     *
     * @return Contact
     */
    public function updateContactAvatar(int $contactId, UploadedFile $photo): Contact;

    /**
     * Allows to delete existing contact avatar.
     *
     * @param int $contactId Contact id.
     */
    public function deleteContactAvatar(int $contactId);
}
