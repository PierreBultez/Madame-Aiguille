<?php
/**
 * Madame Aiguille — module d'accompagnement du thème
 * (ViewModels, attributs catalogue, routes utilitaires).
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'MadameAiguille_Theme', __DIR__);
