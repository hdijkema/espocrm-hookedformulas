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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\HashGroup;

use Espo\Core\Exceptions\Error;

class SetType extends \Espo\Core\Formula\Functions\Base
{
    public function process(\StdClass $item)
    {
        $args = $this->fetchRawArguments($item);

        if (count($args) < 3) {
            throw new Error("Function \'hash\\set\' should receive at least 3 arguments.");
        }

        $hash = $this->evaluate($args[0]);
	$var = $this->evaluate($args[1]);
	$value = $this->evaluate($args[2]);

	$hash[$var] = $value;
	$i = 3;
        $n = count($args);
	while($i < $n) {
            if ($i + 1 == $n) {
                throw new Error("Function \'hash\\set\' must receive key/value pairs.");
            }

            $var = $this->evaluate($args[$i]);
            $value = $this->evaluate($args[$i + 1]);
            $hash[$var] = $value;

            $i += 2;
        }

	return $hash;
    }
}
