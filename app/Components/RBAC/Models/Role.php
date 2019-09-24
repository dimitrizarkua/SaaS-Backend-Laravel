<?php

namespace App\Components\RBAC\Models;

use App\Components\RBAC\PermissionAwareTrait;
use App\Models\ApiRequestFillable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Role
 *
 * @property int                              $id
 * @property string                           $name
 * @property string|null                      $display_name
 * @property string|null                      $description
 * @property \Illuminate\Support\Carbon       $created_at
 * @property \Illuminate\Support\Carbon       $updated_at
 * @property User[]                           $users
 * @property-read Collection|PermissionRole[] $permissions
 * @method static Builder|Role whereCreatedAt($value)
 * @method static Builder|Role whereDescription($value)
 * @method static Builder|Role whereDisplayName($value)
 * @method static Builder|Role whereId($value)
 * @method static Builder|Role whereName($value)
 * @method static Builder|Role whereUpdatedAt($value)
 * @mixin \Eloquent
 * @OA\Schema (
 *     type="object",
 *     required={"id","name","display_name","description"}
 * )
 */
class Role extends Model
{
    use PermissionAwareTrait, ApiRequestFillable;

    protected $guarded = ['id'];

    public $timestamps = false;

    /**
     * @OA\Property(property="id", type="integer", description="Role identifier", example=1),
     * @OA\Property(property="name", type="string", description="Role name", example="admin"),
     * @OA\Property(property="display_name", type="string", description="Display name", example="Admin"),
     * @OA\Property(
     *     property="description",
     *     type="string",
     *     description="Role description",
     *     example="Allows to manage many internal resources"
     * )
     */

    /**
     * Define users relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user');
    }

    /**
     * Define relationship with role_permission table.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissions()
    {
        return $this->hasMany(PermissionRole::class);
    }

    /**
     * Returns collection of permissions.
     *
     * @return Collection|Permission[]
     */
    public function getPermissions(): Collection
    {
        $permissionList = $this->permissions->map(function (PermissionRole $permission) {
            return $this->getPermissionInstance($permission->permission);
        });

        return Collection::make($permissionList);
    }

    /**
     * Checks whether the role has permission.
     *
     * @param \App\Components\RBAC\Models\Permission $permission
     *
     * @return bool
     */
    public function hasPermission(Permission $permission): bool
    {
        $roles = Role::whereHas('permissions', function (Builder $query) use ($permission) {
            $query->where('name', $permission->getName());
        })->find($this->id);


        return null !== $roles;
    }
}
