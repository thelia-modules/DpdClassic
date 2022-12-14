<?php

namespace DpdClassic\Controller;

use DpdClassic\DpdClassic;
use DpdClassic\Form\FreeShippingAmountForm;
use DpdClassic\Form\FreeShippingForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Tools\URL;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/module/DpdClassic", name="DpdClassic")
 * Class FreeShippingController
 * @package DpdClassic\Controller
 * @author Thelia <info@thelia.net>
 */
class FreeShippingController extends BaseAdminController
{
    /**
     * @Route("/freeshipping", name="_freeshipping", methods="POST")
     */
    public function changeFreeShippingAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ["dpdclassic"], AccessManager::UPDATE)) {
            return $response;
        }

        $form = $this->createForm(FreeShippingForm::getName());

        try {
            $vform = $this->validateForm($form);
            $data = $vform->get('freeshipping')->getData();

            DpdClassic::setConfigValue('freeshipping', ($data) ? true : false);

            $response = new Response();
        } catch (\Exception $e) {
            $response = new JsonResponse(array("error"=>$e->getMessage()), 500);
        }

        return $response;
    }

    /**
     * @Route("/freeshipping_amount", name="_freeshipping_amount", methods="POST")
     */
    public function amountAction()
    {
        $form = $this->createForm(FreeShippingAmountForm::getName());

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
