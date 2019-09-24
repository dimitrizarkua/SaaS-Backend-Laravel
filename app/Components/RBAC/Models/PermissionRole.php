<?php

namespace App\Components\RBAC\Models;

use App\Models\HasCompositePrimaryKey;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PermissionRole
 *
 * @mixin \Eloquent
 * @property int    $role_id
 * @property string $permission
 * @property Role   $role
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\RBAC\Models\RolePermission
 *         wherePermission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\RBAC\Models\PermissionRole whereRoleId($value)
 */
class PermissionRole extends Model
{
    use HasCompositePrimaryKey;

    public $incrementing = false;
    public $timestamps   = false;

    protected $table      = 'permission_role';
    protected $fillable   = ['role_id', 'permission'];
    protected $primaryKey = ['role_id', 'permission'];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
}
