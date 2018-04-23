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

use DpdClassic\Model\DpdclassicPrice;
use DpdClassic\Model\DpdclassicPriceQuery;
use Propel\Runtime\Connection\ConnectionInterface;
use Thelia\Exception\OrderException;
use Thelia\Install\Database;
use Thelia\Model\Country;
use Thelia\Model\MessageQuery;
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

    protected $request;
    protected $dispatcher;

    public function postActivation(ConnectionInterface $con = null)
    {
        try {
            DpdclassicPriceQuery::create()->findOne();
        } catch (\Exception $e) {
            $database = new Database($con->getWrappedConnection());
            $database->insertSql(null, [__DIR__ . '/Config/thelia.sql']);
            $database->insertSql(null, [__DIR__ . '/Config/insert_prices.sql']);
        }

        try {
            MessageQuery::create()->findOneByName('order_confirmation_dpdclassic');
        } catch (\Exception $e) {
            $database = new Database($con->getWrappedConnection());
            $database->insertSql(null, [__DIR__ . '/Config/insert_message.sql']);
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

        $slices = DpdclassicPriceQuery::create()
            ->filterByAreaId($areaId)
            ->orderByWeight()
            ->find();

        /**@var DpdclassicPrice $slice*/
        foreach ($slices as $slice) {
            if ($slice->getWeight() < $cartWeight) {
                continue;
            }
            return true;
        }
        return false;
    }

    /**
     * Calculate and return delivery price in the shop's default currency
     *
     * @param Country $country the country to deliver to.
     *
     * @return OrderPostage|float             the delivery price
     * @throws DeliveryException if the postage price cannot be calculated.
     */
    public function getPostage(Country $country)
    {
        if ('true' === Dpdclassic::getConfigValue('freeshipping')) {
            return 0;
        }
        $cartWeight = $this->getRequest()->getSession()->getSessionCart($this->getDispatcher())->getWeight();
        $areaId = $country->getAreaId();

        $slices = DpdclassicPriceQuery::create()
            ->filterByAreaId($areaId)
            ->orderByWeight()
            ->find();
        if (empty($slices)) {
            throw new DeliveryException(
                "DPD Classic delivery unavailable for the chosen delivery country",
                OrderException::DELIVERY_MODULE_UNAVAILABLE
            );
        }

        /**@var DpdclassicPrice $slice */
        foreach ($slices as $slice) {
            if ($slice->getWeight() < $cartWeight) {
                continue;
            }
            return $slice->getPrice();
        }

        throw new DeliveryException(
            sprintf("DPD Classic delivery unavailable for this cart weight (%s kg)", $cartWeight),
            OrderException::DELIVERY_MODULE_UNAVAILABLE
        );
    }
}
