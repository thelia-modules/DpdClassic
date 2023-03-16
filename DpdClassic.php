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

use DpdClassic\Model\PricesSlices;
use DpdClassic\Model\PricesSlicesQuery;
use DpdClassic\Service\TranslateService;
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

    protected $request;
    protected $dispatcher;

    private static $prices = null;


    public function postActivation(ConnectionInterface $con = null): void
    {
        $database = new Database($con->getWrappedConnection());

        $database->insertSql(null, array(__DIR__ . '/Config/TheliaMain.sql'));
        $this->JsonToDatabase();
    }

    public function JsonToDatabase()
    {
        $json_path= __DIR__.DpdClassic::JSON_PRICE_RESOURCE;

        if (!is_readable($json_path)) {
            throw new \Exception("Can't read DpdClassic".DpdClassic::JSON_PRICE_RESOURCE.". Please change the rights on the file.");
        }
        $json_data = json_decode(file_get_contents($json_path), true);

        foreach ($json_data as $areaIndex => $data){

            foreach ($data["slices"] as $weightIndex => $slice){
                $priceSlice = new PricesSlices();
                $priceSlice
                    ->setWeight($weightIndex)
                    ->setPrice($slice)
                    ->setIdArea($areaIndex);

                if (array_key_exists("_info",$data)){
                    $priceSlice->setInfo($data["_info"]);
                }
                $priceSlice->save();
            }
        }
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
        $idAreas = PricesSlicesQuery::create()->select(['id_area'])->setDistinct()->find()->getData();
        foreach ($idAreas as $idArea){
            $priceSliceInfo = PricesSlicesQuery::create()->findOneByIdArea($idArea)->getInfo();
            if ($priceSliceInfo !== null){
                $prices[(string)$idArea]['info_'] = $priceSliceInfo;
            }
            $prices[(string)$idArea]['slices'] = [];
        }
        foreach ($idAreas as $idArea){
            $pricesSlices = PricesSlicesQuery::create()->filterByIdArea($idArea);
            foreach ($pricesSlices as $pricesSlice){
                $price = $pricesSlice->getPrice();
                $weight = (string)$pricesSlice->getWeight();
                if (strpos( $weight, "." )){
                    $price = (string)$price;
                }
                $prices[$idArea]['slices'][$weight] = $price;
            }
        }
        return $prices;
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
