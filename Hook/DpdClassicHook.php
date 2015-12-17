<?php

namespace DpdClassic\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

/**
 * Class DpdClassicHook
 * @package DpdClassic\Hook
 * @author Etienne Perriere <eperriere@openstudio.fr>
 */
class DpdClassicHook extends BaseHook
{
    public function onModuleConfig(HookRenderEvent $event)
    {
        $event->add($this->render('dpdclassic-configuration.html'));
    }

    public function onModuleConfigJs(HookRenderEvent $event)
    {
        die('TAZAZAZAZAZAZAZAZAZAZA');
        $event->add($this->render('dpdclassic-config-js.html'));
    }
}