<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia                                                                       */
/*                                                                                   */
/*      Copyright (c) OpenStudio                                                     */
/*      email : info@thelia.net                                                      */
/*      web : http://www.thelia.net                                                  */
/*                                                                                   */
/*      This program is free software; you can redistribute it and/or modify         */
/*      it under the terms of the GNU General Public License as published by         */
/*      the Free Software Foundation; either version 3 of the License                */
/*                                                                                   */
/*      This program is distributed in the hope that it will be useful,              */
/*      but WITHOUT ANY WARRANTY; without even the implied warranty of               */
/*      MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                */
/*      GNU General Public License for more details.                                 */
/*                                                                                   */
/*      You should have received a copy of the GNU General Public License            */
/*      along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace DpdClassic\Form;

use DpdClassic\DpdClassic;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Form\BaseForm;
use Thelia\Core\Translation\Translator;
use Thelia\Model\OrderQuery;

/**
 * Class ExportForm
 * @package DpdClassic\Form
 * @author Etienne Perriere <eperriere@openstudio.fr>
 */
class ExportForm extends BaseForm
{
    public function getName()
    {
        return "export_form";
    }

    protected function buildForm()
    {
        if (null === $data = DpdClassic::getConfigValue('default_status')) {
            $data = DpdClassic::NO_CHANGE;
        }

        $entries = OrderQuery::create()
            ->filterByDeliveryModuleId(DpdClassic::getModuleId())
            ->filterByStatusId([DpdClassic::STATUS_PAID, DpdClassic::STATUS_PROCESSING])
            ->orderByCreatedAt(Criteria::DESC)
            ->find();

        $this->formBuilder
            ->add(
                'new_status_id',
                'choice',
                array(
                    'label' => Translator::getInstance()->trans('Change order status to', [], DpdClassic::DOMAIN_NAME),
                    'choices' => array(
                        DpdClassic::NO_CHANGE => $this->translator->trans("Do not change", [], DpdClassic::DOMAIN_NAME),
                        DpdClassic::PROCESS => $this->translator->trans(
                            "Set orders status as processing",
                            [],
                            DpdClassic::DOMAIN_NAME
                        ),
                        DpdClassic::SEND => $this->translator->trans(
                            "Set orders status as sent",
                            [],
                            DpdClassic::DOMAIN_NAME
                        )
                    ),
                    'required' => true,
                    'expanded' => true,
                    'multiple' => false,
                    'data' => $data
                )
            );

        foreach ($entries as $order) {
            $orderRef = str_replace(".", "-", $order->getRef());

            $this->formBuilder
                ->add(
                    $orderRef,
                    'checkbox',
                    array(
                        'label' => $orderRef,
                        'label_attr' => array(
                            'for' => $orderRef
                        )
                    )
                )
                ->add(
                    $orderRef . "-assur",
                    'checkbox'
                )
                ->add(
                    $orderRef . "-pkgNumber",
                    'number'
                )
                ->add(
                    $orderRef . "-pkgWeight",
                    'number'
                );
        }
    }
}
