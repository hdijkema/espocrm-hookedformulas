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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions;

use \Espo\Core\Exceptions\Error;

class ConfigSetType extends Config
{
    protected function init()
    {
        $this->addDependency('entityManager');
        $this->addDependency('selectManagerFactory');
    }

    public function process(\StdClass $item)
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
        $selectManager = $this->getInjection('selectManagerFactory')->create($entityType);
        $selectParams = $selectManager->getEmptySelectParams();

        $whereClause = [];
        $key = 'name=';
        $value = $cfg_key;
        $whereClause[] = [$key => $value];

        $selectParams['whereClause'] = $whereClause;
        $entityRepos = $this->getInjection('entityManager')->getRepository($entityType);

        $e = $entityRepos->select(['id', 'type'])->findOne($selectParams);
        if ($e) {
            $type = $e->get('type');
            $cfg_entity = $entityRepos->get($e->id);
            if ($type == 'int') {
               $cfg_entity->set('valueInt', $setting);
            } else if ($type == 'string') {
               $cfg_entity->set('valueString', $setting);
            } else if ($type == 'real') {
               $cfg_entity->set('valueReal', $setting);
            }
            $entityRepos->save($cfg_entity);
        } else {
           throw new Error('\'configSet\', cannot get config item \''.$value.'\'');
        }
        return parent::set($cfg_key, $setting);
    }
}
