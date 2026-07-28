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

use \Espo\ORM\Entity;
use \Espo\Core\Exceptions\Error;

class ExecType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\ContextFunction
{

    public function __construct(
        string $name,
        \Espo\Core\Formula\Processor $processor,
        ?\Espo\ORM\Entity $entity,
        ?\stdClass $variables,
        protected \Espo\Core\Formula\Manager $formulaManager
    ) {
        parent::__construct($name, $processor, $entity, $variables);
    }


    protected function getFormulaManager()
    {
        return $this->formulaManager;
    }

    protected function processContext(\stdClass $item): mixed
    {
        if (!property_exists($item, 'value')) {
            return true;
        }

        if (!is_array($item->value)) {
            throw new Error('Value for \'Exec\' item is not array.');
        }

        if (count($item->value) != 1) {
             throw new Error('Bad value for \'Exec\' item.');
        }

        $formula = $this->evaluate($item->value[0]);
        $variables = $this->getVariables();
        $entity = $this->getEntity();
		return $this->getFormulaManager()->run($formula, $entity, $variables);
    }
}

?>
