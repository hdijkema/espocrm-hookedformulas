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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\EspoGroup;

use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Exceptions\BadArgumentType;
use Espo\Core\Formula\Exceptions\BadArgumentValue;
use Espo\Core\Formula\Exceptions\TooFewArguments;
use Espo\Core\Formula\Func;
use Espo\Core\Utils\Config;

class InfoType implements Func
{
    public function __construct(
        private Config $config
    ) {}

    public function process(EvaluatedArgumentList $arguments): mixed
    {
        if (count($arguments) < 1) {
            throw TooFewArguments::create(1);
        }

        $item = $arguments[0];

        if (!is_string($item)) {
            throw BadArgumentType::create(1, 'string');
        }

        return match ($item) {
            'version' => $this->config->get('version'),
            'phpVersion' => PHP_VERSION,
            default => throw BadArgumentValue::create(
                1,
                "Unknown Espo information item '$item'."
            ),
        };
    }
}
