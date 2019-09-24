<?php

namespace App\Http\Controllers\Photos;

use App\Components\Photos\Interfaces\PhotosServiceInterface;
use App\Components\Photos\Models\Photo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Photos\CreatePhotoRequest;
use App\Http\Requests\Photos\DownloadMultiplePhotosRequest;
use App\Http\Requests\Photos\UpdatePhotoRequest;
use App\Http\Responses\ApiOKResponse;
use App\Http\Responses\Photos\PhotoResponse;
use OpenApi\Annotations as OA;

/**
 * Class PhotosController
 *
 * @package App\Http\Controllers\Photos
 */
class PhotosController extends Controller
{
    /**
     * @var \App\Components\Photos\Interfaces\PhotosServiceInterface
     */
    private $service;

    /**
     * PhotosController constructor.
     *
     * @param PhotosServiceInterface $service
     */
    public function __construct(PhotosServiceInterface $service)
    {
        $this->service = $service;
    }

    /**
     * @OA\Post(
     *      path="/photos",
     *      tags={"Photos"},
     *      summary="Upload new photo",
     *      description="Allows to upload new photo",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/CreatePhotoRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=201,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PhotoResponse")
     *       ),
     *       @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Photos\CreatePhotoRequest $request
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(CreatePhotoRequest $request)
    {
        $this->authorize('photos.create');

        $photo = $this->service->createPhotoFromFile($request->photo());

        return PhotoResponse::make($photo, null, 201);
    }

    /**
     * @OA\Get(
     *      path="/photos/{id}",
     *      tags={"Photos"},
     *      summary="Get detailed photo info",
     *      description="Returns detailed info about specific photo",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PhotoResponse")
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested photo could not be found.",
     *      ),
     * )
     *
     * @param \App\Components\Photos\Models\Photo $photo
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(Photo $photo)
    {
        $this->authorize('photos.view');

        return PhotoResponse::make($photo);
    }

    /**
     * @OA\Post(
     *      path="/photos/{id}",
     *      tags={"Photos"},
     *      summary="Re-upload the photo",
     *      description="Allows to edit (re-upload) existing photo",
     *      security={{"passport": {}}},
     *      @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(ref="#/components/schemas/UpdatePhotoRequest")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(ref="#/components/schemas/PhotoResponse")
     *       ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested photo could not be found.",
     *      ),
     *      @OA\Response(
     *          response=405,
     *          description="Not allowed. Could not update a thumbnail.",
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Validation error",
     *          @OA\JsonContent(ref="#/components/schemas/ValidationErrorResponse")
     *      ),
     * )
     *
     * @param \App\Http\Requests\Photos\UpdatePhotoRequest $request
     * @param int                                          $photoId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function reupload(UpdatePhotoRequest $request, int $photoId)
    {
        $this->authorize('photos.update');

        $photo = $this->service->updatePhotoFromFile($photoId, $request->photo());

        return PhotoResponse::make($photo, null, 200);
    }

    /**
     * @OA\Get(
     *      path="/photos/{id}/download",
     *      tags={"Photos"},
     *      summary="Download specific photo",
     *      description="Allows to download specific photo",
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
     *          description="Not found. Requested photo could not be found.",
     *      ),
     * )
     *
     * @param int $photoId
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function download(int $photoId)
    {
        $this->authorize('photos.view');

        return $this->service->getPhotoContentsAsResponse($photoId);
    }

    /**
     * @OA\Get(
     *      path="/photos/download-multiple",
     *      tags={"Photos"},
     *      summary="Download specific photo",
     *      description="Allows to download multiple photos as a ZIP archive",
     *      security={{"passport": {}}},
     *      @OA\Parameter(
     *          name="photo_ids",
     *          in="path",
     *          required=true,
     *          description="Photo ids to download",
     *          @OA\Schema(
     *              type="array",
     *              @OA\Items(type="integer")
     *          )
     *      ),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\MediaType(
     *              mediaType="application/zip",
     *              @OA\Schema(type="file")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. One of the requested photos could not be found.",
     *      ),
     * )
     *
     * @param \App\Http\Requests\Photos\DownloadMultiplePhotosRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function downloadMultiple(DownloadMultiplePhotosRequest $request)
    {
        $this->authorize('photos.view');

        return $this->service->getPhotosZipAsResponse($request->getPhotoIds());
    }

    /**
     * @OA\Delete(
     *      path="/photos/{id}",
     *      tags={"Photos"},
     *      summary="Delete existing photo",
     *      description="Delete existing photo",
     *      security={{"passport": {}}},
     *      @OA\Parameter(ref="#/components/parameters/required-id-in-path"),
     *      @OA\Response(
     *         response=200,
     *         description="OK",
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found. Requested photo could not be found.",
     *      ),
     * )
     *
     * @param int $photoId
     *
     * @return \App\Http\Responses\ApiOKResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $photoId)
    {
        $this->authorize('photos.delete');
        $this->service->deletePhoto($photoId);

        return ApiOKResponse::make();
    }
}
