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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\EntityGroup;

use Espo\Core\Exceptions\Error;

class GetManyType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\MetadataSelectBase
{

    protected function processEvaluated(\stdClass $item): mixed
    {
        $args = $this->fetchArguments($item);

        if (count($args) < 2) {
             throw new Error("Formula entity\\getMany: Too few arguments.");
        }

        $entityManager = $this->entityManager;

        $entity = $args[0];
        $limit = $args[1];

        $orderBy = null;
        $order = null;

        if (count($args) > 2) { $orderBy = $args[2]; }
        if (count($args) > 3) { $order = $args[3]; }

        if (!is_int($limit)) throw new Error("Formula entity\\GetRelated: limit should be int.");

        $metadata = $this->metadata;

        if (!$orderBy) {
            $orderBy = $metadata->get(['entityDefs', $entity, 'collection', 'orderBy']);
            if (is_null($order)) {
                $order = $metadata->get(['entityDefs', $entity, 'collection', 'order']) ?? 'asc';
            }
        } else {
            $order = $order ?? 'asc';
        }

        $selectManager = $this->selectManagerFactory->create($entity);
        $selectParams = $selectManager->getEmptySelectParams();

        if (count($args) <= 4) {
            $filter = null;
            if (count($args) == 5) {
                $filter = $args[4];
            }
            if ($filter) {
                if (!is_string($filter)) throw new Error("Formula entity\\getRelated: Bad filter.");
                $selectManager->applyFilter($filter, $selectParams);
            }
        } else {
            $i = 4;
            while ($i < count($args) - 1) {
                $key = $args[$i];
                $value = $args[$i + 1];
                $selectParams['whereClause'][] = [$key => $value];
                $i = $i + 2;
            }
        }

        $selectParams['limit'] = $limit;

        if ($orderBy) {
            $selectManager->applyOrder($orderBy, $order, $selectParams);
        }

        $collection = $this->createSelectBuilderFromParams($entity, $selectParams, ['id'])->find();

        $entities = [];   
        foreach ($collection as $e) {
            $entities[] = $entityManager->getEntity($entity, $e->getId());
        }
        return $entities;
    }
}
