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

use Thelia\Core\Template\Loop\Delivery;
use Thelia\Core\Template\Element\LoopResult;
use DpdClassic\DpdClassic;

/**
 * Class DpdClassicDelivery
 * @package DpdClassic\Loop
 * @author Thelia <info@thelia.net>
 */
class DpdClassicDelivery extends Delivery
{
    public function parseResults(LoopResult $loopResult)
    {
        $moduleId = DpdClassic::getModuleId();

        $loopResult = parent::parseResults($loopResult);
        for ($loopResult->rewind(); $loopResult->valid(); $loopResult->next()) {
            $loopResult->current()->set("MODULE_ID", $moduleId);
        }

        return $loopResult;
    }
}
