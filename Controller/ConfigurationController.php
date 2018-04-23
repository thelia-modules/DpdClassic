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
        if (null !== $response =
                $this->checkAuth(
                    [AdminResources::MODULE],
                    ['DpdClassic'],
                    [AccessManager::CREATE, AccessManager::UPDATE]
                )
        ) {
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
            $errorMessage = $this->getTranslator()->trans(
                'Sorry, an error occurred: %err',
                ['%err' => $ex->getMessage()],
                DpdClassic::DOMAIN_NAME
            );
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
}
