<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPurchaseOrdersTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createPurchaseOrdersTable();
        $this->createPurchaseOrderItemsTable();
        $this->createPurchaseOrderStatusesTable();
        $this->createPurchaseOrderApproveRequestsTable();
        $this->createPurchaseOrderTagTable();
        $this->createNotePurchaseOrderTable();
        $this->createPurchaseOrderUserNotificationTable();
    }

    private function createPurchaseOrdersTable()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('location_id');
            $table->bigInteger('accounting_organization_id');
            $table->bigInteger('recipient_contact_id');
            $table->bigInteger('job_id')->nullable();
            $table->bigInteger('document_id')->nullable();
            $table->date('date');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('locked_at')->nullable();

            $table->index('location_id');
            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('accounting_organization_id');
            $table->foreign('accounting_organization_id')
                ->references('id')
                ->on('accounting_organizations')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('recipient_contact_id');
            $table->foreign('recipient_contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('job_id');
            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    private function createPurchaseOrderItemsTable()
    {
        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('purchase_order_id');
            $table->bigInteger('gs_code_id');
            $table->text('description');
            $table->decimal('unit_cost', 10, 2);
            $table->integer('quantity');
            $table->decimal('markup', 10, 2);
            $table->bigInteger('gl_account_id');
            $table->bigInteger('tax_rate_id');
            $table->timestamp('created_at')->useCurrent();

            $table->index('purchase_order_id');
            $table->foreign('purchase_order_id')
                ->references('id')
                ->on('purchase_orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('gs_code_id');
            $table->foreign('gs_code_id')
                ->references('id')
                ->on('gs_codes')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('gl_account_id');
            $table->foreign('gl_account_id')
                ->references('id')
                ->on('gl_accounts')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('tax_rate_id');
            $table->foreign('tax_rate_id')
                ->references('id')
                ->on('tax_rates')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createPurchaseOrderStatusesTable()
    {
        Schema::create('purchase_order_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('purchase_order_id');
            $table->bigInteger('user_id');
            $table->text('status');
            $table->timestamp('created_at')->useCurrent();

            $table->index('purchase_order_id');
            $table->foreign('purchase_order_id')
                ->references('id')
                ->on('purchase_orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createPurchaseOrderApproveRequestsTable()
    {
        Schema::create('purchase_order_approve_requests', function (Blueprint $table) {
            $table->bigInteger('purchase_order_id');
            $table->bigInteger('requester_id');
            $table->bigInteger('approver_id');
            $table->primary([
                'purchase_order_id',
                'requester_id',
                'approver_id',
            ]);
            $table->timestamp('approved_at')->nullable();

            $table->index('purchase_order_id');
            $table->foreign('purchase_order_id')
                ->references('id')
                ->on('purchase_orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('requester_id');
            $table->foreign('requester_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('approver_id');
            $table->foreign('approver_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createPurchaseOrderTagTable()
    {
        Schema::create('purchase_order_tag', function (Blueprint $table) {
            $table->bigInteger('purchase_order_id');
            $table->bigInteger('tag_id');
            $table->primary([
                'purchase_order_id',
                'tag_id',
            ]);

            $table->index('purchase_order_id');
            $table->foreign('purchase_order_id')
                ->references('id')
                ->on('purchase_orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('tag_id');
            $table->foreign('tag_id')
                ->references('id')
                ->on('tags')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    private function createNotePurchaseOrderTable()
    {
        Schema::create('note_purchase_order', function (Blueprint $table) {
            $table->bigInteger('purchase_order_id');
            $table->bigInteger('note_id');
            $table->primary([
                'purchase_order_id',
                'note_id',
            ]);

            $table->index('purchase_order_id');
            $table->foreign('purchase_order_id')
                ->references('id')
                ->on('purchase_orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('note_id');
            $table->foreign('note_id')
                ->references('id')
                ->on('notes')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    private function createPurchaseOrderUserNotificationTable()
    {
        Schema::create('purchase_order_user_notification', function (Blueprint $table) {
            $table->bigInteger('purchase_order_id');
            $table->bigInteger('user_notification_id');
            $table->primary([
                'purchase_order_id',
                'user_notification_id',
            ]);

            $table->index('purchase_order_id');
            $table->foreign('purchase_order_id')
                ->references('id')
                ->on('purchase_orders')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('user_notification_id');
            $table->foreign('user_notification_id')
                ->references('id')
                ->on('user_notifications')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_order_user_notification');
        Schema::dropIfExists('note_purchase_order');
        Schema::dropIfExists('purchase_order_tag');
        Schema::dropIfExists('purchase_order_approve_requests');
        Schema::dropIfExists('purchase_order_statuses');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
    }
}
