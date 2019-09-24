<?php

namespace App\Components\RBAC\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class RoleUser
 *
 * @mixin \Eloquent
 * @property int $user_id
 * @property int $role_id
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\RBAC\Models\RoleUser whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Components\RBAC\Models\RoleUser whereUserId($value)
 */
class RoleUser extends Model
{
    protected $table = 'role_user';
}
