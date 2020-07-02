<?php
# vi: set sw=4 ts=4:
/************************************************************************
 * This file is part of HookedFormulas.
 *
 * HookedFormulas - Extension to the Open Source EspoCRM application.
 * Copyright (C) 2020 Hans Dijkema
 * Website: https://github.com/hdijkema/espocrm
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
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 ************************************************************************/

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\RecordGroup;

use Espo\Core\Exceptions\Error;

class RecalculateType extends \Espo\Core\Formula\Functions\Base
{
    protected function init()
    {
        $this->addDependency('entityManager');
		$this->addDependency('selectManagerFactory');
    }

    public function process(\StdClass $item)
    {
		if (!property_exists($item, 'value')) {
			throw new Error();
		}

		if (!is_array($item->value)) {
			throw new Error();
		}

		if (count($item->value) < 1) { 
        	throw new Error("Formula record\recalculate: Too few arguments.");
		}

		$entityType = $this->evaluate($item->value[0]);
        if (!is_string($entityType)) throw new Error("Formula record\recalculate: First argument should be a string (entitytype).");

		$data = [];
        $i = 1;
        while ($i < count($item->value) - 1) {
            $condition = $this->evaluate($item->value[$i]);
            if (!is_string($condition)) throw new Error("Formula record\calculate: Condition should be a string.");
            $value = $this->evaluate($item->value[$i + 1]);

			if ($condition == 'limit by') {
                $selectParams['limit'] = $value + 0;
            } else {
                $data[$condition] = $value;
            }

            $i = $i + 2;
        }

        $em = $this->getInjection('entityManager');
		$sm = $this->getInjection('selectManagerFactory');

		$selectMgr = $sm->create($entityType);
		$selectParams = $selectMgr->getEmptySelectParams();

		$selectParams['whereClause'] = $data;

		$collection = $em->getRepository($entityType)->find($selectParams);

		$ok = true;
		foreach ($collection as $entity) {
			$data = [];
			$nowString = date('Y-m-d H:i:s', time());
			$data['modifiedAt'] = $nowString;
			$ok = $ok && $em->saveEntity($entity);
		}

        return $ok;
    }
}

?>
