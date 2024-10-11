<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Security\Role;
use App\Repository\Security\Interface\MenuRepository;
use App\Repository\Security\Interface\ModuleRepository;
use App\Repository\Security\Interface\PermissionRepository;

final class RoleGetOneProvider implements ProviderInterface
{
    public function __construct(private readonly ModuleRepository $moduleRepository,
    private readonly MenuRepository $menuRepository, private readonly PermissionRepository $permissionRepository
    )
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof CollectionOperationInterface) {
            return [new Role(), new Role()];
        }
        $book = $this->itemProvider->provide($operation, $uriVariables, $context);
        dd($book);

        return new Role($uriVariables);
    }

}