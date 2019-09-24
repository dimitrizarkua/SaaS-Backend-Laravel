<?php

namespace App\Components\Notes\Interfaces;

use App\Components\Documents\Models\Document;
use App\Components\Notes\Models\Note;
use App\Components\Notes\Models\NoteData;

/**
 * Interface NotesServiceInterface
 *
 * @package App\Components\Notes\Interfaces
 */
interface NotesServiceInterface
{
    /**
     * Get note by id.
     *
     * @param int $noteId Note id.
     *
     * @return \App\Components\Notes\Models\Note
     */
    public function getNote(int $noteId): Note;

    /**
     * Create new note.
     *
     * @param \App\Components\Notes\Models\NoteData $noteData
     *
     * @return \App\Components\Notes\Models\Note
     */
    public function addNote(NoteData $noteData): Note;

    /**
     * Update existing note.
     *
     * @param \App\Components\Notes\Models\NoteData $noteData
     * @param int                                   $noteId
     *
     * @return \App\Components\Notes\Models\Note
     */
    public function updateNote(NoteData $noteData, int $noteId): Note;

    /**
     * Attach document to the note.
     *
     * @param \App\Components\Documents\Models\Document $document
     * @param int                                       $noteId
     */
    public function attachDocument(Document $document, int $noteId): void;

    /**
     * Detach document from the note.
     *
     * @param \App\Components\Documents\Models\Document $document
     * @param int                                       $noteId
     */
    public function detachDocument(Document $document, int $noteId): void;
}
