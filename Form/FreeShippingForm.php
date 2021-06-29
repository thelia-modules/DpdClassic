<?php

namespace DpdClassic\Form;

use DpdClassic\DpdClassic;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

/**
 * Class FreeShippingForm
 * @package DpdClassic\Form
 * @author Etienne Perriere <eperriere@openstudio.fr>
 */
class FreeShippingForm extends BaseForm
{
    public static function getName()
    {
        return "freeshipping_form";
    }

    /**
     * @return null
     */
    protected function buildForm()
    {
        $freeShipping = DpdClassic::getConfigValue('freeshipping');

        $this->formBuilder
            ->add(
                "freeshipping",
                CheckboxType::class,
                [
                    'data' => (bool) $freeShipping,
                    'label' => Translator::getInstance()->trans("Activate free shipping: ", [], DpdClassic::DOMAIN_NAME)
                ]
            );
    }
}
