<?php

namespace App\Components\Finance\Services;

use App\Components\Finance\Events\AddApproveRequestsToPurchaseOrder;
use App\Components\Finance\Events\PurchaseOrderCreated;
use App\Components\Finance\Events\PurchaseOrderDeleted;
use App\Components\Finance\Events\PurchaseOrderStatusChanged;
use App\Components\Finance\Events\PurchaseOrderUpdated;
use App\Components\Finance\Models\FinancialEntity;
use App\Components\Finance\Models\PurchaseOrder;
use App\Components\Finance\Models\PurchaseOrderApproveRequest;
use App\Components\Finance\Models\PurchaseOrderItem;
use App\Components\Finance\ViewData\PurchaseOrderPrintVersion;
use App\Helpers\Decimal;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Class PurchaseOrdersService
 *
 * @package App\Components\Finance\Services
 *
 * @method FinancialEntity|PurchaseOrder getEntity(int $entityId)
 */
class PurchaseOrdersService extends FinancialEntityService
{
    protected $viewDataClass = PurchaseOrderPrintVersion::class;
    protected $templateName  = 'finance.purchaseOrders.print';

    /**
     * @inheritDoc
     */
    protected function getEntityClass(): string
    {
        return PurchaseOrder::class;
    }

    /**
     * @inheritDoc
     */
    protected function getItemsClassName(): string
    {
        return PurchaseOrderItem::class;
    }

    /**
     * @inheritDoc
     */
    protected function getApproveRequestClass(): string
    {
        return PurchaseOrderApproveRequest::class;
    }

    /**
     * @inheritDoc
     */
    protected function getForeignKeyName(): string
    {
        return 'purchase_order';
    }

    /**
     * @inheritDoc
     *
     * @param FinancialEntity|PurchaseOrder $entity
     */
    protected function isUserHasCorrectLimit(FinancialEntity $entity, User $user): bool
    {
        return Decimal::gte($user->purchase_order_approve_limit, $entity->getTotalAmount());
    }

    /**
     * @inheritDoc
     */
    protected function getEventsMap(): array
    {
        return [
            self::EVENT_NAME_CREATED                 => PurchaseOrderCreated::class,
            self::EVENT_NAME_DELETED                 => PurchaseOrderDeleted::class,
            self::EVENT_NAME_APPROVED                => PurchaseOrderStatusChanged::class,
            self::EVENT_NAME_APPROVE_REQUEST_CREATED => AddApproveRequestsToPurchaseOrder::class,
            self::EVENT_NAME_UPDATED                 => PurchaseOrderUpdated::class,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     * @throws \Throwable
     */
    public function getSuggestedApprovers(int $purchaseOrderId): Collection
    {
        $purchaseOrder     = $this->getEntity($purchaseOrderId);
        $purchaseOrderCost = $purchaseOrder->getTotalAmount();

        return User::query()
            ->leftJoin(
                'location_user',
                'users.id',
                '=',
                'location_user.user_id'
            )
            ->select([
                'id',
                'first_name',
                'last_name',
                'email',
                'purchase_order_approve_limit',
            ])
            ->where('location_id', '=', $purchaseOrder->location_id)
            ->where('purchase_order_approve_limit', '>=', $purchaseOrderCost)
            ->get();
    }
}
