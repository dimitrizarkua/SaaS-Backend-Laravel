<?php

namespace App\Observers;

use App\Components\AssessmentReports\Models\AssessmentReportEntity;
use App\Components\AssessmentReports\Models\AssessmentReportSectionEntity;
use App\Components\Finance\Models\FinancialEntityItem;
use App\Contracts\PositionableInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PositionableObserver
 *
 * @package App\Components\AssessmentReports
 */
class PositionableObserver
{
    /**
     * Handle assessment report entities "updated" event.
     *
     * @param Model $positionableEntity
     *
     * @return void
     */
    public function updated(Model $positionableEntity): void
    {
        if ($positionableEntity->isDirty('position')) {
            $this->updateOtherEntitiesPosition($positionableEntity);
        }
    }

    /**
     * Handle assessment report entities "created" event.
     *
     * @param Model $positionableEntity
     *
     * @return void
     */
    public function created(Model $positionableEntity): void
    {
        $this->updateOtherEntitiesPosition($positionableEntity);
    }

    /**
     * Updates position of other entities.
     *
     * @param Model|AssessmentReportSectionEntity|AssessmentReportEntity|FinancialEntityItem $model Model instance.
     *
     * @return void
     */
    private function updateOtherEntitiesPosition(Model $model): void
    {
        if (!$model instanceof PositionableInterface) {
            return;
        }

        $mapping = $model->getPositionableMapping();
        if (null === $mapping) {
            return;
        }

        $query       = $model::query()
            ->where($mapping->getIdField(), '!=', $model->{$mapping->getIdField()})
            ->when(null !== $mapping->getParentIdField(), function (Builder $query) use ($model, $mapping) {
                $query->where($mapping->getParentIdField(), $model->{$mapping->getParentIdField()});
            });
        $copiedQuery = clone $query;

        $existingEntityWithSamePosition = $copiedQuery
            ->where('position', '=', $model->position)
            ->count($mapping->getIdField());

        if (!$existingEntityWithSamePosition) {
            return;
        }

        $query->where('position', '>=', $model->position)
            ->increment('position');
    }
}
