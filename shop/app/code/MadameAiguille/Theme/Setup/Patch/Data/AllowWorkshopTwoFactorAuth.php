<?php
/**
 * Le rôle « Atelier » doit avoir le droit « Two Factor Auth » (Magento_TwoFactorAuth::tfa) : sans lui, Céline ne
 * peut ni configurer ni valider sa double authentification et reste bloquée à la connexion (« vous devez disposer
 * des autorisations nécessaires »). Relevé le 02/11/2026 avec un compte de test « Atelier ».
 *
 * Ajoute ce droit et ses parents aux droits existants du rôle ; ceux des utilisateurs et des rôles restent refusés.
 */

declare(strict_types=1);

namespace MadameAiguille\Theme\Setup\Patch\Data;

use Magento\Authorization\Model\Acl\Role\Group;
use Magento\Authorization\Model\ResourceModel\Role\CollectionFactory as RoleCollectionFactory;
use Magento\Authorization\Model\ResourceModel\Rules\CollectionFactory as RulesCollectionFactory;
use Magento\Authorization\Model\RulesFactory;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AllowWorkshopTwoFactorAuth implements DataPatchInterface
{
    public const RESOURCES = [
        'Magento_Backend::system',
        'Magento_User::acl',
        'Magento_TwoFactorAuth::tfa',
    ];

    public function __construct(
        private readonly RoleCollectionFactory $roleCollectionFactory,
        private readonly RulesCollectionFactory $rulesCollectionFactory,
        private readonly RulesFactory $rulesFactory
    ) {
    }

    public function apply(): self
    {
        $role = $this->roleCollectionFactory->create()
            ->addFieldToFilter('role_name', CreateWorkshopRole::ROLE_NAME)
            ->addFieldToFilter('role_type', Group::ROLE_TYPE)
            ->getFirstItem();
        if (!$role->getId()) {
            return $this;
        }

        $allowed = [];
        foreach ($this->rulesCollectionFactory->create()->getByRoles((int) $role->getId()) as $rule) {
            if ($rule->getPermission() === 'allow') {
                $allowed[] = (string) $rule->getResourceId();
            }
        }

        $this->rulesFactory->create()
            ->setRoleId($role->getId())
            ->setResources(array_values(array_unique([...$allowed, ...self::RESOURCES])))
            ->saveRel();

        return $this;
    }

    public static function getDependencies(): array
    {
        return [CreateWorkshopRole::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
