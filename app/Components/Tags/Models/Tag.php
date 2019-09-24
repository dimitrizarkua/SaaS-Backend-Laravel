<?php

namespace App\Components\Tags\Models;

use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Jobs\Models\Job;
use App\Models\ApiRequestFillable;
use App\Components\Tags\TagsIndexConfigurator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Laravel\Scout\Builder;
use ScoutElastic\Searchable;

/**
 * Class Tag
 *
 * @mixin \Eloquent
 *
 * @property int                             $id
 * @property string                          $type
 * @property string                          $name
 * @property boolean                         $is_alert
 * @property int                             $color
 * @property-read Collection|Job[]           $jobs
 * @property-read Collection|PurchaseOrder[] $purchaseOrders
 *
 * @OA\Schema(
 *     type="object",
 *     required={"id","type","name","is_alert"}
 * )
 */
class Tag extends Model
{
    use ApiRequestFillable, Searchable;

    public $timestamps = false;

    protected $indexConfigurator = TagsIndexConfigurator::class;

    protected $mapping = [
        'properties' => [
            'type' => [
                'type' => 'keyword',
            ],
            'name' => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete_search',
            ],
        ],
    ];

    /**
     * @OA\Property(property="id", type="integer", description="Tag identifier", example=1),
     * @OA\Property(property="type", type="string", description="Tag type", example="job"),
     * @OA\Property(property="name", type="string", description="Tag name", example="Urgent"),
     * @OA\Property(property="is_alert", type="boolean", description="Is alert", example=true),
     * @OA\Property(property="color", type="integer", description="Tag color", example=16777215),
     */

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Tagged jobs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function jobs(): BelongsToMany
    {
        return $this->belongsToMany(
            Job::class,
            'job_tag',
            'tag_id',
            'job_id'
        );
    }

    /**
     * Tagged purchase orders.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function purchaseOrders(): BelongsToMany
    {
        return $this->belongsToMany(
            PurchaseOrder::class,
            'purchase_order_tag',
            'tag_id',
            'purchase_order_id'
        );
    }

    /**
     * Allows to filter tags
     *
     * @param array $options
     *
     * @return \Laravel\Scout\Builder
     */
    public static function filter(array $options): Builder
    {
        $name  = $options['name'] ?? '*';
        $query = Tag::search($name);

        if (isset($options['type'])) {
            $query->where('type', $options['type']);
        }

        if (isset($options['count'])) {
            $query->take($options['count']);
        }

        return $query;
    }
}
