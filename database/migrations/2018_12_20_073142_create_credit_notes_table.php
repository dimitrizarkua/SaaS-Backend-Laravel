<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('location_id');
            $table->bigInteger('accounting_organization_id');
            $table->bigInteger('recipient_contact_id');
            $table->bigInteger('job_id')->nullable();
            $table->bigInteger('document_id')->nullable();
            $table->bigInteger('payment_id')->nullable();
            $table->date('date');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
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
                ->onDelete('set null');

            $table->index('document_id');
            $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->index('payment_id');
            $table->foreign('payment_id')
                ->references('id')
                ->on('payments')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('credit_notes');
    }
}
