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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\TableGroup;

use Espo\Core\Exceptions\Error;

class AddType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\ContextFunction
{
    protected function processContext(\stdClass $item): mixed
    {
        if (count($item->value) < 2) throw new Error("Formula table\\add: needs <table> and <column values> as arguments.");

        $var = $this->getArgumentName($item->value[0]);

        $table = $this->evaluate($item->value[0]);
        $ncols = count($table->header);
        $value_count = count($item->value);

        if ($value_count != ($ncols + 1)) throw new Error('Formula table\add: needs '.$ncols.' columns as arguments, has: '.($value_count - 1).'.');

        $row = [];
        for($i = 1; $i <= $ncols; $i++ ) {
            array_push($row, $this->evaluate($item->value[$i]));
        }

        array_push($table->rows, $row);
        array_push($table->styles, '');

        $this->getVariables()->$var = $table;

        return $table;
    }
}

