<?php
/**
 * Created by PhpStorm.
 * User: nicolasbarbey
 * Date: 28/09/2020
 * Time: 11:48
 */

namespace DpdClassic\Smarty\Plugins;


use DpdClassic\Model\DpdclassicLabelsQuery;
use TheliaSmarty\Template\AbstractSmartyPlugin;
use TheliaSmarty\Template\SmartyPluginDescriptor;

class DpdClassicLabelNumber extends AbstractSmartyPlugin
{
    public function getPluginDescriptors()
    {
        return [
            new SmartyPluginDescriptor('function', 'DpdClassicLabelNumber', $this, 'dpdClassicLabelNumber'),
        ];
    }

    /**
     * @param $params
     * @param $smarty
     */
    public function dpdClassicLabelNumber($params, $smarty)
    {
        $orderId = $params["order_id"];

        $labelNumber = DpdclassicLabelsQuery::create()->filterByOrderId($orderId)->findOne();

        $smarty->assign('labelNbr', $labelNumber ? $labelNumber->getLabelNumber() : null);
    }
}