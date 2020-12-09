<?php

declare(strict_types=1);

namespace Mvo\ContaoTooling\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Mvo\ContaoTooling\MvoContaoToolingBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(MvoContaoToolingBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
