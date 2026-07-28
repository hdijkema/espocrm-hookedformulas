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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\Base;

use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Func;
use stdClass;

/**
 * Base for functions whose arguments may be evaluated before invocation.
 */
abstract class EvaluatedFunction implements Func
{
    final public function process(EvaluatedArgumentList $arguments): mixed
    {
        return $this->processEvaluated((object) [
            'value' => iterator_to_array($arguments),
        ]);
    }

    abstract protected function processEvaluated(stdClass $item): mixed;

    protected function evaluate(mixed $value): mixed
    {
        return $value;
    }

    /** @return mixed[] */
    protected function fetchArguments(stdClass $item): array
    {
        return $item->value ?? [];
    }
}
