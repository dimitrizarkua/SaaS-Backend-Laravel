<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/env', 'MonitoringController@getEnv');

Route::middleware('auth:api')
    ->post('/broadcasting/auth', 'Broadcast\AuthController@authenticate');

Route::namespace('Auth')->prefix('auth')
    ->group(function () {
        Route::post('/password/forgot', 'ForgotPasswordController@forgot');
        Route::post('/password/reset', 'ForgotPasswordController@setPassword');
    });

Route::post(
    '/webhooks/c078a402cc8868d062ad1b371ed7e5128a5952147bddc02ba08167b60d94748b',
    'WebhooksController@handleMailgunMessageStatusUpdateWebhook'
);
Route::post(
    '/webhooks/542d8ff15b46da27fee939c95f8cc1f9c99b1fad09d39fabb7c93612ef104d89',
    'WebhooksController@handleMailgunIncomingJobMessageWebhook'
);

//Auth protected routes
Route::namespace('Users')->middleware('auth:api')
    ->group(function () {
        Route::get('me', 'UserProfileController@getProfile')->name('user-profile');
        Route::patch('me', 'UserProfileController@updateProfile');
        Route::get('me/locations', 'UserProfileController@getLocations')->name('user-locations');
        Route::get('users/search/mentions', 'UsersController@search')->name('users-search-mentions');
        Route::get('me/teams', 'UserProfileController@getTeams')->name('user-teams');
        Route::post('me/avatar', 'UserProfileController@updateAvatar');
        Route::delete('me/avatar', 'UserProfileController@deleteAvatar');

        Route::get('me/notifications', 'UserNotificationsController@listUnreadNotifications')
            ->name('user-notifications');
        Route::delete('me/notifications', 'UserNotificationsController@readAll');
        Route::delete('me/notifications/{notification_id}', 'UserNotificationsController@read');
    });

Route::middleware('auth:api')->namespace('RBAC')
    ->group(function () {
        Route::apiResource('users', 'UsersController');
        Route::apiResource('roles', 'RolesController');
        Route::apiResource('permissions', 'PermissionsController');

        Route::get('/users/{user}/roles', 'UserRolesController@getUserRoles')
            ->middleware('can:users.view');
        Route::post('/users/{user}/roles', 'UserRolesController@attachRole')
            ->middleware('can:users.update');
        Route::patch('/users/{user}/roles', 'UserRolesController@changeRole')
            ->middleware('can:users.update');
        Route::delete('/users/{user}/roles', 'UserRolesController@detachRoles')
            ->middleware('can:users.update');
        Route::get('/roles/{role}/permissions', 'PermissionsController@getRolePermissions')
            ->middleware('can:roles.view');
        Route::post('/roles/{role}/permissions', 'PermissionsController@attachPermissionsToRole')
            ->middleware('can:roles.update');
        Route::delete('/roles/{role}/permissions', 'PermissionsController@detachPermissionsFromRole')
            ->middleware('can:roles.update');
    });

Route::middleware('auth:api')->namespace('Tags')
    ->group(function () {
        Route::get('/tags/search', 'TagsController@search');
        Route::apiResource('tags', 'TagsController')->except('index');
    });

Route::middleware('auth:api')->namespace('Notes')
    ->group(function () {
        Route::apiResource('notes', 'NotesController')->except('index');

        Route::post('/notes/{note}/documents/{document}', 'NotesController@attachDocument');
        Route::delete('/notes/{note}/documents/{document}', 'NotesController@detachDocument');
    });

Route::middleware('auth:api')->namespace('Messages')
    ->group(function () {
        Route::apiResource('messages', 'MessagesController')->except('index');

        Route::post('/messages/{message}/documents/{document}', 'MessagesController@attachDocument');
        Route::delete('/messages/{message}/documents/{document}', 'MessagesController@detachDocument');
    });

Route::middleware('auth:api')->namespace('Documents')
    ->group(function () {
        Route::apiResource('documents', 'DocumentsController')->except('index', 'update');

        Route::get('/documents/{document}/download', 'DocumentsController@download');
    });

Route::middleware('auth:api')->namespace('Addresses')
    ->group(function () {
        Route::get('suburbs/search', 'SuburbsController@searchSuburbs');

        Route::apiResource('countries', 'CountriesController')->except('update');
        Route::apiResource('states', 'StatesController');
        Route::apiResource('suburbs', 'SuburbsController');
        Route::apiResource('addresses', 'AddressController');

        Route::post('addresses/parse', 'AddressController@parseAddress');
    });

Route::middleware('auth:api')->namespace('Contacts')
    ->group(function () {
        // Contact categories
        Route::apiResource('contacts/categories', 'ContactCategoriesController');

        Route::patch('/contacts/{contact}/status', 'ContactStatusesController@changeStatus');
        Route::apiResource('contacts/statuses', 'ContactStatusesController')->except('store', 'show', 'destroy');

        // Contact notes
        Route::get('/contacts/{contact}/notes', 'ContactNotesController@getContactNotes');
        Route::get('/contacts/{contact}/notes/{note}', 'ContactNotesController@viewContactNote');
        Route::post('/contacts/{contact}/notes/{note}', 'ContactNotesController@addContactNote');
        Route::delete('/contacts/{contact}/notes/{note}', 'ContactNotesController@deleteContactNote');

        // Contact tags
        Route::get('/contacts/{contact}/tags', 'ContactTagsController@getContactTags');
        Route::post('/contacts/{contact}/tags/{tag}', 'ContactTagsController@addContactTag');
        Route::delete('/contacts/{contact}/tags/{tag}', 'ContactTagsController@deleteContactTag');

        // Contact addresses
        Route::get('/contacts/{contact}/addresses', 'ContactAddressesController@getContactAddresses');
        Route::post('/contacts/{contact}/addresses/{address}', 'ContactAddressesController@addContactAddress');
        Route::delete('/contacts/{contact}/addresses/{address}', 'ContactAddressesController@deleteContactAddress');

        // Contact accounts
        Route::post('/contacts/{contact}/users/{user}', 'ContactAccountsController@addManagedAccount');
        Route::delete('/contacts/{contact}/users/{user}', 'ContactAccountsController@deleteManagedAccount');

        // Contact avatars
        Route::post('/contacts/{contact}/avatar', 'ContactsController@updateAvatar');
        Route::delete('/contacts/{contact}/avatar', 'ContactsController@deleteAvatar');

        // Contacts
        Route::get('/contacts/search', 'ContactsController@search');
        Route::post('/contacts/person', 'ContactsController@addPerson');
        Route::post('/contacts/company', 'ContactsController@addCompany');
        Route::post('/contacts/{parent}/{child}', 'ContactsController@linkContact')
            ->where('parent', '\d+')
            ->where('child', '\d+');
        Route::delete('/contacts/{parent}/{child}', 'ContactsController@unlinkContact')
            ->where('parent', '\d+')
            ->where('child', '\d+');

        Route::apiResource('contacts', 'ContactsController')->except('store');
    });

Route::middleware('auth:api')->namespace('Reporting')
    ->group(function () {
        Route::get('/contacts/reports/volume', 'ReportingContactsController@volumeReport');
    });

Route::middleware('auth:api')->namespace('Meetings')
    ->group(function () {
        Route::apiResource('meetings', 'MeetingsController')->except('index', 'update');
    });

Route::middleware('auth:api')->namespace('Locations')
    ->group(function () {
        Route::apiResource('locations', 'LocationsController');

        Route::get('/locations/{location}/users', 'LocationsController@getUsers');
        Route::post('/locations/{location}/users/{user}', 'LocationsController@addUser');
        Route::delete('/locations/{location}/users/{user}', 'LocationsController@removeUser');

        Route::get('/locations/{location}/suburbs', 'LocationsController@getSuburbs');
        Route::post('/locations/{location}/suburbs/{suburb}', 'LocationsController@addSuburb');
        Route::delete('/locations/{location}/suburbs/{suburb}', 'LocationsController@removeSuburb');
        Route::get(
            '/locations/{location}/accounting-organization',
            'LocationsController@getAccountingOrganizationByLocation'
        );
    });

Route::middleware('auth:api')->namespace('Teams')
    ->group(function () {
        Route::apiResource('teams', 'TeamsController');
        Route::get('/teams/{team}/users', 'TeamsController@getMembers');
        Route::post('/teams/{team}/users/{user}', 'TeamsController@addUser');
        Route::delete('/teams/{team}/users/{user}', 'TeamsController@deleteUser');
    });

Route::middleware('auth:api')->namespace('Photos')
    ->group(function () {
        Route::post('/photos/{photo}', 'PhotosController@reupload');
        Route::get('/photos/{photo}/download', 'PhotosController@download');
        Route::get('/photos/download-multiple', 'PhotosController@downloadMultiple');

        Route::apiResource('photos', 'PhotosController')->except('update');
    });

Route::middleware('auth:api')->namespace('Jobs')
    ->group(function () {
        /**
         * Jobs listing endpoints
         */
        Route::get('/jobs/search', 'JobsController@search');
        Route::get('/jobs/info', 'JobListController@info');
        Route::get('/jobs/inbox', 'JobListController@inbox');
        Route::get('/jobs/local', 'JobListController@local');
        Route::get('/jobs/mine', 'JobListController@mine');
        Route::get('/jobs/mine/active', 'JobListController@mineActive');
        Route::get('/jobs/mine/closed', 'JobListController@mineClosed');
        Route::get('/jobs/mine/teams/{team}', 'JobListController@mineTeams');
        Route::get('/jobs/{job}/previous', 'JobListController@listPrevious');
        Route::get('/jobs/mine/no-contact', 'JobListController@noContact24Hours');
        Route::get('/jobs/mine/upcoming-kpi', 'JobListController@upcomingKpi');
        /** End of listing endpoint */

        Route::apiResource('jobs/services', 'JobServicesController');
        Route::apiResource('jobs/message-templates', 'JobNotesTemplatesController');
        Route::apiResource('jobs', 'JobsController')->except('index');
        Route::post('jobs/{job}/duplicate', 'JobsController@duplicate');

        Route::get('/jobs/recurring/check', 'RecurringJobController@check');
        Route::apiResource('/jobs/recurring', 'RecurringJobController')
            ->except('update');

        Route::get('/jobs/{job}/next-statuses', 'JobStatusesController@listNextStatuses');
        Route::patch('/jobs/{job}/status', 'JobStatusesController@changeStatus');

        Route::post('/jobs/{job}/pin', 'JobPinsController@pinJob');
        Route::delete('/jobs/{job}/pin', 'JobPinsController@unpinJob');

        Route::post('/jobs/{job}/follow', 'JobFollowersController@followJob');
        Route::delete('/jobs/{job}/follow', 'JobFollowersController@unfollowJob');

        Route::get('/jobs/{job}/users', 'JobUsersController@listAssignedUsers');
        Route::post('/jobs/{job}/users/{user}', 'JobUsersController@assignToUser');
        Route::delete('/jobs/{job}/users/{user}', 'JobUsersController@unassignFromUser');

        Route::get('/jobs/{job}/teams', 'JobTeamsController@listAssignedTeams');
        Route::post('/jobs/{job}/teams/{team}', 'JobTeamsController@assignToTeam');
        Route::delete('/jobs/{job}/teams/{team}', 'JobTeamsController@unassignFromTeam');

        Route::get('/jobs/{job}/tags', 'JobTagsController@listJobTags');
        Route::post('/jobs/{job}/tags/{tag}', 'JobTagsController@tagJob');
        Route::delete('/jobs/{job}/tags/{tag}', 'JobTagsController@untagJob');

        Route::get('/jobs/{job}/documents', 'JobDocumentsController@listJobDocuments');

        Route::get('/jobs/contacts/assignments/types', 'JobContactsController@listAssignmentTypes');
        Route::get('/jobs/{job}/contacts/assignments', 'JobContactsController@listAssignedContacts');
        Route::post('/jobs/{job}/contacts/assignments/{contact}', 'JobContactsController@assignContact');
        Route::patch('/jobs/{job}/contacts/assignments/{contact}', 'JobContactsController@updateAssignment');
        Route::delete('/jobs/{job}/contacts/assignments/{contact}', 'JobContactsController@unassignContact');

        Route::get('/jobs/{job}/notes', 'JobNotesController@listNotes');
        Route::post('/jobs/{job}/notes/{note}', 'JobNotesController@addNote');
        Route::delete('/jobs/{job}/notes/{note}', 'JobNotesController@deleteNote');

        Route::get('/jobs/{job}/messages', 'JobMessagesController@listMessages');
        Route::post('/jobs/{job}/messages/from-template', 'JobMessagesController@composeMessage');
        Route::patch('/jobs/{job}/messages/read', 'JobMessagesController@markMessagesAsRead');
        Route::patch('/jobs/{job}/messages/unread', 'JobMessagesController@markLatestMessageAsUnread');
        Route::post('/jobs/{job}/messages/{message}', 'JobMessagesController@attachMessage');
        Route::post('/jobs/{job}/messages/{message}/send', 'JobMessagesController@sendMessage');
        Route::delete('/jobs/{job}/messages/{message}', 'JobMessagesController@detachMessage');

        Route::get('/jobs/{job}/statuses', 'JobsController@listJobStatuses');
        Route::get('/jobs/{job}/notes-and-messages', 'JobsController@listNotesAndMessages');

        Route::post('/jobs/{job}/snooze', 'JobsController@snoozeJob');
        Route::delete('/jobs/{job}/unsnooze', 'JobsController@unsnoozeJob');

        Route::get('/jobs/{job}/linked-jobs', 'LinkedJobsController@listLinkedJobs');
        Route::post('/jobs/{id}/jobs/{linked_job_id}', 'LinkedJobsController@linkJobs')
            ->where('linked_job_id', '\d+');
        Route::delete('/jobs/{id}/jobs/{linked_job_id}', 'LinkedJobsController@unlinkJobs')
            ->where('linked_job_id', '\d+');

        Route::get('/jobs/{job}/photos', 'JobPhotosController@listJobPhotos');
        Route::post('/jobs/{job}/photos/{photo}', 'JobPhotosController@attachPhoto');
        Route::delete('/jobs/{job}/photos/{photo}', 'JobPhotosController@detachPhoto');
        Route::delete('/jobs/{job}/photos/bulk', 'JobPhotosController@detachPhotos');
        Route::patch('/jobs/{job}/photos/{photo}', 'JobPhotosController@updatePhoto');

        Route::post('/jobs/{source_job_id}/jobs/{destination_job_id}/merge', 'JobsController@mergeJob')
            ->where('source_job_id', '\d+')
            ->where('destination_job_id', '\d+');

        Route::get('/jobs/{job}/site-survey', 'JobSiteSurveyController@getSiteSurvey');
        Route::post('/jobs/{job}/site-survey/questions/{question}', 'JobSiteSurveyController@attachQuestion');
        Route::delete('/jobs/{job}/site-survey/questions/{question}', 'JobSiteSurveyController@detachQuestion');
        Route::apiResource('/jobs/{job}/areas', 'JobAreasController');

        // Job Tasks
        Route::apiResource('jobs/tasks/types', 'JobTaskTypesController')->except('edit');
        Route::get('/jobs/{job}/tasks', 'JobTasksController@listJobTasks');
        Route::get('/jobs/{job}/tasks/{task}', 'JobTasksController@viewJobTask');
        Route::post('/jobs/{job}/tasks', 'JobTasksController@addJobTask');
        Route::patch('/jobs/{job}/tasks/{task}', 'JobTasksController@updateJobTask');
        Route::delete('/jobs/{job}/tasks/{task}', 'JobTasksController@deleteJobTask');
        Route::patch('/jobs/{job}/tasks/{task}/status', 'JobTasksController@changeStatus');
        Route::patch('/jobs/{job}/tasks/{task}/status/scheduled', 'JobTasksController@changeScheduledStatus');
        Route::post('/jobs/{job}/tasks/{task}/crew/{user}', 'JobTasksController@assignUser');
        Route::delete('/jobs/{job}/tasks/{task}/crew/{user}', 'JobTasksController@unassignUser');
        Route::post('/jobs/{job}/tasks/{task}/vehicles/{vehicle}', 'JobTasksController@assignVehicle');
        Route::delete('/jobs/{job}/tasks/{task}/vehicles/{vehicle}', 'JobTasksController@unassignVehicle');
        Route::post('/jobs/{job}/tasks/{task}/teams/{team}', 'JobTasksController@assignTeam');
        Route::delete('/jobs/{job}/tasks/{task}/teams/{team}', 'JobTasksController@unassignTeam');
        Route::post('/jobs/{job}/tasks/{task}/snooze', 'JobTasksController@snoozeTask');
        Route::delete('/jobs/{job}/tasks/{task}/unsnooze', 'JobTasksController@unsnoozeTask');

        //Job equipment
        Route::get('/jobs/{job}/equipment/amount', 'JobEquipmentController@getTotalAmount');
        Route::patch('/jobs/{job}/equipment/{equipment}/finish-using', 'JobEquipmentController@finishUsing');
        Route::patch('/jobs/{job}/equipment/{equipment}/override', 'JobEquipmentController@overrideIntervalsCount');
        Route::apiResource('/jobs/{job}/equipment', 'JobEquipmentController')->except('update');

        //Job Materials
        Route::get('/jobs/{job}/materials/amount', 'JobMaterialsController@getTotalAmount');
        Route::apiResource('/jobs/{job}/materials', 'JobMaterialsController');

        //Job Assessment Reports
        Route::get(
            '/jobs/{job}/assessment-reports/{assessment_report}/next-statuses',
            'JobAssessmentReportsController@getNextStatuses'
        );
        Route::patch(
            '/jobs/{job}/assessment-reports/{assessment_report}/statuses',
            'JobAssessmentReportsController@changeStatus'
        );
        Route::get(
            '/jobs/{job}/assessment-reports/{assessment_report}/document',
            'JobAssessmentReportsController@document'
        );
        Route::get(
            '/jobs/{job}/assessment-reports/{assessment_report}/status-and-total',
            'JobAssessmentReportsController@getStatusAndTotal'
        );
        Route::apiResource('/jobs/{job}/assessment-reports', 'JobAssessmentReportsController');

        //Job Laha compensations
        Route::post('/jobs/{job}/laha/{laha}/approve', 'JobLahaCompensationsController@approve');
        Route::apiResource('/jobs/{job}/laha', 'JobLahaCompensationsController');
        //Job allowances
        Route::post(
            '/jobs/{job}/allowances/{allowance}/approve',
            'JobAllowancesController@approve'
        );
        Route::apiResource('/jobs/{job}/allowances', 'JobAllowancesController');
        //Job reimbursements
        Route::post(
            '/jobs/{job}/reimbursements/{reimbursements}/approve',
            'JobReimbursementsController@approve'
        );
        Route::apiResource('/jobs/{job}/reimbursements', 'JobReimbursementsController');
        //Job labours
        Route::get('/jobs/{job}/labours/amount', 'JobLaboursController@getTotalAmount');
        Route::apiResource('/jobs/{job}/labours', 'JobLaboursController');
        //Job costing summary
        Route::get('/jobs/{job_id}/summary', 'JobsController@getCostingSummary');
        //Job usage and actuals counters
        Route::get('/jobs/{job_id}/costing-counters', 'JobsController@getCostingCounters');
    });

Route::middleware('auth:api')->namespace('Management')
    ->group(function () {
        Route::post('/management/search/index', 'SearchEngineController@index');
        Route::post('/management/search/flush', 'SearchEngineController@flush');

        Route::apiResource('/management/site-survey/questions', 'SiteSurveyQuestionsController');
        Route::apiResource(
            '/management/site-survey/questions/{question}/options',
            'SiteSurveyQuestionOptionsController'
        );
    });

Route::namespace('Search')->middleware('auth:api')
    ->group(function () {
        Route::get('/search/users-and-teams', 'UsersAndTeamsController@search');
    });

Route::middleware('auth:api')
    ->prefix('/finance')
    ->group(function () {
        //Finance operations
        Route::namespace('Finance')
            ->group(function () {
                Route::apiResource('accounting-organizations', 'AccountingOrganizationsController');
                Route::get(
                    '/accounting-organizations/{accounting_organization}/locations',
                    'AccountingOrganizationsController@getLocations'
                );
                Route::post(
                    '/accounting-organizations/{accounting_organization}/locations/{location}',
                    'AccountingOrganizationsController@addLocation'
                );

                Route::apiResource('gs-codes', 'GSCodesController');

                Route::get('/gl-accounts/search', 'GLAccountsController@search');
                Route::apiResource(
                    '/accounting-organizations/{accounting_organization}/gl-accounts',
                    'GLAccountsController'
                );
                Route::apiResource('account-types', 'AccountTypesController');
                Route::apiResource('tax-rates', 'TaxRatesController');

                Route::post('/payments/transfers/receive', 'ReceivePaymentController@receive');

                Route::post('payments/forward', 'ForwardedPaymentsController@forward');

                Route::apiResource('payments', 'PaymentsController')->except('update');

                Route::post('/credit-notes/{credit_note}/approve', 'CreditNotesController@approve');
                Route::post('/credit-notes/{credit_note}/payment', 'CreditNotesController@createPayment');

                Route::get('/credit-notes/listings/info', 'CreditNoteListingController@getInfo');
                Route::get('/credit-notes/listings/draft', 'CreditNoteListingController@getDraft');
                Route::get('/credit-notes/listings/pending-approval', 'CreditNoteListingController@getPendingApproval');
                Route::get('/credit-notes/listings/approved', 'CreditNoteListingController@getApproved');
                Route::get('/credit-notes/listings/all', 'CreditNoteListingController@getAll');
                Route::get('/credit-notes/listings/search', 'CreditNoteListingController@search');

                Route::get('/credit-notes/{credit_note}/document', 'CreditNotesController@getDocument');
                Route::get('/credit-notes/{credit_note}/approve-requests', 'CreditNotesController@getApproveRequests');
                Route::post('/credit-notes/{credit_note}/approve-requests', 'CreditNotesController@addApproveRequests');
                Route::get('/credit-notes/{credit_note}/approver-list', 'CreditNotesController@approverList');

                Route::get('/credit-notes/{credit_note}/notes', 'CreditNoteNotesController@getNotes');
                Route::post('/credit-notes/{credit_note}/notes/{note}', 'CreditNoteNotesController@attachNote');
                Route::delete('/credit-notes/{credit_note}/notes/{note}', 'CreditNoteNotesController@detachNote');

                Route::get('/credit-notes/{credit_note}/tags', 'CreditNoteTagsController@getTags');
                Route::post('/credit-notes/{credit_note}/tags/{tag}', 'CreditNoteTagsController@attachTag');
                Route::delete('/credit-notes/{credit_note}/tags/{tag}', 'CreditNoteTagsController@detachTag');

                Route::apiResource('gs-codes', 'GSCodesController');

                Route::apiResource('account-types', 'AccountTypesController');
                Route::apiResource('tax-rates', 'TaxRatesController');
                Route::apiResource('payments', 'PaymentsController')->except('update');
                Route::apiResource('credit-notes', 'CreditNotesController')->except('index');

                Route::post('credit-notes/{credit_note}/items', 'CreditNoteItemsController@store');
                Route::patch('credit-notes/{credit_note}/items/{credit_note_item}', 'CreditNoteItemsController@update');
                Route::delete(
                    'credit-notes/{credit_note}/items/{credit_note_item}',
                    'CreditNoteItemsController@destroy'
                );

                Route::apiResource('/purchase-orders', 'PurchaseOrdersController')->except('index');

                Route::get(
                    '/purchase-orders/{purchase_order}/approver-list',
                    'PurchaseOrdersController@getSuggestedApprovers'
                );
                Route::post(
                    '/purchase-orders/{purchase_order}/approve',
                    'PurchaseOrdersController@approve'
                );
                Route::get('/purchase-orders/{purchase_order}/document', 'PurchaseOrdersController@document');
                Route::apiResource('/purchase-orders/{purchase_order}/items', 'PurchaseOrderItemsController');
                Route::get(
                    '/purchase-orders/{purchase_order}/approve-requests',
                    'PurchaseOrderApproveRequestsController@getPurchaseOrderApproveRequests'
                );
                Route::get('/purchase-orders/listings/all', 'PurchaseOrderListingController@index');
                Route::get('/purchase-orders/listings/info', 'PurchaseOrderListingController@getInfo');
                Route::get('/purchase-orders/listings/draft', 'PurchaseOrderListingController@getDraft');
                Route::get(
                    '/purchase-orders/listings/pending-approval',
                    'PurchaseOrderListingController@getPendingApproval'
                );
                Route::get('/purchase-orders/listings/approved', 'PurchaseOrderListingController@getApproved');
                Route::get('/purchase-orders/listings/search', 'PurchaseOrderListingController@search');
                Route::post(
                    '/purchase-orders/{purchase_order}/approve-requests',
                    'PurchaseOrderApproveRequestsController@createPurchaseOrderApproveRequests'
                );
                Route::get('/purchase-orders/{purchase_order}/tags', 'PurchaseOrderTagsController@getTags');
                Route::post(
                    '/purchase-orders/{purchase_order}/tags/{tag}',
                    'PurchaseOrderTagsController@attachTag'
                );
                Route::delete(
                    '/purchase-orders/{purchase_order}/tags/{tag}',
                    'PurchaseOrderTagsController@detachTag'
                );
                Route::get('/purchase-orders/{purchase_order}/notes', 'PurchaseOrderNotesController@getNotes');
                Route::post(
                    '/purchase-orders/{purchase_order}/notes/{note}',
                    'PurchaseOrderNotesController@attachNote'
                );
                Route::delete(
                    '/purchase-orders/{purchase_order}/notes/{note}',
                    'PurchaseOrderNotesController@detachNote'
                );

                Route::post(
                    'invoices/{invoice}/payments/receive/credit-card',
                    'InvoicesController@receiveCreditCardPayment'
                );

                Route::post(
                    'invoices/{invoice}/payments/receive/direct-deposit',
                    'InvoicesController@receiveDirectDepositPayment'
                );

                Route::apiResource('invoices', 'InvoicesController')->except('index');

                Route::post('invoices/{invoice}/approve', 'InvoicesController@approve');
                Route::get('invoices/{invoice}/document', 'InvoicesController@document');

                //Invoice Approve request
                Route::post(
                    'invoices/{invoice}/approve-requests',
                    'InvoiceApproveRequestsController@createApproveRequest'
                );
                Route::get(
                    'invoices/{invoice}/approve-requests',
                    'InvoiceApproveRequestsController@getApproveRequests'
                );
                Route::get('invoices/{invoice}/approver-list', 'InvoiceApproveRequestsController@approverList');

                //Invoice Items
                Route::post('invoices/{invoice}/items', 'InvoiceItemsController@store');
                Route::patch('invoices/{invoice}/items/{invoice_item}', 'InvoiceItemsController@update');
                Route::delete('invoices/{invoice}/items/{invoice_item}', 'InvoiceItemsController@destroy');

                //Invoice Listings
                Route::get('invoices/listings/all', 'InvoiceListingController@index');
                Route::get('invoices/listings/search', 'InvoiceListingController@search');
                Route::get('invoices/listings/search-by-id-or-job', 'InvoiceListingController@searchByIdOrJobId');
                Route::get('invoices/listings/info', 'InvoiceListingController@info');
                Route::get('invoices/listings/draft', 'InvoiceListingController@draft');
                Route::get('invoices/listings/unpaid', 'InvoiceListingController@unpaid');
                Route::get('invoices/listings/overdue', 'InvoiceListingController@overdue');
                Route::get('invoices/listings/unforwarded', 'InvoiceListingController@listUnforwarded');

                //Invoice-Notes
                Route::get('invoices/{invoice}/notes', 'InvoiceNotesController@getNotes');
                Route::post('invoices/{invoice}/notes/{note}', 'InvoiceNotesController@attachNote');
                Route::delete('invoices/{invoice}/notes/{note}', 'InvoiceNotesController@detachNote');

                //Invoice-Tags
                Route::get('invoices/{invoice}/tags', 'InvoiceTagsController@getTags');
                Route::post('invoices/{invoice}/tags/{tag}', 'InvoiceTagsController@attachTag');
                Route::delete('invoices/{invoice}/tags/{tag}', 'InvoiceTagsController@detachTag');
            });

        //Reports
        Route::prefix('/reports')
            ->namespace('Reporting')
            ->group(function () {
                //Payments reporting
                Route::get('/invoices/payments', 'ReportingPaymentsController@invoicePaymentsReport');
                Route::get('/financial/volume', 'ReportingFinancialController@volumeReport');
                Route::get('/financial/revenue', 'ReportingFinancialController@revenueReport');
                Route::get('/financial/accounts_receivables', 'ReportingFinancialController@accountsReceivablesReport');
                //GL accounts reporting
                Route::get('/gl-accounts/transactions', 'ReportingGLAccountsController@listTransaction');
                Route::get('/gl-accounts/transactions/info', 'ReportingGLAccountsController@listTransactionReport');

                //Finance: Report - Income by Account Detailed
                Route::get(
                    '/gl-accounts/income/summary',
                    'ReportingGLAccountsController@listIncomeReport'
                );
                //Finance: Report - Trial
                Route::get('/gl-accounts/trial-report', 'ReportingGLAccountsController@trialReport');

                Route::get('/gst', 'GSTReportController@index');
            });
    });

Route::middleware('auth:api')->namespace('Operations')
    ->prefix('operations')
    ->group(function () {
        // Tasks
        Route::get('tasks', 'TasksController@listLocationTasks');
        Route::get('tasks/search', 'TasksController@search');
        Route::get('tasks/mine', 'TasksController@getMineTasks');

        // Staff
        Route::get('staff/search', 'StaffController@search');
        Route::apiResource('staff', 'StaffController')->except(['store', 'update', 'destroy']);

        // Vehicles
        Route::apiResource('vehicles/statuses/types', 'VehicleStatusTypesController')->except('edit');
        Route::apiResource('vehicles', 'VehiclesController')->except('index');
        Route::get('vehicles', 'VehiclesController@listLocationVehicles');
        Route::patch('vehicles/{vehicle}/status', 'VehiclesController@changeStatus');

        // Runs
        Route::apiResource('runs', 'RunsController')->except('index');
        Route::get('runs', 'RunsController@listLocationRuns');
        Route::get('runs/{run}/crew', 'RunsController@listRunCrew');
        Route::post('runs/{run}/crew/{user}', 'RunsController@assignUser');
        Route::delete('runs/{run}/crew/{user}', 'RunsController@unassignUser');
        Route::post('runs/{run}/vehicles/{vehicle}', 'RunsController@assignVehicle');
        Route::delete('runs/{run}/vehicles/{vehicle}', 'RunsController@unassignVehicle');
        Route::post('runs/{run}/tasks/{task}', 'RunsController@scheduleTask');
        Route::delete('runs/{run}/tasks/{task}', 'RunsController@removeTask');
        Route::post('runs/from-template/{template}', 'RunsController@createFromTemplate');

        // Templates and template runs
        Route::apiResource('runs/templates', 'RunTemplatesController')->except('index');
        Route::get('runs/templates', 'RunTemplatesController@listLocationTemplates');

        Route::get('runs/templates/{template}/runs', 'RunTemplateRunsController@listTemplateRuns');
        Route::get('runs/templates/{template}/runs/{run}', 'RunTemplateRunsController@viewTemplateRun');
        Route::post('runs/templates/{template}/runs', 'RunTemplateRunsController@addTemplateRun');
        Route::patch('runs/templates/{template}/runs/{run}', 'RunTemplateRunsController@updateTemplateRun');
        Route::delete('runs/templates/{template}/runs/{run}', 'RunTemplateRunsController@deleteTemplateRun');
        Route::post('runs/templates/{template}/{run}/crew/{user}', 'RunTemplateRunsController@assignUser');
        Route::delete('runs/templates/{template}/{run}/crew/{user}', 'RunTemplateRunsController@unassignUser');
        Route::post('runs/templates/{template}/{run}/vehicles/{vehicle}', 'RunTemplateRunsController@assignVehicle');
        Route::delete(
            'runs/templates/{template}/{run}/vehicles/{vehicle}',
            'RunTemplateRunsController@unassignVehicle'
        );
    });

Route::middleware('auth:api')->namespace('UsageAndActuals')
    ->prefix('/usage-and-actuals')
    ->group(function () {
        Route::apiResource('insurer-contracts', 'InsurerContractsController')->except('index');
        Route::get('/insurer-contracts/contracts/{id}', 'InsurerContractsController@getContracts');
        Route::get('/insurer-contracts/contracts/{id}/active', 'InsurerContractsController@getActiveContract');

        Route::apiResource('/equipment-categories', 'EquipmentCategoriesController');

        Route::get('/equipment/{equipment}/notes', 'EquipmentController@getNotes');
        Route::post('/equipment/{equipment}/notes/{note}', 'EquipmentController@attachNote');
        Route::delete('/equipment/{equipment}/notes/{note}', 'EquipmentController@detachNote');
        Route::get('/equipment/search', 'EquipmentController@search');
        Route::apiResource('/equipment', 'EquipmentController');

        Route::apiResource('/measure-units', 'MeasureUnitsController');
        Route::get('/materials/search', 'MaterialsController@search');
        Route::apiResource('/materials', 'MaterialsController');
        Route::apiResource('/labour-types', 'LabourTypesController');
        Route::apiResource('/allowance-types', 'AllowanceTypesController');
        Route::apiResource('/laha-compensations', 'LahaCompensationsController');
    });

Route::middleware('auth:api')->namespace('AssessmentReports')
    ->prefix('assessment-reports')
    ->group(function () {
        Route::apiResources([
            '/flooring-types'                     => 'FlooringTypesController',
            '/flooring-subtypes'                  => 'FlooringSubtypesController',
            '/underlay-types'                     => 'UnderlayTypesController',
            '/non-restorable-reasons'             => 'NonRestorableReasonsController',
            '/carpet-types'                       => 'CarpetTypesController',
            '/carpet-construction-types'          => 'CarpetConstructionTypesController',
            '/carpet-ages'                        => 'CarpetAgesController',
            '/carpet-face-fibres'                 => 'CarpetFaceFibresController',
            '/{assessment_report}/costing-stages' => 'AssessmentReportCostingStagesController',
            '/{assessment_report}/cost-items'     => 'AssessmentReportCostItemsController',
            '/{assessment_report}/sections'       => 'AssessmentReportSectionsController',
        ]);
        Route::apiResources([
            '/{assessment_report}/sections/{section}/text-blocks' => 'AssessmentReportSectionTextBlocksController',
            '/{assessment_report}/sections/{section}/images'      => 'AssessmentReportSectionImagesController',
            '/{assessment_report}/sections/{section}/photos'      => 'AssessmentReportSectionPhotosController',
            '/{assessment_report}/sections/{section}/rooms'       => 'AssessmentReportSectionRoomsController',
        ], [
            'except' => ['index', 'show'],
        ]);
        Route::post(
            '/{assessment_report}/sections/{section}/cost-items',
            'AssessmentReportSectionCostItemsController@store'
        );
        Route::patch(
            '/{assessment_report}/sections/{section}/cost-items',
            'AssessmentReportSectionCostItemsController@update'
        );
        Route::delete(
            '/{assessment_report}/sections/{section}/cost-items',
            'AssessmentReportSectionCostItemsController@destroy'
        );
    });
