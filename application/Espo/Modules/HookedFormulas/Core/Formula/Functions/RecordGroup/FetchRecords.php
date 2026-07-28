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

abstract class FetchRecords extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\MetadataSelectBase
{

    protected function fetchRecs(\StdClass $item)
    {
        if (!property_exists($item, 'value')) {
            throw new Error();
        }

        if (!is_array($item->value)) {
            throw new Error();
        }

        if (count($item->value) < 4) {
            throw new Error();
        }

        $entityType = $this->evaluate($item->value[0]);
        $items = $this->evaluate($item->value[1]);
        $orderBy = $this->evaluate($item->value[2]);
        $order = $this->evaluate($item->value[3]) ?? 'asc';

        $selectManager = $this->selectManagerFactory->create($entityType);
        $selectParams = $selectManager->getEmptySelectParams();

        $whereClause = [];
        $i = 4;
        while ($i < count($item->value) - 1) {
            $key = $this->evaluate($item->value[$i]);
            $value = $this->evaluate($item->value[$i + 1]);
            if ($key == 'limit by') {
                $selectParams['limit'] = $value + 0;
            } else {
                if ($key == 'use filter') {
                    $filter = $value;
                    if ($filter) {
                        if (!is_string($filter)) throw new Error("Formula record\\findOne: Bad filter.");
                        $selectManager->applyFilter($filter, $selectParams);
                    }
                } else {
                    $whereClause[] = [$key => $value];
                }
            }
            $i = $i + 2;
        }
        $selectParams['whereClause'] = $whereClause;

        if ($orderBy) {
            $selectManager->applyOrder($orderBy, $order, $selectParams);
        }

        $items = array_map("trim", explode(',', $items));
        $metadata = $this->metadata;

        // does an item represent a field with more fields?
        // and are these fields not present in $items?
        // Then we also fetch these, because otherwise, e.g. the $name field
        // will not be completely filled. 
        $extend_items = [];
        foreach($items as $item) {
           $itemType = $metadata->get(['entityDefs', $entityType, 'fields', $item, 'type']);
           $actual_fields = $metadata->get(['fields', $itemType, 'actualFields']);
           if ($actual_fields) {
              foreach($actual_fields as $field) {
                 $field = $field . ucfirst($item);
                 if ($metadata->get(['entityDefs', $entityType, 'fields', $field])) {
                     if (!in_array($field, $items) && !in_array($field, $extend_items)) {
                         array_push($extend_items, $field);
                     }
                 }
              }
           }
        }
        $items = array_merge($items, $extend_items);

        $e = $this->createSelectBuilderFromParams($entityType, $selectParams, $items)->find();

        $results = [ 'items' => $items, 'elements' => $e ];

        return (object) $results;
    }
}
