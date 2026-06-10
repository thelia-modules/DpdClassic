<?php

namespace DpdClassic\Hook;

use DpdClassic\Controller\ExportExaprintController;
use DpdClassic\DpdClassic;
use DpdClassic\Form\ConfigurationForm;
use DpdClassic\Form\ExportExaprintForm;
use DpdClassic\Form\ExportForm;
use DpdClassic\Form\FreeShippingAmountForm;
use DpdClassic\Form\FreeShippingForm;
use DpdClassic\Form\ImportExaprintForm;
use DpdClassic\Form\TaxRuleForm;
use Propel\Runtime\ActiveQuery\Criteria;
use Symfony\Component\Form\FormView;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Thelia\Core\Event\Hook\BaseHookRenderEvent;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Form\TheliaFormFactory;
use Thelia\Core\Hook\BaseHook;
use Thelia\Core\Template\Parser\ParserResolver;
use Thelia\Model\AreaQuery;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\OrderQuery;
use Thelia\Tools\MoneyFormat;

/**
 * Class DpdClassicHook
 * @package DpdClassic\Hook
 * @author Etienne Perriere <eperriere@openstudio.fr>
 */
class DpdClassicHook extends BaseHook
{
    public function __construct(
        private readonly TheliaFormFactory $formFactory,
        ?EventDispatcherInterface $dispatcher = null,
        ?ParserResolver $parserResolver = null,
    ) {
        parent::__construct($dispatcher, $parserResolver);
    }

    public static function getSubscribedHooks(): array
    {
        return [
            'module.configuration' => [
                ['type' => 'back', 'method' => 'onModuleConfig'],
            ],
            'module.config-js' => [
                ['type' => 'back', 'method' => 'onModuleConfigJs'],
            ],
            'order.tab-content' => [
                ['type' => 'back', 'method' => 'onOrderModuleTab'],
            ],
        ];
    }

    public function onModuleConfig(HookRenderEvent $event): void
    {
        $request = $this->getRequest();
        $currentTab = $request?->query->get('current_tab') ?? '';

        $event->add($this->render('DpdClassic/dpdclassic-configuration.html.twig', [
            'check_rights_errors' => $this->getCheckRightsErrors(),
            'current_tab' => $currentTab,
            'currency_symbol' => $this->getDefaultCurrencySymbol(),
            'orders' => $this->getPendingOrders(),
            'areas' => $this->getAreasWithPrices(),
            'config_form' => $this->buildView(ConfigurationForm::getName()),
            'export_form' => $this->buildView(ExportForm::getName()),
            'import_form' => $this->buildView(ImportExaprintForm::getName()),
            'export_exaprint_form' => $this->buildView(ExportExaprintForm::getName()),
            'freeshipping_form' => $this->buildView(FreeShippingForm::getName()),
            'freeshipping_amount_form' => $this->buildView(FreeShippingAmountForm::getName()),
            'tax_rule_form' => $this->buildView(TaxRuleForm::getName()),
            'sender' => $this->getSenderValues(),
            'free_shipping_enabled' => (bool) DpdClassic::getConfigValue('freeshipping'),
            'free_shipping_amount' => (float) DpdClassic::getFreeShippingAmount(),
        ]));
    }

    public function onModuleConfigJs(HookRenderEvent $event): void
    {
        $event->add($this->render('DpdClassic/dpdclassic-config-js.html.twig', [
            'order_refs' => array_map(
                static fn ($order) => str_replace('.', '-', $order['ref']),
                $this->getPendingOrders()
            ),
        ]));
    }

    public function onOrderModuleTab(BaseHookRenderEvent $event): void
    {
        $orderId = $event->getArgument('order_id');
        $order = $orderId !== null ? OrderQuery::create()->findPk((int) $orderId) : null;

        $trackingUrl = null;
        $ref = null;

        if ($order !== null
            && $order->getDeliveryModuleId() === DpdClassic::getModuleId()
            && !in_array($order->getOrderStatus()?->getCode(), ['not_paid', 'sent', 'canceled', 'refunded'], true)
        ) {
            $ref = $order->getRef();
            $trackingUrl = sprintf('http://www.dpd.fr/traces_info_%s', $order->getDeliveryRef());
        }

        $content = $this->render('DpdClassic/dpdclassic-order-edit.html.twig', [
            'export_form' => $this->buildView(ExportForm::getName()),
            'order_ref' => $ref,
            'tracking_url' => $trackingUrl,
        ]);

        // The default-twig BO probes render hooks via has_hook(), which dispatches a
        // HookRenderBlockEvent; the actual content is emitted through safe_hook() (HookRenderEvent).
        if ($event instanceof HookRenderBlockEvent) {
            $event->add(['id' => 'dpdclassic', 'content' => $content]);

            return;
        }

        if ($event instanceof HookRenderEvent) {
            $event->add($content);
        }
    }

    private function buildView(string $formName): FormView
    {
        return $this->formFactory->createForm($formName)->createView()->getView();
    }

    private function getDefaultCurrencySymbol(): string
    {
        return CurrencyQuery::create()->findOneByByDefault(true)?->getSymbol() ?? '';
    }

    private function getCheckRightsErrors(): array
    {
        $errors = [];
        $dir = __DIR__ . '/../Config/';

        if (!is_readable($dir)) {
            $errors[] = ['message' => $this->trans('Can\'t read Config directory', [], DpdClassic::DOMAIN_NAME), 'file' => ''];
        }
        if (!is_writable($dir)) {
            $errors[] = ['message' => $this->trans('Can\'t write Config directory', [], DpdClassic::DOMAIN_NAME), 'file' => ''];
        }

        if ($handle = @opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (strlen($file) > 5 && substr($file, -5) === '.json') {
                    if (!is_readable($dir . $file)) {
                        $errors[] = ['message' => $this->trans('Can\'t read file', [], DpdClassic::DOMAIN_NAME), 'file' => 'DpdClassic/Config/' . $file];
                    }
                    if (!is_writable($dir . $file)) {
                        $errors[] = ['message' => $this->trans('Can\'t write file', [], DpdClassic::DOMAIN_NAME), 'file' => 'DpdClassic/Config/' . $file];
                    }
                }
            }
            closedir($handle);
        }

        return $errors;
    }

    private function getPendingOrders(): array
    {
        $orders = OrderQuery::create()
            ->filterByDeliveryModuleId(DpdClassic::getModuleId())
            ->filterByStatusId([DpdClassic::STATUS_PAID, DpdClassic::STATUS_PROCESSING])
            ->orderByCreatedAt(Criteria::DESC)
            ->find();

        $request = $this->getRequest();
        $moneyFormat = $request !== null ? MoneyFormat::getInstance($request) : null;

        $result = [];

        foreach ($orders as $order) {
            $customer = $order->getCustomer();
            $currency = $order->getCurrency();
            $symbol = $currency?->getSymbol();
            $amount = (float) $order->getTotalAmount();

            $result[] = [
                'id' => $order->getId(),
                'ref' => $order->getRef(),
                'create_date' => $order->getCreatedAt(),
                'total_taxed_amount' => $moneyFormat !== null
                    ? $moneyFormat->format($amount, null, null, null, $symbol)
                    : number_format($amount, 2) . ($symbol !== null ? ' ' . $symbol : ''),
                'customer_id' => $customer?->getId(),
                'customer_firstname' => $customer?->getFirstname(),
                'customer_lastname' => $customer?->getLastname(),
            ];
        }

        return $result;
    }

    private function getAreasWithPrices(): array
    {
        $moduleId = DpdClassic::getModuleId();
        $prices = DpdClassic::getPrices() ?? [];

        $areas = AreaQuery::create()
            ->useAreaDeliveryModuleQuery()
                ->filterByDeliveryModuleId([$moduleId], Criteria::IN)
            ->endUse()
            ->find();

        $result = [];

        foreach ($areas as $area) {
            $areaId = $area->getId();
            $slices = [];

            if (isset($prices[$areaId]['slices'])) {
                $slices = $prices[$areaId]['slices'];
                ksort($slices);
            }

            $sliceRows = [];
            foreach ($slices as $maxWeight => $price) {
                $sliceRows[] = ['max_weight' => $maxWeight, 'price' => $price];
            }

            $result[] = [
                'id' => $areaId,
                'name' => $area->getName(),
                'slices' => $sliceRows,
            ];
        }

        return $result;
    }

    private function getSenderValues(): array
    {
        $path = ExportExaprintController::getJSONpath();

        if (is_readable($path)) {
            return json_decode(file_get_contents($path), true) ?: [];
        }

        return [];
    }
}
