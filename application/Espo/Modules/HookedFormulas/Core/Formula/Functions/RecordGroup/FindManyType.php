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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\RecordGroup;

use Espo\Core\Exceptions\Error;

class FindManyType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\SelectBase
{

    protected function processEvaluated(\stdClass $item): mixed
    {
        if (!property_exists($item, 'value')) {
            throw new Error();
        }

        if (!is_array($item->value)) {
            throw new Error();
        }

        if (count($item->value) < 3) {
            throw new Error();
        }

        $entityType = $this->evaluate($item->value[0]);
        $orderBy = $this->evaluate($item->value[1]);
        $order = $this->evaluate($item->value[2]) ?? 'asc';

        $selectManager = $this->selectManagerFactory->create($entityType);
        $selectParams = $selectManager->getEmptySelectParams();

        if (count($item->value) <= 4) {
            $filter = null;
            if (count($item->value) == 4) {
                $filter = $this->evaluate($item->value[3]);
            }
            if ($filter) {
                if (!is_string($filter)) throw new Error("Formula record\\findOne: Bad filter.");
                $selectManager->applyFilter($filter, $selectParams);
            }
        } else {
            $whereClause = [];
            $i = 3;
            while ($i < count($item->value) - 1) {
                $key = $this->evaluate($item->value[$i]);
                $value = $this->evaluate($item->value[$i + 1]);
                if ($key == 'limit by') {
                    $selectParams['limit'] = $value + 0;
                } else {
                    $whereClause[] = [$key => $value];
                }
                $i = $i + 2;
            }
            $selectParams['whereClause'] = $whereClause;
        }

        if ($orderBy) {
            $selectManager->applyOrder($orderBy, $order, $selectParams);
        }

        $e = $this->createSelectBuilderFromParams($entityType, $selectParams, ['id'])->find();

        $ids = array();
        foreach($e as $elem) {
            array_push($ids, $elem->getId());
        }

        return $ids;
    }
}
