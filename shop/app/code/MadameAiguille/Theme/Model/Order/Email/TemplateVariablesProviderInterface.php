<?php
/**
 * Variables supplémentaires des notifications de statut, fournies par d'autres modules
 * (ex. créneau et lieu de retrait, MadameAiguille_Checkout) et déclarées dans leur di.xml.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Model\Order\Email;

use Magento\Sales\Model\Order;

interface TemplateVariablesProviderInterface
{
    /**
     * @return array<string, mixed>
     */
    public function getVariables(Order $order): array;
}
