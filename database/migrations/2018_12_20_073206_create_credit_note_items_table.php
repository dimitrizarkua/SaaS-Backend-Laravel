<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCreditNoteItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('credit_note_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('credit_note_id');
            $table->bigInteger('gs_code_id');
            $table->text('description');
            $table->integer('quantity');
            $table->decimal('unit_cost', 10, 2);
            $table->bigInteger('gl_account_id');
            $table->bigInteger('tax_rate_id');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->index('credit_note_id');
            $table->foreign('credit_note_id')
                ->references('id')
                ->on('credit_notes')
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
        Schema::dropIfExists('credit_note_items');
    }
}
