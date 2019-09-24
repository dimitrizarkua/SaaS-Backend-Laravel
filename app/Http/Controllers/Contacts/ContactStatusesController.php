<?php

namespace App\Http\Controllers\Contacts;

use App\Components\Contacts\Models\Contact;
use App\Components\Contacts\Models\Enums\ContactStatuses;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contacts\UpdateContactStatusRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Contacts\ContactStatusListResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactStatusesController
 *
 * @package App\Http\Controllers\Contacts
 */
class ContactStatusesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/contacts/statuses",
     *      tags={"Contacts"},
     *      summary="Returns list of all contact statuses",
     *      description="Returns list of all contact statuses.
    `contacts.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ContactStatusListResponse")
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('contacts.view');

        return ContactStatusListResponse::make(array_values(ContactStatuses::values()));
    }

    /**
     * @OA\Patch(
     *      path="/contacts/{id}/status",
     *      tags={"Contacts"},
     *      summary="Change contact status",
     *      description="Allows to change status of the contact. **`contacts.update`** permission is required to
    perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateContactStatusRequest")
     *          )
     *      ),
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Not allowed. Status could not be changed.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Contacts\UpdateContactStatusRequest $request
     * @param \App\Components\Contacts\Models\Contact                $contact
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function changeStatus(UpdateContactStatusRequest $request, Contact $contact)
    {
        $this->authorize('contacts.update');

        $contact->changeStatus($request->getStatus());

        return ApiOkResponse::make();
    }
}
