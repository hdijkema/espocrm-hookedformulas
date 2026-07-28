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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\HashGroup;

use Espo\Core\Exceptions\Error;

class CopyType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\EvaluatedFunction
{
    protected function processEvaluated(\stdClass $item): mixed
    {
        $args = $this->fetchArguments($item);

        if (count($args) != 1) {
            throw new Error("Function \'hash\\copy\' should receive 1 argument.");
        }

        $hash = $this->evaluate($args[0]);
        $chash = unserialize(serialize($hash));

        return $chash;
    }
}
