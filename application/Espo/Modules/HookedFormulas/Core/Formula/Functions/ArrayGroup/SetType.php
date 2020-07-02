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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\ArrayGroup;

use Espo\Core\Exceptions\Error;

class SetType extends \Espo\Core\Formula\Functions\Base
{
    public function process(\StdClass $item)
    {
        if (count($item->value) < 3) throw new Error("Formula: array\\set: Not enough arguments.");

	$array = $this->evaluate($item->value[0]);
        $index = $this->evaluate($item->value[1]);
        $value = $this->evaluate($item->value[2]);

        if (!is_array($array)) throw new Error("Formula: array\\set: First argument must be array.");
        if (!is_int($index)) throw new Error("Formula: array\\set: Second argument must be integer.");

        if (!array_key_exists($index, $array)) {
            $GLOBALS['log']->notice("Formula: array\\set: Index doesn't exist.");
            return null;
        }

        $array[$index] = $value;

        return $array;
    }
}
