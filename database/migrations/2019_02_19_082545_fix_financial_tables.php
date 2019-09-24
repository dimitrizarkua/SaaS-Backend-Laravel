<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Components\Finance\Models\Invoice;
use App\Components\Finance\Models\CreditNote;
use Illuminate\Database\Migrations\Migration;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\FinancialEntity;

class FixFinancialTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @throws \Throwable
     */
    public function up()
    {
        Invoice::disableSearchSyncing();
        $invoiceList = Invoice::whereNull('recipient_address')->get();
        foreach ($invoiceList as $invoice) {
            $this->fillRecipientInfo($invoice);
        }
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('recipient_address')->nullable(false)->change();
            $table->text('recipient_name')->nullable(false)->change();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->text('recipient_address')->nullable(true);
            $table->text('recipient_name')->nullable(true);
        });
        PurchaseOrder::disableSearchSyncing();
        foreach (PurchaseOrder::all() as $purchaseOrder) {
            $this->fillRecipientInfo($purchaseOrder);
        }
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->text('recipient_address')->nullable(false)->change();
            $table->text('recipient_name')->nullable(false)->change();
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            $table->text('recipient_address')->nullable(true);
            $table->text('recipient_name')->nullable(true);
        });
        CreditNote::disableSearchSyncing();
        foreach (CreditNote::all() as $creditNote) {
            $this->fillRecipientInfo($creditNote);
        }
        Schema::table('credit_notes', function (Blueprint $table) {
            $table->text('recipient_address')->nullable(false)->change();
            $table->text('recipient_name')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->text('recipient_address')->nullable(true)->change();
            $table->text('recipient_name')->nullable(true)->change();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('recipient_address');
            $table->dropColumn('recipient_name');
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            $table->dropColumn('recipient_address');
            $table->dropColumn('recipient_name');
        });
    }

    /**
     * @param FinancialEntity $entity
     *
     * @throws Throwable
     */
    private function fillRecipientInfo(FinancialEntity $entity)
    {
        $contact = $entity->recipientContact;

        $address = $contact->getMailingAddress();
        if (null === $address) {
            if ($contact->addresses->isEmpty()) {
                $address = '';
            } else {
                $address = $contact->addresses->first();
            }
        }

        $entity->recipient_address = $address;
        $entity->recipient_name    = $contact->getContactName();
        $entity->saveOrFail();
    }
}
