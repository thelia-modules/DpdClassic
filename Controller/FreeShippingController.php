<?php

namespace DpdClassic\Controller;

use DpdClassic\DpdClassic;
use DpdClassic\Form\FreeShippingForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;

/**
 * Class FreeShippingController
 * @package DpdClassic\Controller
 * @author Thelia <info@thelia.net>
 * @contributor Etienne Perriere <eperriere@openstudio.fr>
 */
class FreeShippingController extends BaseAdminController
{
    public function changeFreeShippingAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ["dpdclassic"], AccessManager::UPDATE)) {
            return $response;
        }

        $form = new FreeShippingForm($this->getRequest());
        $response=null;

        try {
            $vform = $this->validateForm($form);
            $data = $vform->get('freeshipping')->getData();

            DpdClassic::setConfigValue('freeshipping', ($data) ? 'true' : 'false');

            $response = Response::create('');
        } catch (\Exception $e) {
            $response = JsonResponse::create(array("error"=>$e->getMessage()), 500);
        }

        return $response;
    }
}
