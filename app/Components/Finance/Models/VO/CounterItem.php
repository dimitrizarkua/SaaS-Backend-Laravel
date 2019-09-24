<?php

namespace App\Components\Finance\Models\VO;

use App\Core\JsonModel;

/**
 * Class CounterItem
 *
 * @package App\Components\Finance\Models\VO
 *
 * @OA\Schema(
 *     type="object",
 *     required={"count","amount"},
 * )
 */
class CounterItem extends JsonModel
{
    /**
     * @OA\Property(
     *     property="count",
     *     type="integer",
     *     description="Count of items in list",
     *     example=2
     * ),
     * @OA\Property(
     *     property="amount",
     *     type="number",
     *     description="Total amount of items in list",
     *     example="1000.00"
     * ),
     */

    /**
     * @var int
     */
    public $count = 0;
    /**
     * @var float
     */
    public $amount = 0;

    /**
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'count'  => $this->getCount(),
            'amount' => $this->getAmount(),
        ];
    }
}
