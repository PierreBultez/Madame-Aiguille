<?php
declare(strict_types=1);

namespace MadameAiguille\Theme\Block\Home;

use MadameAiguille\Theme\Model\Home\Events as EventsModel;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\View\Element\Template;

/** Rendez-vous de l'accueil : la page entière porte le tag, purgé à l'enregistrement et chaque nuit. */
class Events extends Template implements IdentityInterface
{
    public function getIdentities(): array
    {
        return [EventsModel::CACHE_TAG];
    }
}
