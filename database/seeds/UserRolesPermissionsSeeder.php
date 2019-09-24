<?php

use App\Components\RBAC\Models\Role;
use Illuminate\Database\Seeder;
use League\Csv\Reader;
use League\Csv\Statement;

/**
 * Class UserRolesPermissionsSeeder
 */
class UserRolesPermissionsSeeder extends Seeder
{
    /**
     * Seed the user roles and permissions.
     *
     * @return void
     * @throws \Throwable
     */
    public function run()
    {
        $csv = Reader::createFromPath(database_path('misc/roles.csv'), 'r');
        $csv->setHeaderOffset(0);

        $records = (new Statement())->process($csv);
        foreach ($records as $record) {
            $data = [
                'name'         => $record['name'],
                'display_name' => $record['display_name'],
                'description'  => $record['description'],
            ];

            $existing = DB::table('roles')
                ->where('name', $data['name'])
                ->first();

            if (!$existing) {
                DB::table('roles')->insert($data);
            } else {
                DB::table('roles')
                    ->where('name', $data['name'])
                    ->update($data);
            }
        }

        $roles = Role::query()
            ->select(['id', 'name'])
            ->get()
            ->keyBy('name')
            ->toArray();

        $filePath = database_path('misc/user_roles_permissions.csv');
        $csv      = Reader::createFromPath($filePath, 'r');
        $csv->setHeaderOffset(0);

        $records = (new Statement())->process($csv);

        foreach ($records as $record) {
            foreach ($record as $roleKey => $value) {
                if ($value === 'yes' && isset($roles[$roleKey])) {
                    $data = [
                        'permission' => $record['permission'],
                        'role_id'    => $roles[$roleKey]['id'],
                    ];

                    $existing = DB::table('permission_role')
                        ->where('role_id', $data['role_id'])
                        ->where('permission', $data['permission'])
                        ->first();

                    if (!$existing) {
                        DB::table('permission_role')->insert($data);
                    }
                }
            }
        }
    }
}
