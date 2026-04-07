<?php

namespace DpdClassic\Controller;


use DpdClassic\DpdClassic;
use DpdClassic\Form\PriceSlicesImportForm;
use Exception;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Thelia\Controller\Admin\AdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\Template\ParserContext;
use Thelia\Form\Exception\FormValidationException;

#[Route('/admin/module/DpdClassic/price-slices', name: 'DpdClassic_price-slices')]
class PriceSlicesImportExportController extends AdminController
{
    #[Route('/export', name: 'export')]
    public function exportData(): BinaryFileResponse
    {
        $filePath = __DIR__ ."/../". DpdClassic::JSON_PRICE_RESOURCE;

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'prices.json'
        );

        return $response;
    }

    #[Route('/import', name: 'import')]
    public function importData(ParserContext $parserContext): RedirectResponse|Response
    {
        $form = $this->createForm(PriceSlicesImportForm::getName());

        try {
            $data = $this->validateForm($form)->getData();

            if (empty($data['import_file'])) {
                throw new Exception("No data to import");
            }

            $uploadedFile = $data['import_file'];
            $jsonContent = file_get_contents($uploadedFile->getPathname());
            $jsonData = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("Invalid JSON file");
            }

            file_put_contents(__DIR__ ."/../". DpdClassic::JSON_PRICE_RESOURCE, json_encode($jsonData));

            return $this->generateSuccessRedirect($form);
        } catch (FormValidationException $e) {
            $error_message = $this->createStandardFormValidationErrorMessage($e);
        } catch (Exception $e) {
            $error_message = $e->getMessage();
        }

        $form->setErrorMessage($error_message);

        $parserContext
            ->addForm($form)
            ->setGeneralError($error_message);

        return $this->generateErrorRedirect($form);
    }
}