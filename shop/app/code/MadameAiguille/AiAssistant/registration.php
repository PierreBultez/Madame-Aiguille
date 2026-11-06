<?php
/**
 * Madame Aiguille — assistant IA des fiches produits, dans l'administration seulement (lot 10).
 */

declare(strict_types=1);

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(ComponentRegistrar::MODULE, 'MadameAiguille_AiAssistant', __DIR__);
