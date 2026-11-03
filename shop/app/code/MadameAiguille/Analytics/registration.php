<?php
/**
 * Madame Aiguille — mesure d'audience Matomo auto-hébergée (lot 9).
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'MadameAiguille_Analytics', __DIR__);
