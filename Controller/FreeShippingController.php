<?php

namespace DpdClassic\Controller;

use DpdClassic\DpdClassic;
use DpdClassic\Form\FreeShippingForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Tools\URL;

/**
 * Class FreeShippingController
 * @package DpdClassic\Controller
 * @author Thelia <info@thelia.net>
 */
class FreeShippingController extends BaseAdminController
{
    public function changeFreeShippingAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ["dpdclassic"], AccessManager::UPDATE)) {
            return $response;
        }

        $form = new FreeShippingForm($this->getRequest());

        try {
            $vform = $this->validateForm($form);
            $data = $vform->get('freeshipping')->getData();

            DpdClassic::setConfigValue('freeshipping', ($data) ? true : false);

            $response = Response::create('');
        } catch (\Exception $e) {
            $response = JsonResponse::create(array("error"=>$e->getMessage()), 500);
        }

        return $response;
    }

    public function amountAction()
    {
        $form = $this->createForm('freeshipping_amount_form');

        try {
            $vform = $this->validateForm($form);
            $data = (float) $vform->get('amount')->getData();

            DpdClassic::setFreeShippingAmount($data);
        } catch (\Exception $e) {
            $this->setupFormErrorContext(
                "Setting free shipping amount",
                $e->getMessage(),
                $form,
                $e
            );
        }

        return $this->generateRedirect(
            URL::getInstance()->absoluteUrl('/admin/module/DpdClassic', ['current_tab' => 'prices_slices_tab'])
        );
    }
}
