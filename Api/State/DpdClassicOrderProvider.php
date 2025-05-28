<?php

namespace DpdClassic\Api\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DpdClassic\DpdClassic;
use DpdClassic\Api\Resource\DpdClassicOrder;
use Thelia\Model\OrderQuery;

class DpdClassicOrderProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): iterable
    {
        $orders = OrderQuery::create()
            ->filterByDeliveryModuleId(DpdClassic::getModuleId())
            ->filterByStatusId([DpdClassic::STATUS_PAID, DpdClassic::STATUS_PROCESSING])
            ->orderByCreatedAt()
            ->find();

        foreach ($orders as $order) {
            yield (new DpdClassicOrder())
                ->setId($order->getId())
                ->setRef($order->getRef())
                ->setStatus($order->getStatusId())
                ->setCustomerId($order->getCustomerId());
        }
    }
}
