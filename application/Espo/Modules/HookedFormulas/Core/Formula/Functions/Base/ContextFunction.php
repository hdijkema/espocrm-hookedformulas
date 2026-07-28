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

use Espo\Core\Formula\Argument;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Formula\Parser\Ast\Attribute;
use Espo\Core\Formula\Parser\Ast\Variable;
use stdClass;

/**
 * Base for functions that need raw arguments, the current entity or variables.
 */
abstract class ContextFunction extends BaseFunction
{
    final public function process(ArgumentList $arguments): mixed
    {
        return $this->processContext((object) [
            'value' => iterator_to_array($arguments),
        ]);
    }

    abstract protected function processContext(stdClass $item): mixed;

    /** @return mixed[] */
    protected function fetchArguments(stdClass $item): array
    {
        $result = [];

        foreach ($item->value ?? [] as $argument) {
            $result[] = $this->evaluate($argument);
        }

        return $result;
    }

    protected function getArgumentData(Argument $argument): mixed
    {
        return $argument->getData();
    }

    protected function getArgumentName(Argument $argument): string
    {
        $data = $argument->getData();

        if ($data instanceof Attribute || $data instanceof Variable) {
            return $data->getName();
        }

        $this->throwBadArgumentType(0, 'attribute or variable');
    }
}
