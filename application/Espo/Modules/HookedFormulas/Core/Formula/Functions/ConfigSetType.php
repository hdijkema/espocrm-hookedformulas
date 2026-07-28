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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions;

use \Espo\Core\Exceptions\Error;

class ConfigSetType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\SelectBase
{

    protected function processEvaluated(\stdClass $item): mixed
    {
        if (!property_exists($item, 'value')) {
            return '';
        }

        if (!is_array($item->value)) {
            throw new Error('Value for \'configSet\' item is not array.');
        }

        if (count($item->value) < 2) {
            throw new Error('\'configSet\' needs a configuration item and a setting.');
        }

        $cfg_key = $this->evaluate($item->value[0]);
        $setting = $this->evaluate($item->value[1]);

        $entityType = 'Config';
        $selectManager = $this->selectManagerFactory->create($entityType);
        $selectParams = $selectManager->getEmptySelectParams();

        $whereClause = [];
        $key = 'name=';
        $value = $cfg_key;
        $whereClause[] = [$key => $value];

        $selectParams['whereClause'] = $whereClause;
        $entityRepos = $this->entityManager->getRepository($entityType);

        $e = $this->createSelectBuilderFromParams(
            $entityType,
            $selectParams,
            ['id', 'type']
        )->findOne();
        if ($e) {
            $type = $e->get('type');
            $cfg_entity = $entityRepos->get($e->getId());

            if ($type == 'int') {
               $cfg_entity->set('valueInt', $setting);
            } else if ($type == 'string') {
               $cfg_entity->set('valueString', $setting);
            } else if ($type == 'real') {
               $cfg_entity->set('valueReal', $setting);
            }

            $entityRepos->save($cfg_entity);
        } else {
           throw new Error('\'configSet\', cannot set config item \''.$value.'\'');
        }

        #return parent::set($cfg_key, $setting);
        return true;
    }
}
