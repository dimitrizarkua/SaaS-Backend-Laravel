<?php

namespace App\Components\Search\Models;

use App\Components\Search\SearchRules\UsersAndTeamsRule;
use App\Components\Search\UserAndTeamsIndexConfigurator;
use App\Components\Teams\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use ScoutElastic\Searchable;

/**
 * Class UserAndTeam
 *
 * @property int|null       $id
 * @property string|null    $name
 * @property string|null    $type
 * @property int            $entity_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Search\Models\UserAndTeam newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Search\Models\UserAndTeam newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Search\Models\UserAndTeam query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Search\Models\UserAndTeam whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Search\Models\UserAndTeam whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\Search\Models\UserAndTeam whereType($value)
 *
 * @property-read User|Team $entity
 *
 * @mixin \Eloquent
 */
class UserAndTeam extends Model
{
    use Searchable;

    public const TYPE_TEAM = 'team';
    public const TYPE_USER = 'user';

    /**
     * Name of the view.
     *
     * @var string
     */
    protected $table = 'users_and_teams_view';

    /**
     * Index configurator.
     *
     * @var string
     */
    protected $indexConfigurator = UserAndTeamsIndexConfigurator::class;

    /**
     * Search rules for model.
     *
     * @var array
     */
    protected $searchRules = [
        UsersAndTeamsRule::class,
    ];

    /**
     * Mapping for the model.
     *
     * @var array
     */
    protected $mapping = [
        'properties' => [
            'type'         => [
                'type' => 'keyword',
            ],
            'name'         => [
                'type'            => 'text',
                'analyzer'        => 'autocomplete',
                'search_analyzer' => 'autocomplete_search',
            ],
            'location_ids' => [
                'type' => 'long',
            ],

        ],
    ];

    /**
     * Entity relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entity()
    {
        return self::TYPE_USER === $this->type
            ? $this->belongsTo(User::class, 'entity_id')
            : $this->belongsTo(Team::class, 'entity_id');
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $result = $this->toArray();

        if (self::TYPE_USER === $this->type) {
            $result = array_merge($result, $this->entity->toSearchableArray());
        }

        return $result;
    }


    /**
     * Allows to search entities by name. Search result will be boosted by passed location ids.
     *
     * @param string $term        Search term.
     * @param array  $locationIds Array of location ids of user who is making the search.
     *
     * @return array
     */
    public static function filter(string $term, array $locationIds)
    {
        $options = [
            'term'        => $term,
            'locationIds' => $locationIds,
        ];

        $result = static::search($options)
            ->raw();

        return mapElasticResults($result);
    }
}
