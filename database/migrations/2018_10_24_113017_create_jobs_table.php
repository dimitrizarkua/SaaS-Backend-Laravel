<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->createJobServicesTable();
        $this->createJobTable();
        $this->createJobStatuesTable();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_statuses');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_services');
    }

    private function createJobServicesTable()
    {
        Schema::create('job_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('name')->unique();
        });
    }

    private function createJobTable()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('claim_number')->unique();
            $table->bigInteger('job_service_id')->nullable();
            $table->bigInteger('insurer_id')->nullable();
            $table->bigInteger('site_address_id')->nullable();
            $table->decimal('site_address_lat', 9, 6)->nullable();
            $table->decimal('site_address_lng', 9, 6)->nullable();
            $table->bigInteger('assigned_location_id')->nullable();
            $table->bigInteger('owner_location_id')->nullable();
            $table->text('reference_number')->nullable();
            $table->text('claim_type')->nullable();
            $table->text('criticality')->nullable();
            $table->date('date_of_loss')->nullable();
            $table->timestamp('initial_contact_at')->nullable();
            $table->text('cause_of_loss')->nullable();
            $table->text('description')->nullable();
            $table->decimal('anticipated_revenue', 12, 2)->nullable();
            $table->date('anticipated_invoice_date')->nullable();
            $table->timestamp('authority_received_at')->nullable();
            $table->decimal('expected_excess_payment', 12, 2)->nullable();

            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->timestamp('pinned_at')->nullable();
            $table->timestamp('touched_at');
            $table->softDeletes();

            $table->index('job_service_id');
            $table->foreign('job_service_id')
                ->references('id')
                ->on('job_services')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('insurer_id');
            $table->foreign('insurer_id')
                ->references('id')
                ->on('contacts')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('site_address_id');
            $table->foreign('site_address_id')
                ->references('id')
                ->on('addresses')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('assigned_location_id');
            $table->foreign('assigned_location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->index('owner_location_id');
            $table->foreign('owner_location_id')
                ->references('id')
                ->on('locations')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    private function createJobStatuesTable(): void
    {
        Schema::create('job_statuses', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('job_id');
            $table->bigInteger('user_id')->nullable();
            $table->text('status');
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('job_id');
            $table->foreign('job_id')
                ->references('id')
                ->on('jobs')
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
}
