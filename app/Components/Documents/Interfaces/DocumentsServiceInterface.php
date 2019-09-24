<?php

namespace App\Components\Documents\Interfaces;

use App\Components\Documents\Models\Document;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

/**
 * Interface DocumentsServiceInterface
 *
 * @package App\Components\Documents\Interfaces
 */
interface DocumentsServiceInterface
{
    /**
     * Returns disk name to be used as a storage for documents.
     *
     * @return string
     */
    public function getDiskName(): string;

    /**
     * Creates a document from existing file and saves to the database and storage.
     *
     * @param \Illuminate\Http\UploadedFile|\Illuminate\Http\File $file File to be saved.
     *
     * @return \App\Components\Documents\Models\Document Saved document.
     */
    public function createDocumentFromFile(UploadedFile $file): Document;

    /**
     * Downloads a file specified by URL and creates a document from it.
     *
     * @param string $url          URL.
     * @param string $originalName Original file name.
     * @param string $mimeType     Mime type of the file.
     * @param array  $headers      Headers to be used on file download.
     *
     * @return \App\Components\Documents\Models\Document Saved document.
     */
    public function createDocumentFromUrl(
        string $url,
        string $originalName,
        string $mimeType = null,
        array $headers = []
    ): Document;

    /**
     * Get specific document (info) by id.
     *
     * @param int $documentId Document id.
     *
     * @return \App\Components\Documents\Models\Document Document.
     */
    public function getDocument(int $documentId): Document;

    /**
     * Allows to retrieve document's contents by id.
     *
     * @param int $documentId Document id.
     *
     * @return \Illuminate\Http\File
     */
    public function getDocumentContents(int $documentId): string;

    /**
     * Returns document's content as streamed response (for download).
     *
     * @param int $documentId Document id.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getDocumentContentsAsResponse(int $documentId): Response;

    /**
     * Returns temporary url to document.
     *
     * @param int                             $documentId Document id.
     * @param \Illuminate\Support\Carbon|null $expiration Time object which defines when link will expire (default 10
     *                                                    minutes).
     *
     * @return null|string
     */
    public function getDocumentTemporaryUrl(int $documentId, Carbon $expiration = null): ?string;

    /**
     * Allows to update existing document's contents.
     *
     * @param int                           $documentId     Document id.
     * @param \Illuminate\Http\UploadedFile $file           File to be saved.
     * @param bool                          $updateFileName Defines whether document's file name should be updated from
     *                                                      file provided or not.
     *
     * @return \App\Components\Documents\Models\Document Updated document.
     */
    public function updateDocumentContentsFromFile(
        int $documentId,
        UploadedFile $file,
        bool $updateFileName = false
    ): Document;

    /**
     * Allows to delete a document by id.
     *
     * @param int  $documentId Document id.
     * @param bool $hardDelete Defines whether document should be soft or hard deleted.
     */
    public function deleteDocument(int $documentId, bool $hardDelete = false): void;

    /**
     * Allows to link document to other entity in the system (through M-M table).
     *
     * @param int                                 $documentId Document id.
     * @param \Illuminate\Database\Eloquent\Model $model      Other entity.
     * @param bool                                $silent     Defines whether linkage should be done silently (without
     *                                                        errors thrown) or not.
     */
    public function linkDocumentTo(int $documentId, Model $model, bool $silent = false): void;

    /**
     * Unlinks document from other entity in the system (deletes link in M-M table).
     *
     * @param int                                 $documentId Document id.
     * @param \Illuminate\Database\Eloquent\Model $model      Other entity.
     * @param bool                                $silent     Defines whether linkage should be done silently (without
     *                                                        errors thrown) or not.
     */
    public function unlinkDocumentFrom(int $documentId, Model $model, bool $silent = false): void;
}
