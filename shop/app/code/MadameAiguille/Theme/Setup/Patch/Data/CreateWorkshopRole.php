<?php
/**
 * Rôle d'administration « Atelier », pour Céline (lot 8) : ventes, catalogue, clients sans actions sensibles,
 * contenu, code promo, modèles d'emails, abonnés, stock bas, section Madame Aiguille et case des CGV.
 * Ni le reste de la configuration, ni les modules, thèmes, utilisateurs ou outils système.
 *
 * Les branches sont développées depuis l'arbre des droits installé ; les parents d'un droit accordé le sont aussi,
 * sans quoi le menu ne s'affiche pas. Le compte de Céline est créé à part, par Pierre. Rôle existant : rien à faire.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\RoleFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Acl\AclResource\ProviderInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreateWorkshopRole implements DataPatchInterface
{
    public const ROLE_NAME = 'Atelier';

    /** Droits accordés avec toute leur branche. */
    public const BRANCHES = [
        'Magento_Backend::dashboard',
        'Magento_Sales::sales',
        'Magento_Catalog::catalog',
        'Magento_Cms::block',
        'Magento_Cms::media_gallery',
        'Magento_SalesRule::quote',
        'Magento_Email::template',
        'Magento_Newsletter::subscriber',
        'Magento_Reports::lowstock',
        'MadameAiguille_Theme::config',
        'Magento_CheckoutAgreements::checkoutagreement',
        'Magento_Backend::myaccount',
        'Magento_Backend::global_search',
    ];

    /** Droits accordés seuls : fiches clientes sans suppression ni mot de passe, pages sans suppression ni design. */
    public const SINGLE = [
        'Magento_Customer::manage',
        'Magento_Customer::online',
        'Magento_Cms::page',
        'Magento_Cms::save',
    ];

    /** Retirés des branches accordées. */
    public const EXCLUDED = [
        'Magento_Catalog::edit_product_design',
        'Magento_Catalog::edit_category_design',
    ];

    public function __construct(
        private readonly RoleFactory $roleFactory,
        private readonly RoleCollectionFactory $roleCollectionFactory,
        private readonly RulesFactory $rulesFactory,
        private readonly ProviderInterface $aclResourceProvider
    ) {
    }

    public function apply(): self
    {
        $existing = $this->roleCollectionFactory->create()
            ->addFieldToFilter('role_name', self::ROLE_NAME)
            ->addFieldToFilter('role_type', Group::ROLE_TYPE);
        if ($existing->getSize() > 0) {
            return $this;
        }

        $role = $this->roleFactory->create()
            ->setRoleName(self::ROLE_NAME)
            ->setUserType((string) UserContextInterface::USER_TYPE_ADMIN)
            ->setUserId(0)
            ->setRoleType(Group::ROLE_TYPE)
            ->setParentId(0)
            ->setTreeLevel(1)
            ->save();

        $this->rulesFactory->create()
            ->setRoleId($role->getId())
            ->setResources(self::allowedResources($this->aclResourceProvider->getAclResources()))
            ->saveRel();

        return $this;
    }

    /**
     * @param array<int, array{id: string, children?: array}> $tree arbre fourni par ProviderInterface
     * @return list<string>
     */
    public static function allowedResources(array $tree): array
    {
        $allowed = [];
        $walk = static function (array $nodes, array $ancestors, bool $inBranch) use (&$walk, &$allowed): void {
            foreach ($nodes as $node) {
                $id = (string) ($node['id'] ?? '');
                if ($id === '' || in_array($id, self::EXCLUDED, true)) {
                    continue;
                }
                $granted = $inBranch || in_array($id, self::BRANCHES, true);
                if ($granted || in_array($id, self::SINGLE, true)) {
                    foreach ([...$ancestors, $id] as $resource) {
                        $allowed[$resource] = true;
                    }
                }
                $walk($node['children'] ?? [], [...$ancestors, $id], $granted);
            }
        };
        $walk($tree, [], false);

        unset($allowed['Magento_Backend::all']);

        return array_keys($allowed);
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function getAliases(): array
    {
        return [];
    }
}
