<?php

namespace App\Components\Operations\Services;

use App\Components\Locations\Interfaces\LocationsServiceInterface;
use App\Components\Operations\Exceptions\NotAllowedException;
use App\Components\Operations\Interfaces\RunTemplatesServiceInterface;
use App\Components\Operations\Models\JobRunTemplate;
use Illuminate\Support\Collection;

/**
 * Class RunTemplatesService
 *
 * @package App\Components\Operations\Services
 */
class RunTemplatesService implements RunTemplatesServiceInterface
{
    /**
     * {@inheritdoc}
     */
    public function getTemplate(int $templateId): JobRunTemplate
    {
        return JobRunTemplate::findOrFail($templateId);
    }

    /**
     * {@inheritdoc}
     */
    public function listLocationTemplates(int $locationId): Collection
    {
        /* @var \App\Components\Locations\Models\Location $location */
        $location = app()->make(LocationsServiceInterface::class)->getLocation($locationId);

        return $location->runTemplates;
    }

    /**
     * {@inheritdoc}
     */
    public function createTemplate(int $locationId, string $name = null): JobRunTemplate
    {
        return JobRunTemplate::create([
            'location_id' => $locationId,
            'name'        => $name,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteTemplate(int $templateId): void
    {
        $template = $this->getTemplate($templateId);

        try {
            $template->delete();
        } catch (\Exception $exception) {
            throw new NotAllowedException('Could not be deleted since another entity refers to it.');
        }
    }
}
