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
        if (preg_match("#^add|delete$#", $operation) &&
            preg_match("#^\d+$#", $area) &&
            preg_match("#^\d+\.?\d*$#", $weight)
            ) {
            // check if area exists in db
            $exists = AreaQuery::create()
                ->findPK($area);

            if ($exists !== null) {
                $json_path= __DIR__."/../".DpdClassic::JSON_PRICE_RESOURCE;

                if (is_readable($json_path)) {
                    $json_data = json_decode(file_get_contents($json_path), true);
                } elseif (!file_exists($json_path)) {
                    $json_data = array();
                } else {
                    throw new \Exception("Can't read DpdClassic".DpdClassic::JSON_PRICE_RESOURCE.". Please change the rights on the file.");
                }

                if ((float) $weight > 0 && $operation == "add"
                  && preg_match("#\d+\.?\d*#", $price)) {
                    $json_data[$area]['slices'][$weight] = $price;
                } elseif ($operation == "delete") {
                    if (isset($json_data[$area]['slices'][$weight])) {
                        unset($json_data[$area]['slices'][$weight]);
                    }
                } else {
                    throw new \Exception("Weight must be superior to 0");
                }

                ksort($json_data[$area]['slices']);

                if ((file_exists($json_path) ?is_writable($json_path):is_writable(__DIR__."/../"))) {
                    $file = fopen($json_path, 'w');
                    fwrite($file, json_encode($json_data));
                    ;
                    fclose($file);
                } else {
                    throw new \Exception("Can't write DpdClassic".DpdClassic::JSON_PRICE_RESOURCE.". Please change the rights on the file.");
                }
            } else {
                throw new \Exception("Area not found");
            }
        } else {
            throw new \ErrorException("Arguments are missing or invalid");
        }

        return $this->generateRedirectFromRoute(
            "admin.module.configure",
            [],
            [
                'module_code'=>"DpdClassic",
                'current_tab'=>"price_slices_tab",
                '_controller' => 'Thelia\\Controller\\Admin\\ModuleController::configureAction'
            ]
        );
    }
}
