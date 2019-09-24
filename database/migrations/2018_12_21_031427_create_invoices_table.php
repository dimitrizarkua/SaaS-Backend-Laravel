<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('location_id');
            $table->bigInteger('accounting_organization_id');
            $table->bigInteger('recipient_contact_id');
            $table->text('recipient_address');
            $table->text('recipient_name');
            $table->integer('payment_terms_days');
            $table->bigInteger('job_id')->nullable();
            $table->bigInteger('document_id')->nullable();
            $table->text('type');
            $table->timestamp('due_at');
            $table->timestamp('created_at')->nullable();

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
                ->onDelete('restrict');
        });

        Schema::create('invoice_approve_requests', function (Blueprint $table) {
            $table->bigInteger('invoice_id');
            $table->bigInteger('requester_id');
            $table->bigInteger('approver_id');
            $table->timestamp('approved_at')->nullable();

            $table->primary(['invoice_id', 'requester_id', 'approver_id']);

            $table->index('invoice_id');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
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

        Schema::create('invoice_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('invoice_id');
            $table->bigInteger('user_id')->nullable();
            $table->text('status');
            $table->timestamp('created_at')->nullable();

            $table->index('invoice_id');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('invoice_id');
            $table->bigInteger('gs_code_id');
            $table->text('description');
            $table->decimal('unit_cost', 10, 2);
            $table->integer('quantity');
            $table->decimal('discount', 10, 2);
            $table->bigInteger('gl_account_id');
            $table->bigInteger('tax_rate_id');

            $table->index('invoice_id');
            $table->foreign('invoice_id')
                ->references('id')
                ->on('invoices')
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

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoice_statuses');
        Schema::dropIfExists('invoice_approve_requests');
        Schema::dropIfExists('invoices');
    }
}
