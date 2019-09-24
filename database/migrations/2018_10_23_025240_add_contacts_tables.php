<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContactsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contact_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
        });

        Schema::create('contact_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
        });

        Schema::create('company_groups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
        });

        Schema::create('contacts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('contact_type');
            $table->bigInteger('contact_category_id');
            $table->bigInteger('contact_status_id');
            $table->text('email')->nullable();
            $table->text('business_phone')->nullable();
            $table->timestamp('last_active_at')->useCurrent();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->softDeletes();

            $table->foreign('contact_category_id')
                ->references('id')
                ->on('contact_categories')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('contact_status_id')
                ->references('id')
                ->on('contact_statuses')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('contact_category_id');
            $table->index('contact_status_id');
        });

        Schema::create('contact_company', function (Blueprint $table) {
            $table->bigInteger('contact_id');
            $table->bigInteger('company_id');

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('company_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->primary(['contact_id', 'company_id']);
            $table->index(['company_id', 'contact_id']);
        });

        Schema::create('contact_company_profiles', function (Blueprint $table) {
            $table->bigInteger('contact_id');
            $table->text('legal_name');
            $table->text('trading_name')->nullable();
            $table->text('abn');
            $table->text('website')->nullable();
            $table->integer('default_payment_terms_days');

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->primary('contact_id');
        });

        Schema::create('contact_person_profiles', function (Blueprint $table) {
            $table->bigInteger('contact_id');
            $table->text('first_name');
            $table->text('last_name');
            $table->text('job_title')->nullable();
            $table->text('direct_phone')->nullable();
            $table->text('mobile_phone')->nullable();

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->primary('contact_id');
        });

        Schema::create('company_group_contact', function (Blueprint $table) {
            $table->bigInteger('contact_group_id');
            $table->bigInteger('contact_id');

            $table->foreign('contact_group_id')
                ->references('id')
                ->on('company_groups')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->primary(['contact_group_id', 'contact_id']);
        });

        Schema::create('managed_accounts', function (Blueprint $table) {
            $table->bigInteger('user_id');
            $table->bigInteger('contact_id');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->primary(['user_id', 'contact_id']);
        });

        Schema::create('contact_note', function (Blueprint $table) {
            $table->bigInteger('contact_id');
            $table->bigInteger('note_id');

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('note_id')
                ->references('id')
                ->on('notes')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->primary(['contact_id', 'note_id']);
        });

        Schema::create('contact_tag', function (Blueprint $table) {
            $table->bigInteger('tag_id');
            $table->bigInteger('contact_id');

            $table->foreign('tag_id')
                ->references('id')
                ->on('tags')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->primary(['tag_id', 'contact_id']);
        });

        Schema::create('address_contact', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('type');
            $table->bigInteger('address_id');
            $table->bigInteger('contact_id');

            $table->foreign('address_id')
                ->references('id')
                ->on('addresses')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts')
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
        Schema::dropIfExists('contact_company');
        Schema::dropIfExists('contact_company_profiles');
        Schema::dropIfExists('contact_person_profiles');
        Schema::dropIfExists('company_group_contact');
        Schema::dropIfExists('managed_accounts');
        Schema::dropIfExists('contact_note');
        Schema::dropIfExists('contact_tag');
        Schema::dropIfExists('address_contact');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('contact_categories');
        Schema::dropIfExists('contact_statuses');
        Schema::dropIfExists('company_groups');
    }
}
