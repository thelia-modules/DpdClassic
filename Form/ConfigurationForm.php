<?php

namespace DpdClassic\Form;

use DpdClassic\DpdClassic;
use Thelia\Form\BaseForm;

/**
 * Class ConfigurationForm
 * @package DpdClassic\Form
 * @author Etienne Perriere <eperriere@openstudio.fr>
 */
class ConfigurationForm extends BaseForm
{
    public function getName()
    {
        return "config_form";
    }

    /**
     * @return null
     */
    protected function buildForm()
    {
        if (null === $data = DpdClassic::getConfigValue('default_status')){
            $data = DpdClassic::NO_CHANGE;
        }

        $this->formBuilder
            ->add(
                'default_status',
                'choice',
                [
                    'label' => $this->translator->trans('Change order status to', [], DpdClassic::DOMAIN_NAME),
                    'choices' => [
                        DpdClassic::NO_CHANGE => $this->translator->trans("Do not change", [], DpdClassic::DOMAIN_NAME),
                        DpdClassic::PROCESS => $this->translator->trans("Set orders status as processing", [], DpdClassic::DOMAIN_NAME),
                        DpdClassic::SEND => $this->translator->trans("Set orders status as sent", [], DpdClassic::DOMAIN_NAME)
                    ],
                    'required' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'data' => $data
                ]
            );
    }
}