<?php

namespace Basilicom\AdvancedCustomLayoutBundle;

use Basilicom\AdvancedCustomLayoutBundle\DependencyInjection\AdvancedCustomLayoutExtension;
use Pimcore\Extension\Bundle\AbstractPimcoreBundle;

class AdvancedCustomLayoutBundle extends AbstractPimcoreBundle
{
    public function getContainerExtension(): ?AdvancedCustomLayoutExtension
    {
        return new AdvancedCustomLayoutExtension();
    }
}
