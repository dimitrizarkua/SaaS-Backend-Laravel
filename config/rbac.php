<?php

return [

    /*
    |--------------------------------------------------------------------------
    | List of permissions
    |--------------------------------------------------------------------------
    | Each permission should be an array that can contains those keys:
    | - name        - required key that presents name of permission. Should be unique
    |                 across all permissions
    | - displayName - optional key
    | - description - optional key
    | - rule        - optional key. The callback function or any callable entity that
    |                 represents additional business logic fot permission. Into this
    |                 function will be passed two arguments:
    |                  + $user - current authenticate Users entity
    |                  + $arguments - any additional data that you would pass into
    |                                 $this->authorize($ability, $arguments) call inside of your
    |                                 controller
    */

    'permissions' => [
        // Users
        [
            'name'        => 'users.view',
            'displayName' => 'View users',
            'description' => 'Can view list of users and full information about each user',
        ],
        [
            'name'        => 'users.create',
            'displayName' => 'Create users',
            'description' => 'Can create user profile',
        ],
        [
            'name'        => 'users.update',
            'displayName' => 'Update users',
            'description' => 'Can update user profile',
        ],
        [
            'name'        => 'users.delete',
            'displayName' => 'Users delete',
            'description' => 'Can delete users',
        ],
        // Roles and permissions
        [
            'name'        => 'roles.view',
            'displayName' => 'View all roles',
            'description' => 'Can view list of all roles and full information about each one',
        ],
        [
            'name'        => 'roles.update',
            'displayName' => 'Update role',
            'description' => 'Can update existing role',
        ],
        [
            'name'        => 'roles.create',
            'displayName' => 'Create role',
            'description' => 'Can create new role',
        ],
        [
            'name'        => 'roles.delete',
            'displayName' => 'Delete role',
            'description' => 'Can delete existing role',
        ],
        [
            'name'        => 'permissions.view',
            'displayName' => 'View permissions',
            'description' => 'Can view list of all permissions and full information about each one',
        ],
        // Tags
        [
            'name'        => 'tags.view',
            'displayName' => 'View all tags',
            'description' => 'Can view list of all tags and full information about each one',
        ],
        [
            'name'        => 'tags.update',
            'displayName' => 'Update tag',
            'description' => 'Can update existing tag',
        ],
        [
            'name'        => 'tags.create',
            'displayName' => 'Create tag',
            'description' => 'Can create new tag',
        ],
        [
            'name'        => 'tags.delete',
            'displayName' => 'Delete tag',
            'description' => 'Can delete existing tag',
        ],
        // Notes
        [
            'name'        => 'notes.view',
            'displayName' => 'View all notes',
            'description' => 'Can view list of all notes and full information about each one',
        ],
        [
            'name'        => 'notes.create',
            'displayName' => 'Create notes',
            'description' => 'Can create new note',
        ],
        [
            'name'        => 'notes.update',
            'displayName' => 'Update note',
            'description' => 'Can update existing note',
        ],
        [
            'name'        => 'notes.delete',
            'displayName' => 'Delete note',
            'description' => 'Can delete existing note',
        ],
        // Messages
        [
            'name'        => 'messages.view',
            'displayName' => 'View all notes',
            'description' => 'Can view list of all notes and full information about each one',
        ],
        [
            'name'        => 'messages.manage',
            'displayName' => 'Create, update and delete own messages',
            'description' => 'Can create new message, update and delete draft messages.',
        ],
        // Documents
        [
            'name'        => 'documents.create',
            'displayName' => 'Create document',
            'description' => 'Can create new document',
        ],
        [
            'name'        => 'documents.view',
            'displayName' => 'View document info',
            'description' => 'Can view existing document info',
        ],
        [
            'name'        => 'documents.download',
            'displayName' => 'Download documents',
            'description' => 'Can download documents',
        ],
        [
            'name'        => 'documents.delete',
            'displayName' => 'Delete document',
            'description' => 'Can delete existing document',
        ],
        // Countries
        [
            'name'        => 'countries.view',
            'displayName' => 'View all countries',
            'description' => 'Can view list of all countries and full information about each one',
        ],
        [
            'name'        => 'countries.create',
            'displayName' => 'Create country',
            'description' => 'Can create new country',
        ],
        [
            'name'        => 'countries.delete',
            'displayName' => 'Delete country',
            'description' => 'Can delete existing country',
        ],
        // States
        [
            'name'        => 'states.view',
            'displayName' => 'View all states',
            'description' => 'Can view list of all states and full information about each one',
        ],
        [
            'name'        => 'states.update',
            'displayName' => 'Update state',
            'description' => 'Can update existing state',
        ],
        [
            'name'        => 'states.create',
            'displayName' => 'Create state',
            'description' => 'Can create new state',
        ],
        [
            'name'        => 'states.delete',
            'displayName' => 'Delete state',
            'description' => 'Can delete existing state',
        ],
        // States
        [
            'name'        => 'suburbs.view',
            'displayName' => 'View all suburbs',
            'description' => 'Can view list of all suburbs and full information about each one',
        ],
        [
            'name'        => 'suburbs.update',
            'displayName' => 'Update suburb',
            'description' => 'Can update existing suburb',
        ],
        [
            'name'        => 'suburbs.create',
            'displayName' => 'Create suburb',
            'description' => 'Can create new suburb',
        ],
        [
            'name'        => 'suburbs.delete',
            'displayName' => 'Delete suburb',
            'description' => 'Can delete existing suburb',
        ],
        // Addresses
        [
            'name'        => 'addresses.view',
            'displayName' => 'View all addresses',
            'description' => 'Can view list of all addresses and full information about each one',
        ],
        [
            'name'        => 'addresses.update',
            'displayName' => 'Update address',
            'description' => 'Can update existing address',
        ],
        [
            'name'        => 'addresses.create',
            'displayName' => 'Create address',
            'description' => 'Can create new address',
        ],
        [
            'name'        => 'addresses.delete',
            'displayName' => 'Delete address',
            'description' => 'Can delete existing address',
        ],
        //Contacts
        [
            'name'        => 'contacts.view',
            'displayName' => 'View all contacts',
            'description' => 'Can view list of all contacts and full information about each one',
        ],
        [
            'name'        => 'contacts.update',
            'displayName' => 'Update contact',
            'description' => 'Can update existing contact',
        ],
        [
            'name'        => 'contacts.create',
            'displayName' => 'Create contact',
            'description' => 'Can create new contact',
        ],
        [
            'name'        => 'contacts.delete',
            'displayName' => 'Delete contact',
            'description' => 'Can delete existing contact',
        ],
        [
            'name'        => 'contacts.reports.view',
            'displayName' => 'View contacts reports',
            'description' => 'Can view contacts reports',
        ],
        //Meetings
        [
            'name'        => 'meetings.create',
            'displayName' => 'Create meeting',
            'description' => 'Can create new meetings',
        ],
        [
            'name'        => 'meetings.view',
            'displayName' => 'View meeting',
            'description' => 'Can view list of all meetings and full information about each one',
        ],
        [
            'name'        => 'meetings.delete',
            'displayName' => 'Delete meeting',
            'description' => 'Can delete existing meeting',
        ],
        //Teams
        [
            'name'        => 'teams.create',
            'displayName' => 'Create team',
            'description' => 'Can create new team',
        ],
        [
            'name'        => 'teams.update',
            'displayName' => 'Update team',
            'description' => 'Can update existing team',
        ],
        [
            'name'        => 'teams.view',
            'displayName' => 'View team',
            'description' => 'Can view list of all team and full information about each one',
        ],
        [
            'name'        => 'teams.delete',
            'displayName' => 'Delete team',
            'description' => 'Can delete existing team',
        ],
        [
            'name'        => 'teams.modify_members',
            'displayName' => 'Modify team users',
            'description' => 'Can add and remove users from team',
        ],
        // Locations
        [
            'name'        => 'locations.view',
            'displayName' => 'View all locations',
            'description' => 'Can view list of all location, information about each one, their members and suburbs',
        ],
        [
            'name'        => 'locations.create',
            'displayName' => 'Create location',
            'description' => 'Can create new location',
        ],
        [
            'name'        => 'locations.update',
            'displayName' => 'Update location',
            'description' => 'Can update existing location',
        ],
        [
            'name'        => 'locations.modify_members',
            'displayName' => 'Modify location members',
            'description' => 'Can add and remove users from locations members',
        ],
        [
            'name'        => 'locations.modify_suburbs',
            'displayName' => 'Modify location suburbs',
            'description' => 'Can add and remove suburbs from locations',
        ],
        // Jobs
        [
            'name'        => 'jobs.view',
            'displayName' => 'View simple information about job',
            'description' => 'Can view simple information about job',
        ],
        [
            'name'        => 'jobs.manage_inbox',
            'displayName' => 'Pin and snooze job in inbox',
            'description' => 'Can pin/unpin snooze/unsnooze jobs in the inbox',
        ],
        [
            'name'        => 'jobs.manage_tags',
            'displayName' => 'Manage job tags',
            'description' => 'Can add and remove tags from jobs',
        ],
        [
            'name'        => 'jobs.assign_staff',
            'displayName' => 'Assign staff to jobs',
            'description' => 'Can assign and unassign users and teams from jobs',
        ],
        [
            'name'        => 'jobs.create',
            'displayName' => 'Create job',
            'description' => 'Can create new job',
        ],
        [
            'name'        => 'jobs.update',
            'displayName' => 'Update job',
            'description' => 'Can update existing job',
        ],
        [
            'name'        => 'jobs.delete',
            'displayName' => 'Delete job',
            'description' => 'Can update existing job',
        ],
        [
            'name'        => 'jobs.manage_contacts',
            'displayName' => 'Assign contacts to jobs',
            'description' => 'Can assign and unassign contacts from jobs, manage existing assignments',
        ],
        [
            'name'        => 'jobs.manage_notes',
            'displayName' => 'Manage job notes',
            'description' => 'Can add and delete job notes',
        ],
        [
            'name'        => 'jobs.manage_messages',
            'displayName' => 'Manage job messages',
            'description' => 'Can add job messages',
        ],
        [
            'name'        => 'jobs.manage_messages_detach',
            'displayName' => 'Detach job messages',
            'description' => 'Can detach job messages',
        ],
        [
            'name'        => 'jobs.manage_jobs',
            'displayName' => 'Link jobs',
            'description' => 'Can link and unlink jobs',
        ],
        [
            'name'        => 'jobs.tasks.view',
            'displayName' => 'View and browse tasks',
            'description' => 'Can view and browse job tasks',
        ],
        [
            'name'        => 'jobs.tasks.manage',
            'displayName' => 'Manage tasks',
            'description' => 'Can manage job tasks',
        ],
        [
            'name'        => 'jobs.manage_recurring',
            'displayName' => 'Manage recurring jobs',
            'description' => 'Allows to manage recurring jobs',
        ],
        [
            'name'        => 'jobs.usage.materials.create',
            'displayName' => 'Create new job material',
            'description' => 'Allows to create new job material',
        ],
        [
            'name'        => 'jobs.usage.materials.update',
            'displayName' => 'Update job material',
            'description' => 'Allows to update job material',
        ],
        [
            'name'        => 'jobs.usage.materials.delete',
            'displayName' => 'Delete job material',
            'description' => 'Allows to delete job material',
        ],
        [
            'name'        => 'jobs.usage.materials.manage',
            'displayName' => 'Manage job material',
            'description' => 'Allows to manage job material',
        ],
        [
            'name'        => 'jobs.usage.labour.create',
            'displayName' => 'Create new job labour',
            'description' => 'Allows to create new job labour',
        ],
        [
            'name'        => 'jobs.usage.labour.update',
            'displayName' => 'Update job labour',
            'description' => 'Allows to update job labour',
        ],
        [
            'name'        => 'jobs.usage.labour.delete',
            'displayName' => 'Delete job labour',
            'description' => 'Allows to delete job labour',
        ],
        [
            'name'        => 'jobs.usage.labour.manage',
            'displayName' => 'Manage job labour',
            'description' => 'Allows to manage job labour',
        ],

        [
            'name'        => 'jobs.usage.view',
            'displayName' => 'View job usages',
            'description' => 'Allows to view job\'s usage and actuals entries',
        ],
        [
            'name'        => 'jobs.usage.equipment.create',
            'displayName' => 'Create job equipment',
            'description' => 'Allows to create job equipment entries',
        ],
        [
            'name'        => 'jobs.usage.equipment.update',
            'displayName' => 'Update job equipment',
            'description' => 'Allows to update job equipment entries',
        ],
        [
            'name'        => 'jobs.usage.equipment.delete',
            'displayName' => 'Delete job equipment',
            'description' => 'Allows to delete job equipment entries',
        ],
        [
            'name'        => 'jobs.usage.equipment.manage',
            'displayName' => 'Manage job\'s equipment',
            'description' => 'Allows to manage job equipment entries that were created other users',
        ],
        [
            'name'        => 'jobs.areas.manage',
            'displayName' => 'Manage areas',
            'description' => 'Can manage job areas',
        ],
        // Management
        [
            'name'        => 'management.search.index',
            'displayName' => 'Create search index',
            'description' => 'Allows to import the models into the search index',
        ],
        [
            'name'        => 'management.search.flush',
            'displayName' => 'Flush search index',
            'description' => 'Allows to flush all of the model\'s records from the index',
        ],
        [
            'name'        => 'management.system.settings',
            'displayName' => 'Manage system settings',
            'description' => 'Allows to manage system settings',
        ],
        [
            'name'        => 'management.jobs.settings',
            'displayName' => 'Manage jobs settings',
            'description' => 'Allows to manage jobs settings',
        ],
        [
            'name'        => 'management.equipment',
            'displayName' => 'Manage equipment',
            'description' => 'Allows to manage equipment',
        ],
        [
            'name'        => 'management.seed',
            'displayName' => 'Run seeder',
            'description' => 'Allows to run existing database seeder',
        ],
        // Photos
        [
            'name'        => 'photos.create',
            'displayName' => 'Create photo',
            'description' => 'Can create new photo',
        ],
        [
            'name'        => 'photos.view',
            'displayName' => 'View and download photo',
            'description' => 'Can view and download existing photo',
        ],
        [
            'name'        => 'photos.update',
            'displayName' => 'Delete photo',
            'description' => 'Can delete existing photo',
        ],
        [
            'name'        => 'photos.delete',
            'displayName' => 'Delete photo',
            'description' => 'Can delete existing photo',
        ],
        // Finance
        [
            'name'        => 'finance.accounting_organizations.manage',
            'displayName' => 'Manage accounting organizations',
            'description' => 'Allows to manage accounting organizations',
        ],
        // GL accounts - start
        [
            'name'        => 'finance.gl_accounts.manage',
            'displayName' => 'Manage GL Account',
            'description' => 'Allows to manage GL Account',
        ],
        [
            'name'        => 'finance.gl_accounts.view',
            'displayName' => 'View GL accounts',
            'description' => 'Can view GL accounts',
        ],
        [
            'name'        => 'finance.gl_accounts.reports.view',
            'displayName' => 'View GL account reports',
            'description' => 'Can view GL account reports',
        ],
        // GL accounts - end
        // GS codes - start
        [
            'name'        => 'finance.gs_codes.manage',
            'displayName' => 'Manage GS Codes',
            'description' => 'Allows to manage GS codes',
        ],
        [
            'name'        => 'finance.gs_codes.view',
            'displayName' => 'View GS codes',
            'description' => 'Can view GS codes',
        ],
        // GL codes - end
        // Payments - start
        [
            'name'        => 'finance.payments.create',
            'displayName' => 'Create a payment',
            'description' => 'Allows to create a new Payment',
        ],
        [
            'name'        => 'finance.payments.view',
            'displayName' => 'View payment',
            'description' => 'Allows to view payment(s)',
        ],
        [
            'name'        => 'finance.payments.receive',
            'displayName' => 'Receive payments',
            'description' => 'Allows to receive payment(s)',
        ],
        [
            'name'        => 'finance.payments.transfers.receive',
            'displayName' => 'Receive payments from HQ to branch/franchise',
            'description' => 'Allows to receive payment(s) from HQ to branch/franchise',
        ],
        [
            'name'        => 'finance.payments.forward',
            'displayName' => 'Forward a payment to branch/franchise.',
            'description' => 'Allows to forward a payment to branch/franchise.',
        ],
        // Payments - end
        // Credit notes
        [
            'name'        => 'finance.credit_notes.manage',
            'displayName' => 'Manage a credit note',
            'description' => 'Allows to manage a Credit Note',
        ],
        [
            'name'        => 'finance.credit_notes.view',
            'displayName' => 'View a credit note',
            'description' => 'Allows to view a Credit Note',
        ],
        [
            'name'        => 'finance.credit_notes.manage_locked',
            'displayName' => 'Manage locked credit note except already approved',
            'description' => 'Can manage locked credit note except already approved',
        ],
        // Credit notes -end
        // Operations
        [
            'name'        => 'operations.staff.view',
            'displayName' => 'View and browse staff',
            'description' => 'Can view and browse staff',
        ],
        [
            'name'        => 'operations.vehicles.view',
            'displayName' => 'View and browse vehicles',
            'description' => 'Can view and browse vehicles',
        ],
        [
            'name'        => 'operations.vehicles.manage',
            'displayName' => 'Edit, update, delete vehicles',
            'description' => 'Can edit, update and delete vehicles',
        ],
        [
            'name'        => 'operations.vehicles.change_status',
            'displayName' => 'Change status of a vehicle',
            'description' => 'Can change status of a vehicle',
        ],
        [
            'name'        => 'operations.tasks.view',
            'displayName' => 'View and browse job tasks',
            'description' => 'Can view and browse job tasks',
        ],
        [
            'name'        => 'operations.runs.view',
            'displayName' => 'View and browse job runs',
            'description' => 'Can view and browse job runs',
        ],
        [
            'name'        => 'operations.runs.manage',
            'displayName' => 'Edit, update, delete job runs',
            'description' => 'Can edit, update and delete job runs',
        ],
        [
            'name'        => 'operations.runs_templates.view',
            'displayName' => 'View and browse run templates',
            'description' => 'Can view and browse run templates',
        ],
        [
            'name'        => 'operations.runs_templates.manage',
            'displayName' => 'Edit, update, delete run templates',
            'description' => 'Can edit, update and delete run templates',
        ],
        // Finance: purchase orders
        [
            'name'        => 'finance.purchase_orders.view',
            'displayName' => 'View purchase order',
            'description' => 'Allows to view purchase order',
        ],
        [
            'name'        => 'finance.purchase_orders.manage',
            'displayName' => 'Create purchase order',
            'description' => 'Allows to manage purchase order',
        ],
        [
            'name'        => 'finance.purchase_orders.manage_locked',
            'displayName' => 'Modify locked purchase order',
            'description' => 'Allows to manage locked (not approved) purchase order',
        ],
        // Finance: invoices
        [
            'name'        => 'finance.invoices.view',
            'displayName' => 'View invoice',
            'description' => 'Allows to view invoices',
        ],
        [
            'name'        => 'finance.invoices.manage',
            'displayName' => 'Manage invoice',
            'description' => 'Allows to manage invoices',
        ],
        [
            'name'        => 'finance.invoices.manage_locked',
            'displayName' => 'Modify locked invoices',
            'description' => 'Allows to modify locked invoices',
        ],
        //Finance: reports
        [
            'name'        => 'finance.invoices.reports.view',
            'displayName' => 'View invoice report',
            'description' => 'Allows to view invoices payment report',
        ],
        [
            'name'        => 'finance.financial.reports.view',
            'displayName' => 'View financial volume report',
            'description' => 'Allows to view financial volume report',
        ],
        //Usage and actuals: insurer contracts
        [
            'name'        => 'usage_and_actuals.insurer_contracts.view',
            'displayName' => 'View insurer contracts',
            'description' => 'Can view insurer contracts',
        ],
        [
            'name'        => 'usage_and_actuals.insurer_contracts.manage',
            'displayName' => 'Manage insurer contracts',
            'description' => 'Can manage insurer contracts',
        ],
        //Usage and actuals: measure units
        [
            'name'        => 'jobs.usage.view',
            'displayName' => 'View measure units',
            'description' => 'Can view measure units',
        ],
        [
            'name'        => 'management.materials.measure_units',
            'displayName' => 'Manage measure units',
            'description' => 'Can manage measure units',
        ],
        //Usage and actuals: materials
        [
            'name'        => 'jobs.usage.view',
            'displayName' => 'View materials',
            'description' => 'Can view materials',
        ],
        [
            'name'        => 'management.materials',
            'displayName' => 'Manage materials',
            'description' => 'Can manage materials',
        ],
        //Usage and actuals: labour types
        [
            'name'        => 'labour.view',
            'displayName' => 'View labour types',
            'description' => 'Can view labour types',
        ],
        [
            'name'        => 'management.jobs.labour',
            'displayName' => 'Manage labour types',
            'description' => 'Can manage labour types',
        ],
        //Usage and actuals: allowance types
        [
            'name'        => 'allowances.view',
            'displayName' => 'View allowance types',
            'description' => 'Can view allowance types',
        ],
        [
            'name'        => 'management.jobs.allowances',
            'displayName' => 'Manage allowance types',
            'description' => 'Can manage allowance types',
        ],
        //Usage and actuals: laha compensations
        [
            'name'        => 'laha.view',
            'displayName' => 'View laha compensations',
            'description' => 'Can view laha compensations',
        ],
        [
            'name'        => 'management.jobs.laha',
            'displayName' => 'Manage laha compensations',
            'description' => 'Can manage laha compensations',
        ],
        //Usage and actuals: job laha compensations
        [
            'name'        => 'jobs.usage.view',
            'displayName' => 'View job laha compensations',
            'description' => 'Can view job laha compensations',
        ],
        [
            'name'        => 'jobs.usage.laha.manage',
            'displayName' => 'Manage job laha compensations',
            'description' => 'Can manage job laha compensations',
        ],
        [
            'name'        => 'jobs.usage.laha.approve',
            'displayName' => 'Approve laha compensations',
            'description' => 'Can approve laha compensations',
        ],
        //Usage and actuals: job allowances
        [
            'name'        => 'jobs.usage.view',
            'displayName' => 'View job allowances',
            'description' => 'Can view job allowances',
        ],
        [
            'name'        => 'jobs.usage.allowances.manage',
            'displayName' => 'Manage job allowances',
            'description' => 'Can manage job allowances',
        ],
        [
            'name'        => 'jobs.usage.allowances.approve',
            'displayName' => 'Approve allowances',
            'description' => 'Can approve allowances',
        ],

        //Usage and actuals: job reimbursements
        [
            'name'        => 'jobs.usage.view',
            'displayName' => 'View job reimbursements',
            'description' => 'Can view job reimbursements',
        ],
        [
            'name'        => 'jobs.usage.reimbursements.manage',
            'displayName' => 'Manage job reimbursements',
            'description' => 'Can manage job reimbursements',
        ],
        [
            'name'        => 'jobs.usage.reimbursements.approve',
            'displayName' => 'Approve reimbursements',
            'description' => 'Can approve laha compensations',
        ],

        // Usage and actuals: equipment
        [
            'name'        => 'equipment.view',
            'displayName' => 'View equipment',
            'description' => 'Allows to view equipment',
        ],
        [
            'name'        => 'equipment.notes.edit',
            'displayName' => 'Edit equipment notes',
            'description' => 'Allows to attach/detach note to/from equipment',
        ],
        // Assessment Reports
        [
            'name'        => 'assessment_reports.view',
            'displayName' => 'View assessment reports',
            'description' => 'Can view assessment reports',
        ],
        [
            'name'        => 'assessment_reports.manage',
            'displayName' => 'Manage assessment reports',
            'description' => 'Can manage assessment reports',
        ],
        [
            'name'        => 'assessment_reports.approve',
            'displayName' => 'Approve assessment reports',
            'description' => 'Allows to approve assessment reports',
        ],
        [
            'name'        => 'assessment_reports.manage_cancelled',
            'displayName' => 'Manage cancelled assessment reports',
            'description' => 'Allows to change status of cancelled assessment reports',
        ],
    ],
];
