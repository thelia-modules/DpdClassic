<?php

namespace DpdClassic\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Model\OrderQuery;

/**
 * Class DpdClassicHook
 * @package DpdClassic\Hook
 * @author Etienne Perriere <eperriere@openstudio.fr>
 */
class DpdClassicHook extends BaseHook
{
    public function onModuleConfig(HookRenderEvent $event)
    {
        $event->add($this->render('dpdclassic-configuration.html'));
    }

    public function onModuleConfigJs(HookRenderEvent $event)
    {
        $event->add($this->render('assets/js/dpdclassic-config-js.html'));
    }

    public function onOrderModuleTab(HookRenderEvent $event)
    {
        $event->add($this->render('dpdclassic-order-edit.html'));
    }

    public function onMenuItems(HookRenderEvent $event)
    {
        $event->add($this->render('hook/dpdclassic-menu-item.html'));
    }

    public function onOrderBillTop(HookRenderEvent $event)
    {
        $moduleCode = OrderQuery::create()->findOneById($event->getArgument("order_id"))->getModuleRelatedByDeliveryModuleId()->getCode();

        if("DpdClassic" === $moduleCode){
            $event->add($this->render('hook/dpdclassic-order-edit-label.html'));
        }
    }
}