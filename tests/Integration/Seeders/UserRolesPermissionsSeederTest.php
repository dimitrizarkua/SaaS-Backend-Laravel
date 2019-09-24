<?php

namespace Tests\API\Auth;

use App\Components\Office365\Facades\GraphClient;
use App\Components\Office365\Models\UserResource;
use App\Components\RBAC\Interfaces\RBACServiceInterface;
use App\Components\RBAC\Models\Permission;
use App\Components\RBAC\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\Client;
use Tests\API\ApiTestCase;
use Tests\TestCase;
use UserRolesPermissionsSeeder;

/**
 * Class UserRolesPermissionsSeederTest
 *
 * @package Tests\API\Auth
 * @group   users
 * @group   api
 */
class UserRolesPermissionsSeederTest extends TestCase
{

    public function testUserRolesPermissionsSeederSuccess()
    {
        $testRoleName = 'accounts';

        $expectedPermissions = collect([
            'notes.view',
            'notes.create',
            'notes.update',
            'notes.delete',
            'messages.view',
            'messages.manage',
            'documents.create',
            'documents.view',
            'documents.download',
            'documents.delete',
            'addresses.view',
            'addresses.update',
            'addresses.create',
            'addresses.delete',
            'contacts.view',
            'contacts.update',
            'contacts.create',
            'contacts.delete',
            'meetings.create',
            'meetings.view',
            'meetings.delete',
            'jobs.view',
            'jobs.manage_inbox',
            'jobs.manage_tags',
            'jobs.assign_staff',
            'jobs.create',
            'jobs.update',
            'jobs.delete',
            'jobs.manage_contacts',
            'jobs.manage_notes',
            'jobs.manage_messages',
            'jobs.manage_jobs',
            'jobs.tasks.view',
            'jobs.tasks.manage',
            'jobs.manage_recurring',
            'jobs.usage.materials.create',
            'jobs.usage.materials.update',
            'jobs.usage.materials.delete',
            'jobs.usage.materials.manage',
            'jobs.usage.labour.create',
            'jobs.usage.labour.update',
            'jobs.usage.labour.delete',
            'jobs.usage.labour.manage',
            'jobs.usage.view',
            'jobs.usage.equipment.create',
            'jobs.usage.equipment.update',
            'jobs.usage.equipment.delete',
            'jobs.usage.equipment.manage',
            'jobs.areas.manage',
            'photos.create',
            'photos.view',
            'photos.update',
            'photos.delete',
            'finance.gl_accounts.manage',
            'finance.gl_accounts.view',
            'finance.gl_accounts.reports.view',
            'finance.gs_codes.manage',
            'finance.gs_codes.view',
            'finance.payments.create',
            'finance.payments.view',
            'finance.payments.receive',
            'finance.payments.transfers.receive',
            'finance.payments.forward',
            'finance.credit_notes.manage',
            'finance.credit_notes.view',
            'finance.credit_notes.manage_locked',
            'operations.staff.view',
            'operations.vehicles.view',
            'operations.tasks.view',
            'operations.runs.view',
            'finance.purchase_orders.view',
            'finance.purchase_orders.manage',
            'finance.purchase_orders.manage_locked',
            'finance.invoices.view',
            'finance.invoices.manage',
            'finance.invoices.manage_locked',
            'finance.invoices.reports.view',
            'finance.financial.reports.view',
            'allowances.view',
            'management.jobs.allowances',
            'laha.view',
            'management.jobs.laha',
            'jobs.usage.laha.manage',
            'jobs.usage.laha.approve',
            'jobs.usage.allowances.manage',
            'jobs.usage.allowances.approve',
            'jobs.usage.reimbursements.manage',
            'jobs.usage.reimbursements.approve',
            'equipment.view',
            'assessment_reports.view',
            'assessment_reports.manage',
            'assessment_reports.approve',
            'assessment_reports.manage_cancelled',
        ]);

        $this->seed('UserRolesPermissionsSeeder');

        $role = Role::where(['name' => $testRoleName])
            ->firstOrFail();

        $permissions = app()
            ->make(RBACServiceInterface::class)
            ->getRolesService()
            ->getPermissions($role->id)
            ->map(function (Permission $item) {
                return $item->getName();
            });

        self::assertCount($expectedPermissions->count(), $permissions);
        self::assertCount(0, $permissions->diff($expectedPermissions));
    }
}
