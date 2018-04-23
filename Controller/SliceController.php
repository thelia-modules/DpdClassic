<?php

namespace DpdClassic\Controller;

use DpdClassic\DpdClassic;
use DpdClassic\Model\DpdclassicPrice;
use DpdClassic\Model\DpdclassicPriceQuery;
use Propel\Runtime\Map\TableMap;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;

class SliceController extends BaseAdminController
{
    public function deleteSliceAction()
    {
        if (null !== $response =
                $this->checkAuth(
                    [],
                    ['DpdClassic'],
                    [AccessManager::DELETE]
                )
        ) {
            return $response;
        }
        $messages = [];

        try {
            if (0 !== $id = intval($this->getRequest()->request->get('id'))) {
                $slice = DpdclassicPriceQuery::create()->findPk($id);
                if (null !== $slice) {
                    $slice->delete();
                }
            } else {
                $messages[] = $this->getTranslator()->trans(
                    'The slice has not been deleted',
                    [],
                    DpdClassic::DOMAIN_NAME
                );
            }
        } catch (\Exception $e) {
            $messages[] = $e->getMessage();
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

    public function saveSliceAction()
    {
        $response = $this->checkAuth([], ['DpdClassic'], AccessManager::UPDATE);

        if (null !== $response) {
            return $response;
        }

        $messages = [];
        $response = null;

        try {
            $requestData = $this->getRequest()->request;

            if (0 !== $id = intval($requestData->get('id', 0))) {
                $slice = DpdclassicPriceQuery::create()->findPk($id);
            } else {
                $slice = new DpdclassicPrice();
            }

            if (0 !== $areaId = intval($requestData->get('area', 0))) {
                $slice->setAreaId($areaId);
            } else {
                $messages[] = $this->getTranslator()->trans(
                    'The area is not valid',
                    [],
                    DpdClassic::DOMAIN_NAME
                );
            }

            $requestWeight= $requestData->get('weight', null);

            if (!empty($requestWeight)) {
                $weight= $this->getFloatVal($requestWeight);

                if ((0 < $weight) || ($requestWeight != $weight)) {
                    $slice->setWeight($weight);
                } else {
                    $messages[] = $this->getTranslator()->trans(
                        'The weight value is not valid',
                        [],
                        DpdClassic::DOMAIN_NAME
                    );
                }
            } else {
                $slice->setWeight(null);
            }

            $price = $this->getFloatVal($requestData->get('price', 0));
            if (0 <= $price) {
                $slice->setPrice($price);
            } else {
                $messages[] = $this->getTranslator()->trans(
                    'The price value is not valid',
                    [],
                    DpdClassic::DOMAIN_NAME
                );
            }

            if (0 === count($messages)) {
                $slice->save();
                $messages[] = $this->getTranslator()->trans(
                    'Your slice has been saved',
                    [],
                    DpdClassic::DOMAIN_NAME
                );

                $responseData['success'] = true;
                $responseData['slice'] = $slice->toArray(TableMap::TYPE_STUDLYPHPNAME);
            }
        } catch (\Exception $e) {
            $messages[] = $e->getMessage();
        }

        $responseData['message'] = $messages;

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

    protected function getFloatVal($val, $default = -1)
    {
        if (preg_match("#^([0-9\.,]+)$#", $val, $match)) {
            $val = $match[0];
            if (strstr($val, ",")) {
                $val = str_replace(".", "", $val);
                $val = str_replace(",", ".", $val);
            }
            $val = floatval($val);

            return $val;
        }

        return $default;
    }
}
