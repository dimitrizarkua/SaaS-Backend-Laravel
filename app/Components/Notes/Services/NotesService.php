<?php

namespace App\Components\Notes\Services;

use App\Components\Documents\Models\Document;
use App\Components\Notes\Interfaces\NotesServiceInterface;
use App\Components\Notes\Models\Note;
use App\Components\Notes\Models\NoteData;
use App\Exceptions\Api\NotAllowedException;
use Illuminate\Support\Facades\DB;

/***
 * Class NotesService
 *
 * @package App\Components\Notes\Services
 */
class NotesService implements NotesServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getNote(int $noteId): Note
    {
        return Note::findOrFail($noteId);
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function addNote(NoteData $noteData): Note
    {
        $note = null;
        DB::transaction(function () use ($noteData, &$note) {
            $note = new Note([
                'note'    => $noteData->getNote(),
                'user_id' => $noteData->getUserId(),
            ]);
            $note->saveOrFail();
            $note->resolveMentions();
        });

        return $note;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Throwable
     */
    public function updateNote(NoteData $noteData, int $noteId): Note
    {
        $note = $this->getNote($noteId);

        DB::transaction(function () use ($noteData, $note) {
            $note->note = $noteData->getNote();
            $note->saveOrFail();
            $note->resolveMentions();
        });

        return $note;
    }

    /**
     * {@inheritdoc}
     *
     * @throws NotAllowedException
     */
    public function attachDocument(Document $document, int $noteId): void
    {
        $note = $this->getNote($noteId);
        try {
            $note->documents()->attach($document->id);
        } catch (\Exception $exception) {
            throw new NotAllowedException('This document has been already attached to this note earlier.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function detachDocument(Document $document, int $noteId): void
    {
        $note = $this->getNote($noteId);
        $note->documents()->detach($document->id);
    }
}
