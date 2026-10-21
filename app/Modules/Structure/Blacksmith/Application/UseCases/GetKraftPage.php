<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\ItemAssembly\Application\Services\AssemblyCatalog;
use App\Modules\ItemAssembly\Domain\Enums\AssemblyType;
use App\Modules\Structure\Blacksmith\Application\DTOs\KraftPageDTO;
use App\Modules\Structure\Blacksmith\Application\Mappers\KraftPageViewMapper;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetKraftPage
{
    public function __construct(
        private readonly BlacksmithReadRepository $readRepository,
        private readonly KraftPageViewMapper $mapper,
        private readonly AssemblyCatalog $assemblyCatalog,
    ) {}

    public function execute(User $user, int $blacksmithId): KraftPageDTO
    {
        $catalog = $this->assemblyCatalog->execute($user, AssemblyType::ARTIFACT);

        return $this->mapper->map(
            blacksmith: $this->readRepository->findStructureOrFail($blacksmithId),
            recipes: $this->readRepository->getCraftRecipes($user),
            resources: $this->readRepository->getResourceCounts($user),
            assemblies: $catalog['assemblies'],
            assemblyItems: $catalog['items'],
        );
    }
}
