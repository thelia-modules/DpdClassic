<?php

namespace DpdClassic\EventListeners;

use DpdClassic\DpdClassic;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Thelia\Model\AreaDeliveryModuleQuery;
use Thelia\Model\ModuleConfigQuery;

class ConfigListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            'module.config' => ['onModuleConfig', 128]
        ];
    }

    public function onModuleConfig(GenericEvent $event)
    {
        $subject = $event->getSubject();

        if ($subject !== "HealthStatus") {
            throw new \RuntimeException('Event subject does not match expected value');
        }

        $shippingZoneConfig = AreaDeliveryModuleQuery::create()
            ->filterByDeliveryModuleId(DpdClassic::getModuleId())
            ->find();

        $freeShippingAmount = ModuleConfigQuery::create()
            ->filterByModuleId(DpdClassic::getModuleId())
            ->filterByName('free_shipping_amount')
            ->findOne();

        $freeShipping = ModuleConfigQuery::create()
            ->filterByModuleId(DpdClassic::getModuleId())
            ->filterByName('freeshipping')
            ->findOne();

        $taxRuleId = ModuleConfigQuery::create()
            ->filterByModuleId(DpdClassic::getModuleId())
            ->filterByName('dpd_classic_tax_rule_id')
            ->findOne();

        $taxRuleId = $taxRuleId?->getValue();
        $freeShippingAmount = $freeShippingAmount?->getValue();
        $freeShipping = $freeShipping?->getValue();

        $moduleConfig = [];
        $moduleConfig['module'] = DpdClassic::getModuleCode();
        $configsCompleted = false;

        $path = __DIR__ . '/../Config/prices.json';
        $checkSlices = is_readable($path);

        if ($checkSlices) {
            $jsonContent = file_get_contents($path);
            $data = json_decode($jsonContent, true);
            if (empty($data)) {
                $checkSlices = false;
            } else {
                $firstElement = reset($data);
                $checkSlices = isset($firstElement['slices']) && is_array($firstElement['slices']);
                if ($checkSlices && empty($firstElement['slices'])) {
                    $checkSlices = false;
                }
            }
        }

        if ($taxRuleId !== null && !$shippingZoneConfig->isEmpty() &&
            ($freeShippingAmount !== null || $freeShipping !== "") || $checkSlices === true) {
            $configsCompleted = true;
        }

        $moduleConfig['completed'] = $configsCompleted;

        $event->setArgument('dpd.classic.config', $moduleConfig);
    }





}
