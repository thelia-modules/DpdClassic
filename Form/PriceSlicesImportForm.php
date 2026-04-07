<?php

namespace DpdClassic\Form;

use DpdClassic\DpdClassic;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;

class PriceSlicesImportForm extends BaseForm
{

    protected function buildForm(): void
    {
        $this->formBuilder
            ->add(
                "import_file",
                FileType::class,
                [
                    'label' => Translator::getInstance()->trans(
                        'Import price slices from JSON file',
                        [],
                        DpdClassic::DOMAIN_NAME
                    ),
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\File(['mimeTypes' => ['application/json']])
                    ]
                ]
            );
    }
}