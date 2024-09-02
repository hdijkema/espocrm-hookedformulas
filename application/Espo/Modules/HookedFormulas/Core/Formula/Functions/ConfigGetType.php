<?php
# vim: set tabstop=3:softtabstop=3:shiftwidth=3:noexpandtab

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Formula\Parser\Ast\Variable;
use \Espo\Core\Formula\Parser\Ast\Attribute;

class ConfigGetType extends Config
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
            throw new Error('Value for \'configGet\' item is not array.');
        }

        if (count($item->value) < 1) {
            throw new Error('\'configGet\' needs a configuration item.'); 
        }

        $cfg_key = $this->evaluate($item->value[0]);
        if (parent::has($cfg_key)) {
            return parent::get($cfg_key);
        }

        $entityType = 'Config';
        $selectManager = $this->getInjection('selectManagerFactory')->create($entityType);
        $selectParams = $selectManager->getEmptySelectParams();

        $whereClause = [];
        $key = 'name=';
        $value = $cfg_key;
        $whereClause[] = [$key => $value];

        $selectParams['whereClause'] = $whereClause;


        $e = $this->getInjection('entityManager')->getRepository($entityType)->select(['id', 'type', 'valueInt', 'valueString', 'valueReal', 'valueText', 'valueDocId', 'valueScript' ])->findOne($selectParams);
        if ($e) { 
            $type = $e->get('type');
            if ($type == 'int') {
               return parent::set($cfg_key, $e->get('valueInt'));
            } else if ($type == 'string') {
               return parent::set($cfg_key, $e->get('valueString'));
            } else if ($type == 'real') {
               return parent::set($cfg_key, $e->get('valueReal'));
            } else if ($type == 'doc') {
               return parent::set($cfg_key, $e->get('valueDocId'));
            } else if ($type == 'script') {
               return parent::set($cfg_key, $e->get('valueScript')); 
            } else if ($type == 'text') {
               return parent::set($cfg_key, $e->get('valueText')); 
            } else {
              throw new Error('\'configGet\', type '.$type.' is not supported');
            }
        } else {
           throw new Error('\'configGet\', cannot get config item \''.$value.'\'');
        }
    }
}
