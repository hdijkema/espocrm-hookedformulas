<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2020 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: https://www.espocrm.com
 *
 * EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\EntityGroup;

use Espo\Core\Exceptions\Error;

class GetManyType extends \Espo\Core\Formula\Functions\Base
{
    protected function init()
    {
        $this->addDependency('entityManager');
        $this->addDependency('selectManagerFactory');
        $this->addDependency('metadata');
    }

    public function process(\StdClass $item)
    {
        $args = $this->fetchArguments($item);

        if (count($args) < 2) {
             throw new Error("Formula entity\\getMany: Too few arguments.");
        }

        $entityManager = $this->getInjection('entityManager');

        $entity = $args[0];
        $limit = $args[1];

        $orderBy = null;
        $order = null;

        if (count($args) > 2) { $orderBy = $args[2]; }
        if (count($args) > 3) { $order = $args[3]; }

        if (!is_int($limit)) throw new Error("Formula entity\\GetRelated: limit should be int.");

        $metadata = $this->getInjection('metadata');

        if (!$orderBy) {
            $orderBy = $metadata->get(['entityDefs', $entityType, 'collection', 'orderBy']);
            if (is_null($order)) {
                $order = $metadata->get(['entityDefs', $entityType, 'collection', 'order']) ?? 'asc';
            }
        } else {
            $order = $order ?? 'asc';
        }

        $selectManager = $this->getInjection('selectManagerFactory')->create($entity);
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

        $collection = $entityManager->getRepository($entity)->select(['id'])->find($selectParams);

        $entities = [];   
        foreach ($collection as $e) {
            $entities[] = $entityManager->getEntity($entity, $e->id);
        }
        return $entities;
    }
}
