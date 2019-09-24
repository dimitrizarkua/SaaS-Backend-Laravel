<?php

namespace App\Http\Controllers\Tags;

use App\Components\Tags\Interfaces\TagsServiceInterface;
use App\Components\Tags\Models\Tag;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tags\CreateTagRequest;
use App\Http\Requests\Tags\SearchTagsRequest;
use App\Http\Requests\Tags\UpdateTagRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Tags\TagListResponse;
use App\Http\Responses\Tags\TagResponse;
use OpenApi\Annotations as OA;

/**
 * Class TagsController
 *
 * @package App\Http\Controllers\Tags
 */
class TagsController extends Controller
{
    /**
     * @var TagsServiceInterface
     */
    private $service;

    /**
     * TagsController constructor.
     *
     * @param \App\Components\Tags\Interfaces\TagsServiceInterface $tagService
     */
    public function __construct(TagsServiceInterface $tagService)
    {
        $this->service = $tagService;
    }

    /**
     * @OA\Get(
     *      path="/tags/search",
     *      tags={"Tags", "Search"},
     *      summary="Get filtered set of tags",
     *      description="Allows to filter tags by type and name",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Allows to filter tags by name",
     *         @OA\Schema(
     *            type="string",
     *            example="Urgent",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="type",
     *         in="query",
     *         description="Allows to filter tags by type",
     *         @OA\Schema(
     *            type="string",
     *            example="job",
     *         )
     *      ),
     *      @OA\Parameter(
     *         name="count",
     *         in="query",
     *         description="Allows to change the tags limit",
     *         @OA\Schema(
     *            type="integer",
     *            example=10,
     *            default=15
     *         )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TagListResponse")
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function search(SearchTagsRequest $request)
    {
        $this->authorize('tags.view');
        $tags = $this->service->search($request->validated());

        return TagListResponse::make($tags);
    }

    /**
     * @OA\Post(
     *      path="/tags",
     *      tags={"Tags"},
     *      summary="Allows to create new tag",
     *      description="Allows to create new tag",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/CreateTagRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TagResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function store(CreateTagRequest $request)
    {
        $this->authorize('tags.create');
        $tag = Tag::create($request->validated());
        $tag->saveOrFail();

        return TagResponse::make($tag, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/tags/{id}",
     *      tags={"Tags"},
     *      summary="Retrieve full information about specific tag",
     *      description="Retrieve full information about specific tag",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TagResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Tag $tag)
    {
        $this->authorize('tags.view');

        return TagResponse::make($tag);
    }

    /**
     * @OA\Patch(
     *      path="/tags/{id}",
     *      tags={"Tags"},
     *      summary="Update existing tag",
     *      description="Allows to update existing tag",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(ref="#/components/schemas/UpdateTagRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TagResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Throwable
     */
    public function update(UpdateTagRequest $request, Tag $tag)
    {
        $this->authorize('tags.update');
        $tag->fillFromRequest($request);

        return TagResponse::make($tag);
    }

    /**
     * @OA\Delete(
     *      path="/tags/{id}",
     *      tags={"Tags"},
     *      summary="Delete existing tag",
     *      description="Delete existing tag",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Exception
     */
    public function destroy(Tag $tag)
    {
        $this->authorize('tags.delete');
        $tag->delete();

        return ApiOKResponse::make();
    }
}
