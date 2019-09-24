<?php

namespace App\Components\UsageAndActuals\Interfaces;

use App\Components\UsageAndActuals\Models\EquipmentCategory;
use App\Components\UsageAndActuals\Models\VO\EquipmentCategoryData;

/**
 * Interface EquipmentCategoriesInterface
 *
 * @package App\Components\Finance\Interfaces
 */
interface EquipmentCategoriesInterface
{
    /**
     * Creates new equipment category.
     *
     * @param \App\Components\UsageAndActuals\Models\VO\EquipmentCategoryData $data
     *
     * @return \App\Components\UsageAndActuals\Models\EquipmentCategory
     */
    public function createEquipmentCategory(EquipmentCategoryData $data): EquipmentCategory;
}
