<?php

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\Base;

use Espo\ORM\EntityManager;
use Espo\ORM\Query\Select;
use Espo\ORM\Repository\RDBRepository;
use Espo\ORM\Repository\RDBSelectBuilder;
use RuntimeException;

abstract class EntityManagerBase extends EvaluatedFunction
{
    public function __construct(
        protected EntityManager $entityManager
    ) {}

    /**
     * Execute legacy SelectManager parameters through the EspoCRM 9 ORM.
     *
     * EspoCRM 9 repository find/findOne/count methods no longer accept a
     * select-parameters array.  SelectManager still produces a valid raw
     * Select query, so convert that array explicitly and clone it into the
     * repository builder.
     *
     * @param array<string, mixed> $selectParams
     * @param string[]|null $selection
     */
    protected function createSelectBuilderFromParams(
        string $entityType,
        array $selectParams,
        ?array $selection = null
    ): RDBSelectBuilder {
        if ($selection !== null) {
            $selectParams['select'] = $selection;
        }

        $repository = $this->entityManager->getRepository($entityType);

        if (!$repository instanceof RDBRepository) {
            throw new RuntimeException("Entity type '{$entityType}' does not use an RDB repository.");
        }

        return $repository->clone(Select::fromRaw($selectParams));
    }
}
