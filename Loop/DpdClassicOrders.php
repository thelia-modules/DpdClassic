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

use Thelia\Core\Template\Loop\Order;
use DpdClassic\DpdClassic;
use Thelia\Model\OrderQuery;

/**
 * Class DpdClassicOrders
 * @package DpdClassic\Loop
 * @author Thelia <info@thelia.net>
 * @contributor Etienne Perriere <eperriere@openstudio.fr>
 */
class DpdClassicOrders extends Order
{
    public function buildModelCriteria()
    {
        return OrderQuery::create()
            ->filterByDeliveryModuleId(DpdClassic::getModuleId())
            ->filterByStatusId([DpdClassic::STATUS_PAID, DpdClassic::STATUS_PROCESSING]);
    }
}
