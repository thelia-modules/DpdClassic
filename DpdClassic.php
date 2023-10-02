<?php
/*************************************************************************************/
/*      This file is part of the Thelia package.                                     */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : dev@thelia.net                                                       */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      For the full copyright and license information, please view the LICENSE.txt  */
/*      file that was distributed with this source code.                             */
/*************************************************************************************/

namespace DpdClassic;

use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Thelia\Exception\OrderException;
use Thelia\Install\Database;
use Thelia\Model\Country;
use Thelia\Model\OrderPostage;
use Thelia\Module\AbstractDeliveryModule;
use Thelia\Module\Exception\DeliveryException;

class DpdClassic extends AbstractDeliveryModule
{
    const DOMAIN_NAME = 'dpdclassic';

    const DELIVERY_REF_COLUMN = 17;
    const ORDER_REF_COLUMN = 18;

    const STATUS_PAID = 2;
    const STATUS_PROCESSING = 3;
    const STATUS_SENT = 4;

    const NO_CHANGE = 'nochange';
    const PROCESS = 'processing';
    const SEND = 'sent';

    const JSON_PRICE_RESOURCE = "/Config/prices.json";

    const DPD_CLASSIC_TAX_RULE_ID = 'dpd_classic_tax_rule_id';

    protected $request;
    protected $dispatcher;

    private static $prices = null;

    public function postActivation(ConnectionInterface $con = null): void
    {
        $database = new Database($con->getWrappedConnection());

        if (!self::getConfigValue(self::DPD_CLASSIC_TAX_RULE_ID)) {
            self::setConfigValue(self::DPD_CLASSIC_TAX_RULE_ID, null);
        }

        $database->insertSql(null, array(__DIR__ . '/Config/thelia.sql'));
    }

    /**
     * This method is called by the Delivery  loop, to check if the current module has to be displayed to the customer.
     * Override it to implements your delivery rules/
     *
     * If you return true, the delivery method will de displayed to the customer
     * If you return false, the delivery method will not be displayed
     *
     * @param Country $country the country to deliver to.
     *
     * @return boolean
     */
    public function isValidDelivery(Country $country)
    {
        $cartWeight = $this->getRequest()->getSession()->getSessionCart($this->getDispatcher())->getWeight();

        $areaId = $country->getAreaId();

        $prices = self::getPrices();

        /* Check if DpdClassic delivers the asked area */
        if (isset($prices[$areaId]) && isset($prices[$areaId]["slices"])) {
            $areaPrices = $prices[$areaId]["slices"];
            ksort($areaPrices);

            /* check this weight is not too much */
            end($areaPrices);

            $maxWeight = key($areaPrices);
            if ($cartWeight <= $maxWeight) {
                return true;
            }
        }

        return false;
    }

    public static function getPrices()
    {
        if (null === self::$prices) {
            if (is_readable(sprintf('%s/%s', __DIR__, self::JSON_PRICE_RESOURCE))) {
                self::$prices = json_decode(
                    file_get_contents(sprintf('%s/%s', __DIR__, self::JSON_PRICE_RESOURCE)),
                    true
                );
            } else {
                self::$prices = null;
            }
        }

        return self::$prices;
    }

    /**
     * Calculate and return delivery price in the shop's default currency
     *
     * @param Country $country the country to deliver to.
     *
     * @return OrderPostage|float             the delivery price
     * @throws DeliveryException if the postage price cannot be calculated.
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getPostage(Country $country)
    {
        $cart = $this->getRequest()->getSession()->getSessionCart($this->getDispatcher());

        $postage = self::getPostageAmount(
            $country->getAreaId(),
            $cart->getWeight(),
            $cart->getTaxedAmount($country)
        );

        return $postage;
    }

    public static function getPostageAmount($areaId, $weight, $cartAmount = 0)
    {
        $freeShipping = Dpdclassic::getConfigValue('freeshipping');
        $postage=0;

        if (!$freeShipping) {
            $freeShippingAmount = (float) self::getFreeShippingAmount();

            //If a min price for freeShipping is define and the amount of cart reach this montant return 0
            //Be carefull ! Thelia cartAmount is a decimal with 6 in precision ! That's why we must round cart amount
            if ($freeShippingAmount > 0 && $freeShippingAmount <= round($cartAmount, 2)) {
                return 0;
            }

            $prices = self::getPrices();

            /* check if DpdClassic delivers the asked area */
            if (!isset($prices[$areaId]) || !isset($prices[$areaId]["slices"])) {
                throw new DeliveryException(
                    "DPD Classic delivery unavailable for the chosen delivery country",
                    OrderException::DELIVERY_MODULE_UNAVAILABLE
                );
            }

            $areaPrices = $prices[$areaId]["slices"];
            ksort($areaPrices);

            /* check this weight is not too much */
            end($areaPrices);
            $maxWeight = key($areaPrices);
            if ($weight > $maxWeight) {
                throw new DeliveryException(
                    sprintf("DPD Classic delivery unavailable for this cart weight (%s kg)", $weight),
                    OrderException::DELIVERY_MODULE_UNAVAILABLE
                );
            }

            $postage = current($areaPrices);

            while (prev($areaPrices)) {
                if ($weight > key($areaPrices)) {
                    break;
                }

                $postage = current($areaPrices);
            }
        }

        return (float) $postage;
    }


    public static function getFreeShippingAmount()
    {
        if (!null !== $amount = self::getConfigValue('free_shipping_amount')) {
            return (float) $amount;
        }

        return 0;
    }

    public static function setFreeShippingAmount($amount)
    {
        self::setConfigValue('free_shipping_amount', $amount);
    }

    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([THELIA_MODULE_DIR . ucfirst(self::getModuleCode()). "/I18n/*"])
            ->autowire(true)
            ->autoconfigure(true);
    }
}
