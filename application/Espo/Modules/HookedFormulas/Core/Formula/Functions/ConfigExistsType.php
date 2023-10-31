<?php
# vim: set tabstop=3:softtabstop=3:shiftwidth=3:noexpandtab

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Formula\Parser\Ast\Variable;
use \Espo\Core\Formula\Parser\Ast\Attribute;

class ConfigExistsType extends Config
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
            throw new Error('Value for \'configExists\' item is not array.');
        }

        if (count($item->value) < 1) {
            throw new Error('\'configExists\' needs a configuration item.'); 
        }

        $cfg_key = $this->evaluate($item->value[0]);

        if (parent::has($cfg_key)) { return true; }

        $entityType = 'Config';
        $selectManager = $this->getInjection('selectManagerFactory')->create($entityType);
        $selectParams = $selectManager->getEmptySelectParams();

        $whereClause = [];
        $key = 'name=';
        $value = $cfg_key;
        $whereClause[] = [$key => $value];

        $selectParams['whereClause'] = $whereClause;

        $e = $this->getInjection('entityManager')->getRepository($entityType)->select(['id', 'type', 'valueInt', 'valueString', 'valueReal', 'valueDocId', 'valueScript' ])->findOne($selectParams);

        if ($e) { 
            return true;
        } else {
            return false;
        }
    }
}
