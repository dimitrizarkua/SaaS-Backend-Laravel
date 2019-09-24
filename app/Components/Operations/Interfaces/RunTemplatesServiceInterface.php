<?php

namespace App\Components\Operations\Interfaces;

use App\Components\Operations\Models\JobRunTemplate;
use Illuminate\Support\Collection;

/**
 * Interface RunTemplatesServiceInterface
 *
 * @package App\Components\Operations\Interfaces
 */
interface RunTemplatesServiceInterface
{
    /**
     * Get template by id.
     *
     * @param int $templateId Template id.
     *
     * @return \App\Components\Operations\Models\JobRunTemplate
     */
    public function getTemplate(int $templateId): JobRunTemplate;

    /**
     * Get all run templates related to the specified location.
     *
     * @param int $locationId Location id.
     *
     * @return \Illuminate\Support\Collection
     */
    public function listLocationTemplates(int $locationId): Collection;

    /**
     * Create new run template.
     *
     * @param int         $locationId Location id.
     * @param string|null $name       Template name.
     *
     * @return \App\Components\Operations\Models\JobRunTemplate
     */
    public function createTemplate(int $locationId, string $name = null): JobRunTemplate;

    /**
     * Delete run template.
     *
     * @param int $templateId Template id.
     *
     * @return void
     */
    public function deleteTemplate(int $templateId): void;
}
