<?php

namespace App\Http\Controllers\Contacts;

use App\Components\Contacts\Events\ContactModelChanged;
use App\Components\Contacts\Models\CompanyData;
use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\ContactTypes;
use App\Components\Contacts\Models\PersonData;
use App\Components\Pagination\Paginator;
use App\Http\Requests\Contacts\CreateCompanyRequest;
use App\Http\Requests\Contacts\CreatePersonRequest;
use App\Http\Requests\Contacts\GetContactsRequest;
use App\Http\Requests\Contacts\SearchContactsRequest;
use App\Http\Requests\Contacts\UpdateContactAvatarRequest;
use App\Http\Requests\Contacts\UpdateContactRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Contacts\ContactListResponse;
use App\Http\Responses\Contacts\ContactResponse;
use App\Http\Responses\Contacts\FullContactResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;

/**
 * Class ContactsController
 *
 * @package App\Http\Controllers\Contacts
 */
class ContactsController extends ContactsControllerBase
{
    public const SEARCH_RESULTS_LIMIT = 10;

    /**
     * @OA\Get(
     *      path="/contacts",
     *      tags={"Contacts"},
     *      summary="Get contacts",
     *      description="Get paginated list of contacts.
    `contacts.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="contact_type",
     *         in="query",
     *         description="Allows to filter contacts by type",
     *         @OA\Schema(ref="#/components/schemas/ContactTypes")
     *      ),
     *      @OA\Parameter(
     *         name="contact_category_id",
     *         in="query",
     *         description="Allows to filter contacts by category id",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="contact_category_type",
     *         in="query",
     *         description="Allows to filter contacts by category type",
     *         @OA\Schema(ref="#/components/schemas/ContactCategoryTypes")
     *      ),
     *      @OA\Parameter(
     *         name="contact_status",
     *         in="query",
     *         description="Allows to filter contacts by status",
     *         @OA\Schema(ref="#/components/schemas/ContactStatuses")
     *      ),
     *      @OA\Parameter(
     *         name="active_in_days",
     *         in="query",
     *         description="Allows to filter contacts by last activity date (in days)",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="term",
     *         in="query",
     *         description="Allows to filter contacts by full text depending of contact type",
     *         @OA\Schema(
     *            type="string",
     *            example="Yarra",
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/ContactListResponse")
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Contacts\GetContactsRequest $request
     *
     * @return \App\Http\Responses\Contacts\ContactListResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(GetContactsRequest $request)
    {
        $this->authorize('contacts.view');
        $query      = Contact::filter($request->validated());
        $pagination = $query->paginateRaw(Paginator::resolvePerPage());
        $response   = Collection::make(mapElasticResults($pagination));

        return new ContactListResponse($response, [
            'per_page'     => $pagination->perPage(),
            'current_page' => $pagination->currentPage(),
            'last_page'    => $pagination->lastPage(),
            'total_items'  => $pagination->total(),
        ]);
    }

    /**
     * @OA\Get(
     *      path="/contacts/{id}",
     *      tags={"Contacts"},
     *      summary="Retrieve full information about specific contact",
     *      description="Retrieve full information about specific contact.
    `contacts.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/FullContactResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Contacts\Models\Contact $contact
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Contact $contact)
    {
        $this->authorize('contacts.view');

        return FullContactResponse::make($contact);
    }

    /**
     * @OA\Patch(
     *      path="/contacts/{id}",
     *      tags={"Contacts"},
     *      summary="Update existing contact",
     *      description="Allows to update existing contact.
    `contacts.update` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateContactRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/ContactResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Contacts\UpdateContactRequest $request
     * @param \App\Components\Contacts\Models\Contact          $contact
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateContactRequest $request, Contact $contact)
    {
        $this->authorize('contacts.update');

        DB::beginTransaction();
        try {
            $contact->fillFromRequest($request);
            $contactChanges     = $contact->getChanges();
            $contactTypeChanges = [];

            if (ContactTypes::PERSON === $contact->contact_type) {
                $contact->person->fillFromRequest($request);
                $contactTypeChanges = $contact->person->getChanges();
            } elseif (ContactTypes::COMPANY === $contact->contact_type) {
                $contact->company->fillFromRequest($request);
                $contactTypeChanges = $contact->company->getChanges();
            }

            $this->service->touch($contact->id);
            $contactChanges += $contact->getChanges();
            $updatedFields  = array_merge($contactChanges, $contactTypeChanges);

            DB::commit();
            event(new ContactModelChanged($contact->id, Auth::id(), $updatedFields));
        } catch (\Exception $e) {
            DB::rollBack();
        }

        return ContactResponse::make($contact);
    }

    /**
     * @OA\Delete(
     *      path="/contacts/{id}",
     *      tags={"Contacts"},
     *      summary="Delete contact",
     *      description="Allows to delete contact.
    `contacts.delete` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Could not delete contact assigned to a job
    or having assigned subsidiaries or persons.",
     *      ),
     * )
     * @param int $contactId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $contactId)
    {
        $this->authorize('contacts.delete');
        $this->service->deleteContact($contactId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Get(
     *      path="/contacts/search",
     *      tags={"Contacts", "Search"},
     *      summary="Search for contacts",
     *      description="Get filtered list of contacts, results are limited to 10 records.
    `contacts.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="contact_type",
     *         in="query",
     *         description="Allows to filter contacts by type",
     *         @OA\Schema(ref="#/components/schemas/ContactTypes")
     *      ),
     *      @OA\Parameter(
     *         name="contact_category_id",
     *         in="query",
     *         description="Allows to filter contacts by category id",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="contact_category_type",
     *         in="query",
     *         description="Allows to filter contacts by category type",
     *         @OA\Schema(ref="#/components/schemas/ContactCategoryTypes")
     *      ),
     *      @OA\Parameter(
     *         name="term",
     *         in="query",
     *         description="Allows to filter contacts by full text depending of contact type",
     *         @OA\Schema(
     *            type="string",
     *            example="Yarra",
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/ContactListResponse")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Contacts\SearchContactsRequest $request
     *
     * @return \App\Http\Responses\Contacts\ContactListResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(SearchContactsRequest $request)
    {
        $this->authorize('contacts.view');
        $query = Contact::filter($request->validated());
        $query->take(self::SEARCH_RESULTS_LIMIT);
        $response = Collection::make(mapElasticResults($query->raw()));

        return new ContactListResponse($response);
    }

    /**
     * @OA\Post(
     *      path="/contacts/{parent_id}/{child_id}",
     *      tags={"Contacts"},
     *      summary="Link one contact to another",
     *      description="Allows to link person or company to the parent company.
    `contacts.update` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="parent_id",
     *         in="path",
     *         required=true,
     *         description="Parent contact identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="child_id",
     *         in="path",
     *         required=true,
     *         description="Child contact identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Contacts could not be linked",
     *      ),
     * )
     * @param int $parentId
     * @param int $childId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function linkContact(int $parentId, int $childId)
    {
        $this->authorize('contacts.update');
        $this->service->linkContacts($parentId, $childId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/contacts/{parent_id}/{child_id}",
     *      tags={"Contacts"},
     *      summary="Unlink one contact from another",
     *      description="Allows to unlink person or company from the parent company.
    `contacts.update` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="parent_id",
     *         in="path",
     *         required=true,
     *         description="Parent contact identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="child_id",
     *         in="path",
     *         required=true,
     *         description="Child contact identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param int $parentId
     * @param int $childId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function unlinkContact(int $parentId, int $childId)
    {
        $this->authorize('contacts.update');
        $this->service->unlinkContacts($parentId, $childId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Post(
     *      path="/contacts/person",
     *      tags={"Contacts"},
     *      summary="Create new person",
     *      description="Create new person.
    `contacts.create` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreatePersonRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/ContactResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Contacts\CreatePersonRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function addPerson(CreatePersonRequest $request)
    {
        $this->authorize('contacts.create');
        $data       = $request->validated();
        $personData = new PersonData($data);
        $person     = $this->service->createPerson($personData);

        return ContactResponse::make($person, null, 201);
    }

    /**
     * @OA\Post(
     *      path="/contacts/company",
     *      tags={"Contacts"},
     *      summary="Create new company",
     *      description="Create new company.
    `contacts.create` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateCompanyRequest")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(ref="#/components/schemas/ContactResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Contacts\CreateCompanyRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \JsonMapper_Exception
     */
    public function addCompany(CreateCompanyRequest $request)
    {
        $this->authorize('contacts.create');
        $data        = $request->validated();
        $companyData = new CompanyData($data);
        $company     = $this->service->createCompany($companyData);

        return ContactResponse::make($company, null, 201);
    }

    /**
     * @OA\Post(
     *     path="/contacts/{id}/avatar",
     *     summary="Update contact avatar",
     *     description="Allows to update a contact avatar. Image dimensions are limited to 300x300 pixels.
    `contacts.update` permission is required to perform this operation",
     *     tags={"Contacts"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(ref="#/components/schemas/UpdateContactAvatarRequest")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ContactResponse"),
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *     ),
     * )
     * @param \App\Http\Requests\Contacts\UpdateContactAvatarRequest $request
     * @param int                                                    $contactId
     *
     * @return \App\Http\Responses\ApiResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function updateAvatar(UpdateContactAvatarRequest $request, int $contactId)
    {
        $this->authorize('contacts.update');

        $contact = $this->service->updateContactAvatar($contactId, $request->photo());

        return new ContactResponse($contact);
    }

    /**
     * @OA\Delete(
     *     path="/contacts/{id}/avatar",
     *     summary="Allows to reset contact avatar",
     *     description="Allows to reset a contact avatar.
    `contacts.update` permission is required to perform this operation",
     *     tags={"Contacts"},
     *     security={{"passport": {}}},
     *     @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *     @OA\Response(
     *         response=200,
     *         description="OK"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/schemas/UnauthorizedResponse")
     *     ),
     *     @OA\Response(
     *         response="405",
     *         description="Not allowed. Avatar not set."
     *     )
     * )
     * @param int $contactId
     *
     * @return \App\Http\Responses\ApiResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteAvatar(int $contactId)
    {
        $this->authorize('contacts.update');

        $this->service->deleteContactAvatar($contactId);

        return ApiOKResponse::make();
    }
}
