<?php

namespace App\Components\Documents\Services;

use App\Components\Documents\Exceptions\DownloadFailedException;
use App\Components\Documents\Exceptions\InvalidAssociationException;
use App\Components\Documents\Interfaces\DocumentsServiceInterface;
use App\Components\Documents\Models\Document;
use App\Core\Utils\Curl;
use App\Utils\FileIO;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DocumentsService
 *
 * @package App\Components\Documents\Services
 */
class DocumentsService implements DocumentsServiceInterface
{
    const ASSOCIATION_METHOD_NAME = 'documents';

    private $diskName;

    /**
     * Validates that documents model is associated with provider model (via ::documents() method).
     *
     * @param \Illuminate\Database\Eloquent\Model $model Model (instance) to be validated.
     *
     * @throws \ReflectionException
     */
    private function validateAssociationWithDocuments(Model $model): void
    {
        $exception = new InvalidAssociationException(sprintf(
            'Method ::%s() doesn\'t exist in %s class',
            self::ASSOCIATION_METHOD_NAME,
            get_class($model)
        ));

        if (method_exists($model, self::ASSOCIATION_METHOD_NAME)) {
            $reflection = new \ReflectionMethod($model, self::ASSOCIATION_METHOD_NAME);
            if (!$reflection->isPublic()) {
                throw $exception;
            }
        } else {
            throw $exception;
        }
    }

    /**
     * Returns disk name by environment.
     * DOCUMENTS_STORAGE_DISK value overrides any default value.
     *
     * @return string $diskName
     */
    public function getDiskName(): string
    {
        $diskName = env('DOCUMENTS_STORAGE_DISK');

        if (null !== $this->diskName) {
            return $this->diskName;
        }

        if (null === $diskName) {
            $diskName = App::environment(['local', 'testing']) ? 'documents_local' : 'documents_s3';
        }

        return $diskName;
    }

    /**
     * @param string $diskName
     *
     * @return \App\Components\Documents\Services\DocumentsService
     */
    public function setDiskName(string $diskName): self
    {
        $this->diskName = $diskName;

        return $this;
    }

    /**
     * Create document from file.
     *
     * @param \Illuminate\Http\UploadedFile $file
     *
     * @return \App\Components\Documents\Models\Document
     *
     * @throws \Throwable
     */
    public function createDocumentFromFile(UploadedFile $file): Document
    {
        $storageUid = Str::uuid();
        $file->storeAs('/', $storageUid, ['disk' => $this->getDiskName()]);

        $document = new Document();
        $document->guard(['*' => false]);
        $document->fill([
            'storage_uid' => $storageUid,
            'file_name'   => $file->getClientOriginalName(),
            'file_size'   => $file->getSize(),
            'file_hash'   => hash_file('sha256', $file->getRealPath()),
            'mime_type'   => $file->getMimeType(),
        ]);
        $document->saveOrFail();

        return $document;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function createDocumentFromUrl(
        string $url,
        string $originalName,
        string $mimeType = null,
        array $headers = []
    ): Document {
        $tmpPath = FileIO::getTmpFilePath();

        if (!Curl::downloadFile($url, $tmpPath, $headers)) {
            throw new DownloadFailedException('Error occurred during file download.');
        }

        $file = new UploadedFile($tmpPath, $originalName, $mimeType);

        $document = $this->createDocumentFromFile($file);

        File::delete($tmpPath);

        return $document;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getDocument(int $documentId): Document
    {
        return Document::findOrFail($documentId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getDocumentContents(int $documentId): string
    {
        $document = self::getDocument($documentId);

        return Storage::disk($this->getDiskName())->get($document->storage_uid);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function getDocumentContentByUid(string $uid): string
    {
        return Storage::disk($this->getDiskName())->get($uid);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function getDocumentContentsAsResponse(int $documentId): Response
    {
        $document = self::getDocument($documentId);

        return Storage::disk($this->getDiskName())
            ->response($document->storage_uid, Str::ascii($document->file_name));
    }

    /**
     * {@inheritdoc}
     */
    public function getDocumentTemporaryUrl(int $documentId, Carbon $expiration = null): ?string
    {
        $document = self::getDocument($documentId);

        // Not all drivers support temporary urls!

        try {
            $url = Storage::disk($this->getDiskName())->temporaryUrl(
                $document->storage_uid,
                $expiration ?? Carbon::now()->addMinutes(10)
            );

            return $url;
        } catch (\RuntimeException $exception) {
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function updateDocumentContentsFromFile(
        int $documentId,
        UploadedFile $file,
        bool $updateFileName = false
    ): Document {
        $document = self::getDocument($documentId);

        $file->storeAs('/', $document->storage_uid, ['disk' => $this->getDiskName()]);
        $document->guard(['*' => false]);
        $document->fill([
            'file_name' => $updateFileName ? $file->getClientOriginalName() : $document->file_name,
            'file_size' => $file->getSize(),
            'file_hash' => hash_file('sha256', $file->getRealPath()),
            'mime_type' => $file->getMimeType(),
        ]);
        $document->saveOrFail();

        return $document;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Exception
     */
    public function deleteDocument(int $documentId, bool $hardDelete = false): void
    {
        $document = self::getDocument($documentId);

        $document->delete();

        if (true === $hardDelete) {
            $document->forceDelete();
            Storage::disk($this->getDiskName())->delete($document->storage_uid);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidAssociationException When provided model is not associated with documents through public
     *                                     ::documents() method.
     * @throws \Exception
     */
    public function linkDocumentTo(int $documentId, Model $model, bool $silent = false): void
    {
        try {
            $this->validateAssociationWithDocuments($model);

            $model->{self::ASSOCIATION_METHOD_NAME}()->attach($documentId);
        } catch (InvalidAssociationException $exception) {
            if (!$silent) {
                throw $exception;
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidAssociationException When provided model is not associated with documents through public
     *                                     ::documents() method.
     * @throws \Exception
     */
    public function unlinkDocumentFrom(int $documentId, Model $model, bool $silent = false): void
    {
        try {
            $this->validateAssociationWithDocuments($model);

            $model->{self::ASSOCIATION_METHOD_NAME}()->detach($documentId);
        } catch (InvalidAssociationException $exception) {
            if (!$silent) {
                throw $exception;
            }
        }
    }
}
