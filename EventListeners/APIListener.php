<?php

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DpdClassic\EventListeners;

use DpdClassic\DpdClassic;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Api\Bridge\Propel\Event\DeliveryModuleOptionEvent;
use Thelia\Api\Resource\DeliveryModuleOption;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Model\Base\ModuleQuery;

readonly class APIListener implements EventSubscriberInterface
{
    public function __construct(
        private Session $session,
    ) {
    }

    public function getDeliveryModuleOptions(DeliveryModuleOptionEvent $deliveryModuleOptionEvent): void
    {
        if ($deliveryModuleOptionEvent->getModule()->getId() !== DpdClassic::getModuleId()) {
            return;
        }

        $isValid = true;
        $postage = null;

        $locale = $this->session->getLang()->getLocale();

        try {
            $module = new DpdClassic();
            $country = $deliveryModuleOptionEvent->getCountry();
            $cart = $deliveryModuleOptionEvent->getCart();
            if (null === $cart) {
                throw new \Exception('Cart not found');
            }
            $postage = $module->getOrderPostage(
                $country,
                $cart->getWeight(),
                $locale,
                $cart->getTaxedAmount($country)
            );
        } catch (\Exception) {
            $isValid = false;
        }

        $minimumDeliveryDate = ''; // TODO (calculate delivery date from day of order)
        $maximumDeliveryDate = ''; // TODO (calculate delivery date from day of order

        $propelModule = ModuleQuery::create()
            ->filterById(DpdClassic::getModuleId())
            ->findOne();

        $deliveryModuleOption = new DeliveryModuleOption();
        $deliveryModuleOption
            ->setCode(DpdClassic::getModuleCode())
            ->setValid($isValid)
            ->setTitle($propelModule->setLocale($locale)->getTitle())
            ->setImage('')
            ->setMinimumDeliveryDate($minimumDeliveryDate)
            ->setMaximumDeliveryDate($maximumDeliveryDate)
            ->setPostage($postage?->getAmount())
            ->setPostageTax($postage?->getAmountTax())
            ->setPostageUntaxed($postage?->getAmount() - $postage?->getAmountTax())
        ;

        $deliveryModuleOptionEvent->appendDeliveryModuleOptions($deliveryModuleOption);
    }

    public static function getSubscribedEvents(): array
    {
        $listenedEvents = [];

        /* Check for old versions of Thelia where the events used by the API didn't exists */
        if (class_exists(DeliveryModuleOptionEvent::class)) {
            $listenedEvents[TheliaEvents::MODULE_DELIVERY_GET_OPTIONS] = ['getDeliveryModuleOptions', 129];
        }

        return $listenedEvents;
    }
}
