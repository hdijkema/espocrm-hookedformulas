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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions;

use \Espo\ORM\Entity;
use \Espo\Core\Exceptions\Error;

class LogType extends \Espo\Core\Formula\Functions\Base
{
    public function process(\StdClass $item)
    {
        if (!property_exists($item, 'value')) {
            return true;
        }

        if (!is_array($item->value)) {
            throw new Error('Value for \'log\' item is not array.');
        }

	$n = count($item->value);
	if ($n < 1) {
		throw new Error('log needs at least info|warning|error as first parameter');
	}

	$type = $this->evaluate($item->value[0]);

	$msg = "";
	$i = 0;
	for($i = 1; $i < $n; $i++) {
		$expr = $item->value[$i];
		$str = $this->evaluate($expr);
		$msg .= $str;
	}

	if ($type == 'info') {
		$GLOBALS['log']->info("Formula: $msg");
	} else if ($type == 'warning') {
		$GLOBALS['log']->warning("Formula: $msg");
	} else if ($type == 'error') {
		$GLOBALS['log']->warning("Formula: $msg");
	} else {
		throw new Error('Unknown type \''.$type.'\' for log function.');
	}

	return $msg;
    }
}

?>
