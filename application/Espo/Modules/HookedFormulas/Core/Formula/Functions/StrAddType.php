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
use Espo\Core\Formula\Parser\Ast\Attribute;
use Espo\Core\Formula\Parser\Ast\Variable;

class StrAddType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\ContextFunction
{
    protected function processContext(\stdClass $item): mixed
    {
        if (!property_exists($item, 'value')) {
            return '';
        }

        if (!is_array($item->value)) {
            throw new Error('Value for \'strAdd\' item is not array.');
        }

        $result = '';
        $var = '';
        $first = true;
        $type= '';

        foreach ($item->value as $subItem) {
            if ($first) {
               $data = $this->getArgumentData($subItem);

               if ($data instanceof Attribute) {
                  $type = 'attribute';
                  $var = $data->getName();
                  $var_value = $this->evaluate($subItem);
               } else if ($data instanceof Variable) {
                  $type = 'variable';
                  $var = $data->getName();
                  $var_value = $this->evaluate($subItem);
               } else {
                   throw new Error('First argument of \'strAdd\' must be a variable or an entity');
               }
               $first = false;
            } 

            $part = $this->evaluate($subItem);

            if (!is_string($part)) {
                $part = strval($part);
            }

            $result .= $part;
        }

	if ($type == 'attribute') {
            $this->getEntity()->set($var, $result);
        } else {
            $this->getVariables()->$var = $result;
        }
	
        return $result;
    }
}
