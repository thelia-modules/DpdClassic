<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia                                                                       */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*      along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace DpdClassic\Controller;

use DpdClassic\DpdClassic;
use DpdClassic\Form\ExportForm;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Event\Order\OrderEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
use Thelia\Log\Tlog;
use Thelia\Model\CustomerQuery;
use Thelia\Model\Order;
use Thelia\Model\OrderAddressQuery;
use Thelia\Model\OrderQuery;
use Thelia\Model\OrderStatus;
use Thelia\Model\OrderStatusQuery;

/**
 * Class ExportController
 * @package DpdClassic\Controller
 * @author Thelia <info@thelia.net>
 * @original_author etienne roudeix <eroudeix@openstudio.fr>
 * @contributor Etienne Perriere <eperriere@openstudio.fr>
 */
class ExportController extends BaseAdminController
{
    // FONCTION POUR LE FICHIER D'EXPORT BY Maitre eroudeix@openstudio.fr
    // extended by bperche9@gmail.com
    public static function harmonise($value, $type, $len)
    {
        switch ($type) {
            case 'numeric':
                $value = (string)$value;
                if (mb_strlen($value, 'utf8') > $len) {
                    $value = substr($value, 0, $len);
                }
                for ($i = mb_strlen($value, 'utf8'); $i < $len; $i++) {
                    $value = '0' . $value;
                }
                break;
            case 'alphanumeric':
                $value = (string)$value;
                if (mb_strlen($value, 'utf8') > $len) {
                    $value = substr($value, 0, $len);
                }
                for ($i = mb_strlen($value, 'utf8'); $i < $len; $i++) {
                    $value .= ' ';
                }
                break;
            case 'float':
                if (!preg_match("#\d{1,6}\.\d{1,}#", $value)) {
                    $value = str_repeat("0", $len - 3) . ".00";
                } else {
                    $value = explode(".", $value);
                    $int = self::harmonise($value[0], 'numeric', $len - 3);
                    $dec = substr($value[1], 0, 2) . "." . substr($value[1], 2, strlen($value[1]));
                    $dec = (string)ceil(floatval($dec));
                    $dec = str_repeat("0", 2 - strlen($dec)) . $dec;
                    $value = $int . "." . $dec;
                }
                break;
        }

        return $value;
    }

    public function exportFileAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['DpdClassic'], AccessManager::UPDATE)) {
            return $response;
        }

        if (is_readable(ExportExaprintController::getJSONpath())) {
            $admici = json_decode(file_get_contents(ExportExaprintController::getJSONpath()), true);
            $keys = array("name", "addr", "zipcode", "city", "tel", "mobile", "mail", "expcode");
            $valid = true;
            foreach ($keys as $key) {
                $valid &= isset($admici[$key]) && ($key === "assur" ? true : !empty($admici[$key]));
            }
            if (!$valid) {
                return Response::create(
                    Translator::getInstance()->trans(
                        "The file DpdClassic/Config/sender.json is not valid. Please correct it.",
                        [],
                        DpdClassic::DOMAIN_NAME
                    ),
                    500
                );
            }
        } else {
            return Response::create(
                Translator::getInstance()->trans(
                    "Can't read DpdClassic/Config/sender.json. Did you save the export information ?",
                    [],
                    DpdClassic::DOMAIN_NAME
                ),
                500
            );
        }

        $exp_name = $admici['name'];
        $exp_address1 = $admici['addr'];
        $exp_address2 = isset($admici['addr2']) ? $admici['addr2'] : "";
        $exp_zipcode = $admici['zipcode'];
        $exp_city = $admici['city'];
        $exp_phone = $admici['tel'];
        $exp_cellphone = $admici['mobile'];
        $exp_email = $admici['mail'];
        $exp_code = $admici['expcode'];
        $res = self::harmonise('$' . "VERSION=110", 'alphanumeric', 12) . "\r\n";

        $orders = OrderQuery::create()
            ->filterByDeliveryModuleId(DpdClassic::getModuleId())
            ->filterByStatusId([DpdClassic::STATUS_PAID, DpdClassic::STATUS_PROCESSING])
            ->orderByCreatedAt(Criteria::DESC)
            ->find();

        // FORM VALIDATION
        $form = new ExportForm($this->getRequest());
        $status_id = null;

        try {
            $vform = $this->validateForm($form);
            $status_id = $vform->get("new_status_id")->getData();
            if (!preg_match("#^nochange|processing|sent$#", $status_id)) {
                throw new \Exception("Invalid status ID. Expecting nochange or processing or sent");
            }
        } catch (\Exception $e) {
            Tlog::getInstance()->error("Form dpdclassic.export sent with bad infos. ");

            return Response::create(
                Translator::getInstance()->trans(
                    "Got invalid data : %err",
                    ['%err' => $e->getMessage()],
                    DpdClassic::DOMAIN_NAME
                ),
                500
            );
        }

        // For each selected order
        /** @var Order $order */
        foreach ($orders as $order) {
            $orderRef = str_replace(".", "-", $order->getRef());

            if ($vform->get($orderRef)->getData()) {
                // Get if the package is assured, how many packages there are & their weight
                $assur_package = $vform->get($orderRef . "-assur")->getData();
                $pkgNumber = $vform->get($orderRef . '-pkgNumber')->getData();
                $pkgWeight = $vform->get($orderRef . '-pkgWeight')->getData();

                // Check if status has to be changed
                if ($status_id == "processing") {
                    $event = new OrderEvent($order);
                    $status = OrderStatusQuery::create()
                        ->findOneByCode(OrderStatus::CODE_PROCESSING);
                    $event->setStatus($status->getId());
                    $this->getDispatcher()->dispatch(TheliaEvents::ORDER_UPDATE_STATUS, $event);
                } elseif ($status_id == "sent") {
                    $event = new OrderEvent($order);
                    $status = OrderStatusQuery::create()
                        ->findOneByCode(OrderStatus::CODE_SENT);
                    $event->setStatus($status->getId());
                    $this->getDispatcher()->dispatch(TheliaEvents::ORDER_UPDATE_STATUS, $event);
                }

                //Get customer's delivery address
                $address = OrderAddressQuery::create()
                    ->findPK($order->getDeliveryOrderAddressId());

                //Get Customer object
                $customer = CustomerQuery::create()
                    ->findPK($order->getCustomerId());

                // Get cellphone
                if (null == $cellphone = $address->getCellphone()) {
                    $address->getPhone();
                }

                //Weight & price calc
                $price = 0;
                $price = $order->getTotalAmount($price, false); // tax = 0 && include postage = false

                $pkgWeight = floor($pkgWeight * 100);

                $assur_price = ($assur_package == 'true') ? $price : 0;
                $date_format = date("d/m/y", $order->getUpdatedAt()->getTimestamp());

                $res .= self::harmonise($order->getRef(), 'alphanumeric', 35);              // Order ref
                $res .= self::harmonise("", 'alphanumeric', 2);
                $res .= self::harmonise($pkgWeight, 'numeric', 8);                          // Package weight
                $res .= self::harmonise("", 'alphanumeric', 15);
                $res .= self::harmonise($address->getLastname(), 'alphanumeric', 35);       // Delivered customer
                $res .= self::harmonise($address->getFirstname(), 'alphanumeric', 35);
                $res .= self::harmonise($address->getAddress2(), 'alphanumeric', 35);       // Delivered address info
                $res .= self::harmonise($address->getAddress3(), 'alphanumeric', 35);
                $res .= self::harmonise("", 'alphanumeric', 35);
                $res .= self::harmonise("", 'alphanumeric', 35);
                $res .= self::harmonise($address->getZipcode(), 'alphanumeric', 10);        // Delivered address
                $res .= self::harmonise($address->getCity(), 'alphanumeric', 35);
                $res .= self::harmonise("", 'alphanumeric', 10);
                $res .= self::harmonise($address->getAddress1(), 'alphanumeric', 35);
                $res .= self::harmonise("", 'alphanumeric', 10);
                $res .= self::harmonise("F", 'alphanumeric', 3);                // Default delivered country code
                $res .= self::harmonise($address->getPhone(), 'alphanumeric', 30);          // Delivered phone
                $res .= self::harmonise("", 'alphanumeric', 15);
                $res .= self::harmonise($exp_name, 'alphanumeric', 35);                     // Expeditor name
                $res .= self::harmonise($exp_address2, 'alphanumeric', 35);                 // Expeditor address
                $res .= self::harmonise("", 'alphanumeric', 140);
                $res .= self::harmonise($exp_zipcode, 'alphanumeric', 10);
                $res .= self::harmonise($exp_city, 'alphanumeric', 35);
                $res .= self::harmonise("", 'alphanumeric', 10);
                $res .= self::harmonise($exp_address1, 'alphanumeric', 35);
                $res .= self::harmonise("", 'alphanumeric', 10);
                $res .= self::harmonise("F", 'alphanumeric', 3);                   // Default expeditor country code
                $res .= self::harmonise($exp_phone, 'alphanumeric', 30);                    // Expeditor phone
                $res .= self::harmonise("", 'alphanumeric', 35);                            // Order comment 1
                $res .= self::harmonise("", 'alphanumeric', 35);                            // Order comment 2
                $res .= self::harmonise("", 'alphanumeric', 35);                            // Order comment 3
                $res .= self::harmonise("", 'alphanumeric', 35);                            // Order comment 4
                $res .= self::harmonise($date_format.' ', 'alphanumeric', 10);              // Date
                $res .= self::harmonise($exp_code, 'numeric', 8);                           // Expeditor DPD code
                $res .= self::harmonise("", 'alphanumeric', 35);                            // Bar code
                $res .= self::harmonise($customer->getRef(), 'alphanumeric', 35);           // Customer ref
                $res .= self::harmonise("", 'alphanumeric', 29);
                $res .= self::harmonise($assur_price, 'float', 9);                          // Insured value
                $res .= self::harmonise("", 'alphanumeric', 8);
                $res .= self::harmonise($customer->getId(), 'alphanumeric', 35);            // Customer ID
                $res .= self::harmonise("", 'alphanumeric', 46);
                $res .= self::harmonise($exp_email, 'alphanumeric', 80);                    // Expeditor email
                $res .= self::harmonise($exp_cellphone, 'alphanumeric', 35);                // Expeditor cellphone
                $res .= self::harmonise($customer->getEmail(), 'alphanumeric', 80);         // Customer email
                $res .= self::harmonise($cellphone, 'alphanumeric', 35);                    // Customer phone
                $res .= self::harmonise("", 'alphanumeric', 96);

                $res .= "\r\n";
            }
        }

        $response = new Response(
            utf8_decode(mb_strtoupper($res)),
            200,
            array(
                'Content-Type' => 'application/csv-tab-delimited-table;charset=iso-8859-1',
                'Content-disposition' => 'filename=export.dat'
            )
        );

        return $response;
    }
}
