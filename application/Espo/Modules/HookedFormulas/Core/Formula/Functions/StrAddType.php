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
use Espo\Core\Formula\Parser\Ast\Attribute;
use Espo\Core\Formula\Parser\Ast\Variable;

class StrAddType extends \Espo\Core\Formula\Functions\Base
{
    public function process(\StdClass $item)
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
               if ($subItem instanceof Attribute) {
                  $type = 'attribute';
                  $var = $subItem->getName();
                  $var_value = $this->evaluate($subItem);
               } else if ($subItem instanceof Variable) {
                  $type = 'variable';
                  $var = $subItem->getName();
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
