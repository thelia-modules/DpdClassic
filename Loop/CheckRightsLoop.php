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

namespace DpdClassic\Loop;

use DpdClassic\DpdClassic;
use Thelia\Core\Template\Element\ArraySearchLoopInterface;
use Thelia\Core\Template\Element\BaseLoop;
use Thelia\Core\Template\Element\LoopResult;
use Thelia\Core\Template\Element\LoopResultRow;
use Thelia\Core\Template\Loop\Argument\ArgumentCollection;
use Thelia\Core\Translation\Translator;

/**
 * Class CheckRightsLoop
 * @package DpdClassic\Looop
 * @author Thelia <info@thelia.net>
 * @contributor Etienne Perriere <eperriere@openstudio.fr>
 */

class CheckRightsLoop extends BaseLoop implements ArraySearchLoopInterface
{
    protected function getArgDefinitions()
    {
        return new ArgumentCollection();
    }

    public function buildArray()
    {
        $ret = array();
        $dir = __DIR__."/../Config/";
        if (!is_readable($dir)) {
            $ret[] = array("ERRMES"=>Translator::getInstance()->trans(
                "Can't read Config directory",
                [],
                DpdClassic::DOMAIN_NAME
            ), "ERRFILE"=>"");
        }
        if (!is_writable($dir)) {
            $ret[] = array("ERRMES"=>Translator::getInstance()->trans(
                "Can't write Config directory",
                [],
                DpdClassic::DOMAIN_NAME
            ), "ERRFILE"=>"");
        }
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                if (strlen($file) > 5 && substr($file, -5) === ".json") {
                    if (!is_readable($dir.$file)) {
                        $ret[] = array("ERRMES"=>Translator::getInstance()->trans(
                            "Can't read file",
                            [],
                            DpdClassic::DOMAIN_NAME
                        ), "ERRFILE"=>"DpdClassic/Config/".$file);
                    }
                    if (!is_writable($dir.$file)) {
                        $ret[] = array("ERRMES"=>Translator::getInstance()->trans(
                            "Can't write file",
                            [],
                            DpdClassic::DOMAIN_NAME
                        ), "ERRFILE"=>"DpdClassic/Config/".$file);
                    }
                }
            }
        }

        return $ret;
    }
    public function parseResults(LoopResult $loopResult)
    {
        foreach ($loopResult->getResultDataCollection() as $arr) {
            $loopResultRow = new LoopResultRow();
            $loopResultRow->set("ERRMES", $arr["ERRMES"])
                ->set("ERRFILE", $arr["ERRFILE"]);
            $loopResult->addRow($loopResultRow);
        }

        return $loopResult;
    }
}
