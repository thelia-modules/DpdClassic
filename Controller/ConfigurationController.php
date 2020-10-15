<?php

namespace DpdClassic\Controller;

use DpdClassic\DpdClassic;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;

/**
 * Class ConfigurationController
 * @package DpdClassic\Controller
 * @author Etienne Perriere <eperriere@openstudio.fr>
 */
class ConfigurationController extends BaseAdminController
{
    public function configureAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['DpdClassic'], [AccessManager::CREATE, AccessManager::UPDATE])) {
            return $response;
        }

        $baseForm = $this->createForm("config_form");

        $errorMessage = null;

        try {
            $form = $this->validateForm($baseForm);
            $data = $form->getData();

            // Save data
            DpdClassic::setConfigValue('default_status', $data["default_status"]);

        } catch (FormValidationException $ex) {
            $errorMessage = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            $errorMessage = $this->getTranslator()->trans('Sorry, an error occurred: %err', ['%err' => $ex->getMessage()], DpdClassic::DOMAIN_NAME);
        }

        if ($errorMessage !== null) {

            $this->setupFormErrorContext(
                Translator::getInstance()->trans(
                    "Error while updating status",
                    [],
                    DpdClassic::DOMAIN_NAME
                ),
                $errorMessage,
                $baseForm
            );
        }

        return $this->generateRedirectFromRoute(
            "admin.module.configure",
            [],
            [
                'module_code' => "DpdClassic",
                'current_tab' => "config",
                '_controller' => 'Thelia\\Controller\\Admin\\ModuleController::configureAction'
            ]
        );
    }

    public function saveApiConfigAction()
    {
        if (null !== $response = $this->checkAuth([AdminResources::MODULE], ['DpdClassic'], [AccessManager::CREATE, AccessManager::UPDATE])) {
            return $response;
        }

        $baseForm = $this->createForm("dpdclassic_api_configuration");

        $errorMessage = null;

        try {
            $form = $this->validateForm($baseForm);
            $data = $form->getData();

            DpdClassic::setConfigValue(DpdClassic::API_USER_ID, $data["user_id"]);
            DpdClassic::setConfigValue(DpdClassic::API_PASSWORD, $data["password"]);
            DpdClassic::setConfigValue(DpdClassic::API_CENTER_NUMBER, $data["center_number"]);
            DpdClassic::setConfigValue(DpdClassic::API_CUSTOMER_NUMBER, $data["customer_number"]);
            DpdClassic::setConfigValue(DpdClassic::API_IS_TEST, $data["isTest"]);
            DpdClassic::setConfigValue(DpdClassic::API_SHIPPER_NAME, $data["shipper_name"]);
            DpdClassic::setConfigValue(DpdClassic::API_SHIPPER_ADDRESS1, $data["shipper_address1"]);
            DpdClassic::setConfigValue(DpdClassic::API_SHIPPER_COUNTRY, $data["shipper_country"]);
            DpdClassic::setConfigValue(DpdClassic::API_SHIPPER_CITY, $data["shipper_city"]);
            DpdClassic::setConfigValue(DpdClassic::API_SHIPPER_ZIP, $data["shipper_zip_code"]);
            DpdClassic::setConfigValue(DpdClassic::API_SHIPPER_PHONE, $data["shipper_phone"]);
            DpdClassic::setConfigValue(DpdClassic::API_SHIPPER_FAX, $data["shipper_fax"]);

        } catch (FormValidationException $ex) {
            $errorMessage = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            $errorMessage = $this->getTranslator()->trans('Sorry, an error occurred: %err', ['%err' => $ex->getMessage()], DpdClassic::DOMAIN_NAME);
        }


        if ($errorMessage !== null) {

            $this->setupFormErrorContext(
                Translator::getInstance()->trans(
                    "Error while updating api configurations",
                    [],
                    DpdClassic::DOMAIN_NAME
                ),
                $errorMessage,
                $baseForm
            );
        }

        return $this->generateRedirectFromRoute(
            "admin.module.configure",
            [],
            [
                'module_code' => "DpdClassic",
                'current_tab' => "api_config",
                '_controller' => 'Thelia\\Controller\\Admin\\ModuleController::saveApiConfigAction'
            ]
        );
    }
}