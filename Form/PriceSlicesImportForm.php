<?php

namespace DpdClassic\Form;

use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints;
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
                    'label' => 'Import price slices from JSON file',
                    'required' => true,
                    'constraints' => [
                        new Constraints\NotBlank(),
                        new Constraints\File(['mimeTypes' => ['application/json']])
                    ]
                ]
            );
    }
}