<?php

namespace App\Http\Controllers\Contacts;

use App\Http\Responses\ApiOKResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactAccountsController
 *
 * @package App\Http\Controllers\Contacts
 */
class ContactAccountsController extends ContactsControllerBase
{
    /**
     * @OA\Post(
     *      path="/contacts/{contact_id}/users/{user_id}",
     *      tags={"Contacts"},
     *      summary="Create managed account",
     *      description="Allows to create new managed account.
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
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         description="User identifier",
     *         @OA\Schema(
     *            type="integer",
     *            example=1,
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK"
     *      ),
     *      @OA\Response(
     *         response=405,
     *         description="Managed account already exists",
     *      ),
     * )
     * @param int $contactId
     * @param int $userId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function addManagedAccount(int $contactId, int $userId)
    {
        $this->authorize('contacts.update');
        $this->service->addManagedAccount($contactId, $userId);

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/contacts/{contact_id}/users/{user_id}",
     *      tags={"Contacts"},
     *      summary="Delete managed account",
     *      description="Allows to delete new managed account.
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
     *         name="user_id",
     *         in="path",
     *         required=true,
     *         description="User identifier",
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
     * @param int $userId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function deleteManagedAccount(int $contactId, int $userId)
    {
        $this->authorize('contacts.update');
        $this->service->deleteManagedAccount($contactId, $userId);

        return ApiOKResponse::make();
    }
}
