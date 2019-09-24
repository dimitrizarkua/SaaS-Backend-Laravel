<?php

namespace App\Http\Controllers\Finance;

use App\Components\Finance\Models\CreditNote;
use App\Components\Finance\Services\CreditNoteService;
use App\Components\Tags\Models\Tag;
use App\Exceptions\Api\NotAllowedException;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Tags\TagListResponse;
use OpenApi\Annotations as OA;

/**
 * Class CreditNoteTagsController
 *
 * @package App\Http\Controllers\Finance
 */
class CreditNoteTagsController extends Controller
{
    /**
     * @var CreditNoteService
     */
    private $service;

    /**
     * PurchaseOrderListingController constructor.
     *
     * @param CreditNoteService $service
     */
    public function __construct(CreditNoteService $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Get(
     *      path="/finance/credit-notes/{credit_note}/tags",
     *      tags={"Finance","Credit Notes"},
     *      summary="Returns list of tags assigned to credit note.",
     *      description="Allows to view list of credit note tags. **`finance.credit_notes.view`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/TagListResponse")
     *      ),
     *      @OA\Response(
     *         response=404,
     *         description="Not found. Requested resource couldn't be found.",
     *      ),
     * )
     * @param \App\Components\Finance\Models\CreditNote $creditNote
     *
     * @return \App\Http\Responses\ApiResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function getTags(CreditNote $creditNote)
    {
        $this->authorize('finance.credit_notes.view');

        return TagListResponse::make($creditNote->tags);
    }

    /**
     * @OA\Post(
     *      path="/finance/credit-notes/{credit_note}/tags/{tag}",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Allows to attach tag to specific credit note",
     *      description="Allows to attach tag to specific credit note. **`finance.credit_notes.manage`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="credit_note",
     *          in="path",
     *          required=true,
     *          description="Credit note identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="tag",
     *          in="path",
     *          required=true,
     *          description="Attached tag identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Finance\Models\CreditNote $creditNote
     *
     * @param \App\Components\Tags\Models\Tag           $tag
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function attachTag(CreditNote $creditNote, Tag $tag)
    {
        $this->authorize('finance.credit_notes.manage');

        try {
            $creditNote->tags()->attach($tag);
        } catch (\Exception $e) {
            throw new NotAllowedException('Tag already added to this credit note');
        }

        return ApiOKResponse::make();
    }

    /**
     * @OA\Delete(
     *      path="/finance/credit-notes/{credit_note}/tags/{tag}",
     *      tags={"Finance", "Credit Notes"},
     *      summary="Allows to detach tag from specific credit note",
     *      description="Allows to detach tag from specific credit note. **`finance.credit_notes.manage`**
    permission is required to perform this operation.",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="credit_note",
     *          in="path",
     *          required=true,
     *          description="Credit note identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *     @OA\Parameter(
     *          name="tag",
     *          in="path",
     *          required=true,
     *          description="Attached tag identifier",
     *          @OA\Schema(
     *              type="integer",
     *              example=1,
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     * @param \App\Components\Finance\Models\CreditNote $creditNote
     *
     * @param \App\Components\Tags\Models\Tag           $tag
     *
     * @return \App\Http\Responses\ApiOKResponse
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function detachTag(CreditNote $creditNote, Tag $tag)
    {
        $this->authorize('finance.credit_notes.manage');

        $creditNote->tags()->detach($tag);

        return ApiOKResponse::make();
    }
}
