<?php

namespace App\Http\Controllers\Documents;

use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Documents\Models\Document;
use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\CreateDocumentRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Documents\DocumentResponse;
use OpenApi\Annotations as OA;

/**
 * Class DocumentsController
 *
 * @package App\Http\Controllers\Documents
 */
class DocumentsController extends Controller
{
    /**
     * @var \App\Components\Documents\Interfaces\DocumentsServiceInterface
     */
    private $service;

    /**
     * DocumentsController constructor.
     *
     * @param DocumentsServiceInterface $service
     */
    public function __construct(DocumentsServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *      path="/documents",
     *      tags={"Documents"},
     *      summary="Allows to create new document",
     *      description="Allows to create new document",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/CreateDocumentRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/DocumentResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Documents\CreateDocumentRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreateDocumentRequest $request)
    {
        $this->authorize('documents.create');

        $document = $this->service->createDocumentFromFile($request->file('file'));

        return DocumentResponse::make($document, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/documents/{id}",
     *      tags={"Documents"},
     *      summary="Get specific document info",
     *      description="Returns info about specific document",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/DocumentResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Document $document)
    {
        $this->authorize('documents.view');

        return DocumentResponse::make($this->service->getDocument($document->id));
    }

    /**
     * @OA\Get(
     *      path="/documents/{id}/download",
     *      tags={"Documents"},
     *      summary="Allows to download specific document",
     *      description="Download specific document",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\MediaType(
     *              mediaType="application/octet-stream",
     *              @OA\Schema(type="file")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Requested resource could not be found.",
     *      ),
     * )
     *
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function download(Document $document)
    {
        $this->authorize('documents.download');

        return $this->service->getDocumentContentsAsResponse($document->id);
    }

    /**
     * @OA\Delete(
     *      path="/documents/{id}",
     *      tags={"Documents"},
     *      summary="Delete existing document",
     *      description="Delete existing document",
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
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @throws \Exception
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(Document $document)
    {
        $this->authorize('documents.delete');
        $this->service->deleteDocument($document->id);

        return ApiOKResponse::make();
    }
}
