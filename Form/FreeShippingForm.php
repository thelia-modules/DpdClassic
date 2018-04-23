<?php

namespace DpdClassic\Form;

use DpdClassic\DpdClassic;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

/**
 * Class FreeShippingForm
 * @package DpdClassic\Form
 * @author Etienne Perriere <eperriere@openstudio.fr>
 */
class FreeShippingForm extends BaseForm
{
    public function getName()
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
                "checkbox",
                [
                    'data' => boolval($freeShipping),
                    'label' => Translator::getInstance()->trans("Activate free shipping: ", [], DpdClassic::DOMAIN_NAME)
                ]
            );
    }
}
