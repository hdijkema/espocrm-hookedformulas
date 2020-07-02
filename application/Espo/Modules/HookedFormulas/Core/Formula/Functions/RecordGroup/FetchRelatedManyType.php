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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\RecordGroup;

use Espo\Core\Exceptions\Error;

class FetchRelatedManyType extends \Espo\Core\Formula\Functions\Base
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

        if (count($args) < 5) {
            throw new Error("Formula record\\fetchRelatedMany: Too few arguments.");
        }

        $entityManager = $this->getInjection('entityManager');

        $entityType = $args[0];
        $id = $args[1];
        $link = $args[2];
        $items = $args[3];
        $limit = $args[4];

        $orderBy = null;
        $order = null;

        if (count($args) > 5) {
            $orderBy = $args[5];
        }
        if (count($args) > 6) {
            $order = $args[6];
        }

        if (!$entityType) throw new Error("Formula record\\fetchRelatedMany: Empty entityType.");
        if (!is_string($entityType)) throw new Error("Formula record\\fetchRelatedMany: entityType should be string.");

        if (!$id) {
            $GLOBALS['log']->warning("Formula record\\fetchRelatedMany: Empty id.");
            return [];
        }
        if (!is_string($id)) throw new Error("Formula record\\fetchRelatedMany: id should be string.");

        if (!$link) throw new Error("Formula record\\fetchRelatedMany: Empty link.");
        if (!is_string($link)) throw new Error("Formula record\\fetchRelatedMany: link should be string.");

        if (!is_int($limit)) throw new Error("Formula record\\fetchRelatedMany: limit should be int.");

        $entity = $entityManager->getEntity($entityType, $id);

        if (!$entity) {
            $GLOBALS['log']->notice("Formula record\\fetchRelatedMany: Entity {$entity} {$id} not found.");
            return [];
        }

        $metadata = $this->getInjection('metadata');

        if (!$orderBy) {
            $orderBy = $metadata->get(['entityDefs', $entityType, 'collection', 'orderBy']);
            if (is_null($order)) {
                $order = $metadata->get(['entityDefs', $entityType, 'collection', 'order']) ?? 'asc';
            }
        } else {
            $order = $order ?? 'asc';
        }

        $relationType = $entity->getRelationParam($link, 'type');

        if (in_array($relationType, ['belongsTo', 'hasOne', 'belongsToParent'])) {
            throw new Error("Formula record\\fetchRelatedMany: Not supported link type '{$relationType}'.");
        }

        $foreignEntityType = $entity->getRelationParam($link, 'entity');
        if (!$foreignEntityType) throw new Error("Formula record\\fetchRelatedMany: Bad or not supported link '{$link}'.");

        $foreignLink = $entity->getRelationParam($link, 'foreign');
        if (!$foreignLink) throw new Error("Formula record\\fetchRelatedMany: Not supported link '{$link}'.");

        $selectManager = $this->getInjection('selectManagerFactory')->create($foreignEntityType);
        $selectParams = $selectManager->getEmptySelectParams();

        if ($relationType === 'hasChildren') {
            $selectParams['whereClause'][] = [$foreignLink . 'Id' => $entity->id];
            $selectParams['whereClause'][] = [$foreignLink . 'Type' => $entity->getEntityType()];
        } else {
            $selectManager->addJoin($foreignLink, $selectParams);
            $selectParams['whereClause'][] = [$foreignLink . '.id' => $entity->id];
        }

        if (count($args) <= 8) {
            $filter = null;
            if (count($args) == 8) {
                $filter = $args[7];
            }
            if ($filter) {
                if (!is_string($filter)) throw new Error("Formula record\\fetchRelatedMany: Bad filter.");
                $selectManager->applyFilter($filter, $selectParams);
            }
        } else {
            $i = 7;
            while ($i < count($args) - 1) {
                $key = $args[$i];
                $value = $args[$i + 1];

                if ($key == 'limit by') {
                    $selectParams['limit'] = $value + 0;
                } else {
                    $selectParams['whereClause'][] = [$key => $value];
                }

                $i = $i + 2;
            }
        }

        $selectParams['limit'] = $limit;

        if ($orderBy) {
            $selectManager->applyOrder($orderBy, $order, $selectParams);
        }


 	$items = array_map("trim", explode(',', $items));
        $e = $this->getInjection('entityManager')->getRepository($foreignEntityType)->select($items)->find($selectParams);

        $result = array();
        foreach($e as $elem) {
            $row = array();
            foreach($items as $name) {
                $val = $elem->get($name);
                array_push($row, $val);
            }
            array_push($result, $row);
        }

        return $result;
    }
}
