<?php
# vi: set sw=4 ts=4:
/************************************************************************
 * This file is part of HookedFormulas.
 *
 * HookedFormulas - Extension to the Open Source EspoCRM application.
 * Copyright (C) 2020 Hans Dijkema
 * Website: https://github.com/hdijkema/espocrm-hookedformulas
 *
 * HookedFormulas is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * HookedFormulas is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM or espocrm-hookedformulas.
 * If not, see http://www.gnu.org/licenses/.
 *
 ************************************************************************/

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
