<?php

namespace DpdClassic\Controller;

use DpdClassic\DpdClassic;
use DpdClassic\Form\TaxRuleForm;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
use Thelia\Form\Exception\FormValidationException;
use Thelia\Tools\URL;

#[Route('/admin/module/DpdClassic/tax_rule', name: 'dpd_classic_tax_rule_')]
class TaxRuleController extends BaseAdminController
{
    #[Route('/save', name: 'save')]
    public function saveTaxRule()
    {
        if (null !== $response = $this->checkAuth(AdminResources::MODULE, DpdClassic::DOMAIN_NAME, AccessManager::UPDATE)) {
            return $response;
        }

        $taxRuleForm = $this->createForm(TaxRuleForm::getName());

        $message = false;

        $url = '/admin/module/DpdClassic';

        try {
            $form = $this->validateForm($taxRuleForm);

            // Get the form field values
            $data = $form->getData();

            DpdClassic::setConfigValue(DpdClassic::DPD_CLASSIC_TAX_RULE_ID, $data["tax_rule_id"]);

        } catch (FormValidationException $ex) {
            $message = $this->createStandardFormValidationErrorMessage($ex);
        } catch (\Exception $ex) {
            $message = $ex->getMessage();
        }

        if ($message !== false) {
            $this->setupFormErrorContext(
                Translator::getInstance()->trans('Error', [], DpdClassic::DOMAIN_NAME),
                $message,
                $taxRuleForm,
                $ex
            );
        }

        return $this->generateRedirect(URL::getInstance()->absoluteUrl($url, [ 'current_tab' => 'tax_rule']));
    }
}