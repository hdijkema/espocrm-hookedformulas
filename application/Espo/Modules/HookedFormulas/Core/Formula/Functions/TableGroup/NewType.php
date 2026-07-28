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

class NewType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\EvaluatedFunction
{
    protected function processEvaluated(\stdClass $item): mixed
    {
        $args = $this->fetchArguments($item);

        if (count($args) < 1) throw new Error("Formula table\new: needs columns as arguments.");

	    $datatable_header = [];
        $obj = [];
	    while(count($args) > 0) {
            $arg = array_shift($args);
            if (is_int($arg)) { $obj['width'] = $arg; }
            else if ($arg == 'asc') { $obj['sort'] = 'asc'; }
            else if ($arg == 'desc') { $obj['sort'] = 'desc'; }
            else if ($arg == 'ellipsis') { $obj['ellipsis'] = true; }
            else if ($arg == 'right' || $arg == 'left' || $arg == 'center') { $obj['align'] = $arg; }
            else {
                if (isset($obj['column'])) {
                    array_push($datatable_header, $obj);
                }
                $obj = [];
                $obj['column'] = $arg;
            }
        }
        array_push($datatable_header, $obj);

        $datatable = (object)[ 'header' => $datatable_header, 'rows' => [], 'styles' => [], 'rows_per_page' => 15, 'filename' => '' ];

        return $datatable;
    }

}
