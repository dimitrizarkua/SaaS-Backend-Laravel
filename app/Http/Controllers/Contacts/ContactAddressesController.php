<?php

namespace App\Http\Controllers\Contacts;

use App\Components\Contacts\Models\Contact;
use App\Http\Requests\Contacts\CreateContactAddressRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Contacts\ContactAddressListResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactAddressesController
 *
 * @package App\Http\Controllers\Contacts
 */
class ContactAddressesController extends ContactsControllerBase
{
    /**
     * @OA\Get(
     *      path="/contacts/{contact_id}/addresses",
     *      tags={"Contacts"},
     *      summary="Returns list of all contact addresses",
     *      description="Returns list of all contact addresses.
    `contacts.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="contact_id",
     *         in="path",
     *         required=true,
     *         description="Contact identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ContactAddressListResponse")
     *      ),
     * )
     * @param \App\Components\Contacts\Models\Contact $contact
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getContactAddresses(Contact $contact)
    {
        $this->authorize('contacts.view');

        return ContactAddressListResponse::make($contact->addresses);
    }

    /**
     * @OA\Post(
     *      path="/contacts/{contact_id}/addresses/{address_id}",
     *      tags={"Contacts"},
     *      summary="Add contact address",
     *      description="Allows to add address to the contact.
    `contacts.update` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="contact_id",
     *         in="path",
     *         required=true,
     *         description="Contact identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="address_id",
     *         in="path",
     *         required=true,
     *         description="Address identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateContactAddressRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK"
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Address already added to this contact",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Contacts\CreateContactAddressRequest $request
     * @param int                                                     $contactId
     * @param int                                                     $addressId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addContactAddress(CreateContactAddressRequest $request, int $contactId, int $addressId)
    {
        $this->authorize('contacts.update');
        $this->service->addAddress($contactId, $addressId, $request->get('type'));

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/contacts/{contact_id}/addresses/{address_id}",
     *      tags={"Contacts"},
     *      summary="Delete contact address",
     *      description="Allows to delete contact address.
    `contacts.update` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="contact_id",
     *         in="path",
     *         required=true,
     *         description="Contact identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="address_id",
     *         in="path",
     *         required=true,
     *         description="Address identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK"
     *      ),
     * )
     * @param int $contactId
     * @param int $addressId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteContactAddress(int $contactId, int $addressId)
    {
        $this->authorize('contacts.update');
        $this->service->deleteAddress($contactId, $addressId);

        return ApiOKResponse::make();
    }
}
