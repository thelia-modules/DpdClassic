<?php

namespace DpdClassic\EventListeners;

use DpdClassic\DpdClassic;
use OpenApi\Events\DeliveryModuleOptionEvent;
use OpenApi\Events\OpenApiEvents;
use OpenApi\Model\Api\DeliveryModuleOption;
use OpenApi\Model\Api\ModelFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\Translation\Translator;
use Thelia\Model\CountryArea;
use Thelia\Model\LangQuery;
use Thelia\Module\Exception\DeliveryException;
use Thelia\Model\Base\ModuleQuery;

class APIListener implements EventSubscriberInterface
{
    /** @var ContainerInterface  */
    protected $container;

    /** @var RequestStack */
    protected $requestStack;

    /**
     * APIListener constructor.
     * @param ContainerInterface $container We need the container because we use a service from another module
     * which is not mandatory, and using its service without it being installed will crash
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->requestStack = $requestStack;
    }

    public function getDeliveryModuleOptions(DeliveryModuleOptionEvent $deliveryModuleOptionEvent)
    {
        if ($deliveryModuleOptionEvent->getModule()->getId() !== DpdClassic::getModuleId()) {
            return ;
        }

        $isValid = true;
        $postage = null;

        $locale = $this->requestStack->getCurrentRequest()->getSession()->getLang()->getLocale();

        try {
            $module = new DpdClassic();
            $country = $deliveryModuleOptionEvent->getCountry();

            $postage = $module->getOrderPostage(
                $country,
                $deliveryModuleOptionEvent->getCart()->getWeight(),
                $locale,
                $deliveryModuleOptionEvent->getCart()->getTaxedAmount($country)
            );
        } catch (\Exception $exception) {
            $isValid = false;
        }

        $minimumDeliveryDate = ''; // TODO (calculate delivery date from day of order)
        $maximumDeliveryDate = ''; // TODO (calculate delivery date from day of order

        $propelModule = ModuleQuery::create()
            ->filterById(DpdClassic::getModuleId())
            ->findOne();

        /** @var DeliveryModuleOption $deliveryModuleOption */
        $deliveryModuleOption = ($this->container->get('open_api.model.factory'))->buildModel('DeliveryModuleOption');
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

    public static function getSubscribedEvents()
    {
        $listenedEvents = [];

        /** Check for old versions of Thelia where the events used by the API didn't exists */
        if (class_exists(DeliveryModuleOptionEvent::class)) {
            $listenedEvents[OpenApiEvents::MODULE_DELIVERY_GET_OPTIONS] = array("getDeliveryModuleOptions", 129);
        }

        return $listenedEvents;
    }
}
