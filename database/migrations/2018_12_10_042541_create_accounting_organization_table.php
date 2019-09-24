<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccountingOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_types', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->boolean('increase_action_is_debit')->default(false);
            $table->boolean('show_on_pl')->default(false);
            $table->boolean('show_on_bs')->default(false);
        });

        Schema::create('tax_rates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name');
            $table->decimal('rate', 10, 2);
        });

        Schema::create('gl_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('accounting_organization_id');
            $table->bigInteger('account_type_id');
            $table->bigInteger('tax_rate_id')->nullable();
            $table->text('code');
            $table->text('name');
            $table->text('description')->nullable();
            $table->text('bank_account_name')->nullable();
            $table->text('bank_account_number')->nullable();
            $table->text('bank_bsb')->nullable();
            $table->boolean('enable_payments_to_account')->default(false);
            $table->text('status');
            $table->boolean('is_active')->default(true);

            $table->index('account_type_id');
            $table->foreign('account_type_id')
                ->references('id')
                ->on('account_types')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('tax_rate_id');
            $table->foreign('tax_rate_id')
                ->references('id')
                ->on('tax_rates')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('accounting_organizations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('contact_id');
            $table->bigInteger('tax_payable_account_id')->nullable();
            $table->bigInteger('tax_receivable_account_id')->nullable();
            $table->bigInteger('accounts_payable_account_id')->nullable();
            $table->bigInteger('accounts_receivable_account_id')->nullable();
            $table->bigInteger('payment_details_account_id')->nullable();
            $table->text('cc_payments_api_key')->nullable();
            $table->boolean('is_active')->default(true);

            $table->index('contact_id');
            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('tax_payable_account_id');
            $table->foreign('tax_payable_account_id')
                ->references('id')
                ->on('gl_accounts')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index('tax_receivable_account_id');
            $table->foreign('tax_receivable_account_id')
                ->references('id')
                ->on('gl_accounts')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index('accounts_payable_account_id');
            $table->foreign('accounts_payable_account_id')
                ->references('id')
                ->on('gl_accounts')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index('accounts_receivable_account_id');
            $table->foreign('accounts_receivable_account_id')
                ->references('id')
                ->on('gl_accounts')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index('payment_details_account_id');
            $table->foreign('payment_details_account_id')
                ->references('id')
                ->on('gl_accounts')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });

        Schema::table('gl_accounts', function (Blueprint $table) {
            $table->index('accounting_organization_id');
            $table->foreign('accounting_organization_id')
                ->references('id')
                ->on('accounting_organizations')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });

        Schema::create('accounting_organization_locations', function (Blueprint $table) {
            $table->bigInteger('accounting_organization_id');
            $table->bigInteger('location_id');

            $table->primary(['accounting_organization_id', 'location_id']);

            $table->index('accounting_organization_id');
            $table->foreign('accounting_organization_id')
                ->references('id')
                ->on('accounting_organizations')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->index('location_id');
            $table->foreign('location_id')
                ->references('id')
                ->on('locations')
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
        Schema::dropIfExists('accounting_organization_locations');
        Schema::table('gl_accounts', function (Blueprint $table) {
            $table->dropForeign('gl_accounts_account_type_id_foreign');
            $table->dropForeign('gl_accounts_accounting_organization_id_foreign');
            $table->dropForeign('gl_accounts_tax_rate_id_foreign');
        });
        Schema::table('accounting_organizations', function (Blueprint $table) {
            $table->dropForeign('accounting_organizations_accounts_payable_account_id_foreign');
            $table->dropForeign('accounting_organizations_accounts_receivable_account_id_foreign');
            $table->dropForeign('accounting_organizations_contact_id_foreign');
            $table->dropForeign('accounting_organizations_tax_payable_account_id_foreign');
            $table->dropForeign('accounting_organizations_tax_receivable_account_id_foreign');
            $table->dropForeign('accounting_organizations_payment_details_account_id_foreign');
        });
        Schema::dropIfExists('gl_accounts');
        Schema::dropIfExists('account_types');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('accounting_organizations');
    }
}
