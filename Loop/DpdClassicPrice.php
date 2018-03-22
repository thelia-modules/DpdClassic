<?php
/*************************************************************************************/
/*                                                                                   */
/*      Thelia	                                                                     */
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
/*	    along with this program. If not, see <http://www.gnu.org/licenses/>.         */
/*                                                                                   */
/*************************************************************************************/

namespace DpdClassic\Loop;

use DpdClassic\Model\DpdclassicPrice as DpdclassicPriceModel;
use Thelia\Core\Template\Element\PropelSearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Template\Loop\Argument\Argument;
use DpdClassic\Model\DpdclassicPriceQuery;

/**
 * Class DpdClassicPrice
 * @package DpdClassic\Loop
 * @author Thelia <info@thelia.net>
 * @original_author Etienne Roudeix <eroudeix@openstudio.fr>
 * @contributor Etienne Perriere <eperriere@openstudio.fr>
 */
class DpdClassicPrice extends BaseLoop implements PropelSearchLoopInterface //ArraySearchLoopInterface
{
    /**
     * @return ArgumentCollection
     */
    protected function getArgDefinitions()
    {
        return new ArgumentCollection(
            Argument::createIntTypeArgument('area', null, true)
        );
    }

    public function buildModelCriteria()
    {
        $area = $this->getArea();

        $areaPrices = DpdclassicPriceQuery::create()
            ->filterByAreaId($area)
            ->orderByWeight();

        return $areaPrices;
    }

    public function parseResults(LoopResult $loopResult)
    {
        /** @var DpdclassicPriceModel $price */
        foreach ($loopResult->getResultDataCollection() as $price) {
            $loopResultRow = new LoopResultRow();
            $loopResultRow
                ->set("SLICE_ID", $price->getId())
                ->set("MAX_WEIGHT", $price->getWeight())
                ->set("PRICE", $price->getPrice());

            $loopResult->addRow($loopResultRow);
        }
        return $loopResult;
    }
}
