<?php
# vim: set tabstop=3:softtabstop=3:shiftwidth=3:noexpandtab

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Formula\Parser\Ast\Variable;
use \Espo\Core\Formula\Parser\Ast\Attribute;

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
            } else if ($type == 'text') {
               $cfg_entity->set('valueText', $setting);
            } else if ($type == 'script') {
               $cfg_entity->set('valueScript', $setting);
	    }

            $entityRepos->save($cfg_entity);
        } else {
           throw new Error('\'configSet\', cannot get config item \''.$value.'\'');
        }

        return parent::set($cfg_key, $setting);
    }
}
