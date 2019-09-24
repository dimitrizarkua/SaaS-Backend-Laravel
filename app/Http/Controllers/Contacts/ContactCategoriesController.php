<?php

namespace App\Http\Controllers\Contacts;

use App\Components\Contacts\Models\ContactCategory;
use App\Components\Pagination\Paginator;
use App\Exceptions\Api\NotAllowedException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contacts\CreateContactCategoryRequest;
use App\Http\Requests\Contacts\UpdateContactCategoryRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Contacts\ContactCategoryListResponse;
use App\Http\Responses\Contacts\ContactCategoryResponse;
use OpenApi\Annotations as OA;

/**
 * Class ContactCategoriesController
 *
 * @package App\Http\Controllers\Contacts
 */
class ContactCategoriesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/contacts/categories",
     *      tags={"Contacts"},
     *      summary="Returns list of all contact categories",
     *      description="Returns list of all contact categories.
    `contacts.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ContactCategoryListResponse")
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        $this->authorize('contacts.view');
        /** @var \App\Components\Pagination\Paginator $pagination */
        $pagination = ContactCategory::paginate(Paginator::resolvePerPage());

        return ContactCategoryListResponse::make($pagination->getItems(), $pagination->getPaginationData());
    }

    /**
     * @OA\Post(
     *      path="/contacts/categories",
     *      tags={"Contacts"},
     *      summary="Allows to create new contact category",
     *      description="Allows to create new contact category.
    `contacts.update` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateContactCategoryRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ContactCategoryResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @param \App\Http\Requests\Contacts\CreateContactCategoryRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateContactCategoryRequest $request)
    {
        $this->authorize('contacts.update');
        $category = ContactCategory::create($request->validated());
        $category->saveOrFail();

        return ContactCategoryResponse::make($category, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/contacts/categories/{id}",
     *      tags={"Contacts"},
     *      summary="Retrieve full information about specific contact category",
     *      description="Retrieve full information about specific contact category.
    `contacts.view` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ContactCategoryResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Contacts\Models\ContactCategory $category
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(ContactCategory $category)
    {
        $this->authorize('contacts.view');

        return ContactCategoryResponse::make($category);
    }

    /**
     * @OA\Patch(
     *      path="/contacts/categories/{id}",
     *      tags={"Contacts"},
     *      summary="Update existing contact category",
     *      description="Allows to update existing contact category.
    `contacts.update` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateContactCategoryRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/ContactCategoryResponse")
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
     * @param \App\Http\Requests\Contacts\UpdateContactCategoryRequest $request
     * @param \App\Components\Contacts\Models\ContactCategory          $category
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateContactCategoryRequest $request, ContactCategory $category)
    {
        $this->authorize('contacts.update');
        $category->fillFromRequest($request);

        return ContactCategoryResponse::make($category);
    }

    /**
     * @OA\Delete(
     *      path="/contacts/categories/{id}",
     *      tags={"Contacts"},
     *      summary="Delete existing contact category",
     *      description="Delete existing contact category.
    `contacts.update` permission is required to perform this operation",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested resource could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Requested contact category has registered contacts.",
     *      ),
     * )
     * @param \App\Components\Contacts\Models\ContactCategory $category
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(ContactCategory $category)
    {
        $this->authorize('contacts.update');

        if ($category->contacts()->count() > 0) {
            throw new NotAllowedException('Could not be deleted since it has registered contacts');
        }

        $category->delete();

        return ApiOKResponse::make();
    }
}
