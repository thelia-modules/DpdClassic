<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
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
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace DpdClassic\Controller;

use DpdClassic\DpdClassic;
use DpdClassic\Model\PricesSlices;
use DpdClassic\Model\PricesSlicesQuery;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Model\AreaQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Security\AccessManager;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/module/DpdClassic/edit-prices", name="DpdClassic_edit-prices")
 * Class EditPricesController
 * @package DpdClassic\Controller
 * @author Thelia <info@thelia.net>
 */
class EditPricesController extends BaseAdminController
{
    /**
     * @Route("", name="_edit", methods="POST")
     */
    public function editPricesAction(RequestStack $requestStack)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('DpdClassic'), AccessManager::UPDATE)) {
            return $response;
        }
        // Get data & treat
        $post = $requestStack->getCurrentRequest();
        $operation = $post->get('operation');
        $area = $post->get('area');
        $weight = $post->get('weight');
        $price = $post->get('price');

        $this->validateData($operation,$area,$weight,$price);


        if ($operation === "add") {

            $priceSlice = PricesSlicesQuery::create()->filterByIdArea($area)->filterByWeight($weight)->findOne();
            if ($priceSlice === null) {
                $priceSlice = new PricesSlices();
            }

            $priceSlice
                ->setIdArea($area)
                ->setPrice($price)
                ->setWeight($weight)
                ->save();
        }
        if ($operation === "delete") {
            $priceSlice = PricesSlicesQuery::create()->filterByIdArea($area)->filterByWeight($weight)->findOne();

            if ($priceSlice !== null) {
                $priceSlice->delete();
            }
        }

        return $this->generateRedirectFromRoute(
            "admin.module.configure",
            [],
            [
                'module_code' => "DpdClassic",
                'current_tab' => "price_slices_tab",
                '_controller' => 'Thelia\\Controller\\Admin\\ModuleController::configureAction'
            ]
        );
    }

    public function validateData($operation,$area,$weight,$price)
    {
        if (!preg_match("#^add|delete$#", $operation) || !preg_match("#^\d+$#", $area) || !preg_match("#^\d+\.?\d*$#", $weight)) {
            throw new \ErrorException("Arguments are missing or invalid");
        }

        // check if area exists in db
        $exists = AreaQuery::create()
            ->findPK($area);

        if ($exists === null) {
            throw new \Exception("Area not found");
        }

        if ((float)$weight < 0) {
            throw new \Exception("Weight must be superior to 0");
        }

        if (!preg_match("#\d+\.?\d*#", $price) && $operation === "add") {
            throw new \ErrorException("Arguments are missing or invalid");
        }
    }
}