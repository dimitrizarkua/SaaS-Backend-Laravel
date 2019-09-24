<?php

use App\Components\Contacts\Models\Enums\ContactStatuses;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ContactHasManyStatuses extends Migration
{
    /**
     * Migrate to new table structure. It is possible to store history of statuses for contact.
     *
     * @return void
     * @throws \Throwable
     */
    public function up()
    {
        DB::transaction(function () {
            $records = DB::query()
                ->from('contacts')
                ->selectRaw('
                    contacts.id AS contact_id,
                    contact_statuses.name as status_name
                ')
                ->join(
                    'contact_statuses',
                    'contact_statuses.id',
                    '=',
                    'contacts.contact_status_id'
                )
                ->get();

            Schema::table('contacts', function (Blueprint $table) {
                $table->dropForeign(['contact_status_id']);
                $table->dropColumn(['contact_status_id']);
            });

            Schema::drop('contact_statuses');

            Schema::create('contact_statuses', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->text('status');

                $table->bigInteger('contact_id');
                $table->index('contact_id');
                $table->foreign('contact_id')
                    ->references('id')
                    ->on('contacts')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');

                $table->datetime('created_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            });

            foreach ($records as $record) {
                DB::query()
                    ->from('contact_statuses')
                    ->insert([
                        'contact_id' => $record->contact_id,
                        'status'     => $record->status_name,
                    ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     * @throws \Throwable
     */
    public function down()
    {
        DB::transaction(function () {
            $records = DB::query()
                ->from('contacts')
                ->selectRaw('
                    contacts.id AS contact_id, 
                    contact_statuses.id AS status_id,
                    contact_statuses.status AS status_name
                ')
                ->join(
                    'contact_statuses',
                    'contact_statuses.contact_id',
                    '=',
                    'contacts.id'
                )
                ->get();

            Schema::table('contact_statuses', function (Blueprint $table) {
                $table->dropColumn('created_at');
                $table->dropForeign(['contact_id']);
                $table->dropColumn(['contact_id']);
            });

            Schema::drop('contact_statuses');

            Schema::create('contact_statuses', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->text('name')->unique();
            });

            foreach (ContactStatuses::values() as $status) {
                DB::query()
                    ->from('contact_statuses')
                    ->insert(['name' => $status]);
            }

            Schema::table('contacts', function (Blueprint $table) {
                $table->bigInteger('contact_status_id')->nullable(true);
                $table->index('contact_status_id');
            });

            foreach ($records as $record) {
                $status = DB::query()
                    ->from('contact_statuses')
                    ->where('name', '=', $record->status_name)
                    ->first();

                DB::query()
                    ->from('contacts')
                    ->where('contacts.id', '=', $record->contact_id)
                    ->update(['contact_status_id' => $status->id]);
            }

            Schema::table('contacts', function (Blueprint $table) {
                $table->foreign('contact_status_id')
                    ->references('id')
                    ->on('contact_statuses')
                    ->onUpdate('cascade')
                    ->onDelete('restrict');

                $table->bigInteger('contact_status_id')->nullable(false)->change();
            });
        });
    }
}
