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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\RecordGroup;

use Espo\Core\Exceptions\Error;

class FetchManyHashType extends FetchRecords
{
    protected function processEvaluated(\stdClass $item): mixed
    {
        $obj = $this->FetchRecs($item);

        $e = $obj->elements;
        $items = $obj->items;

        $result = array();
        #$first = true;
        foreach($e as $elem) {
            $row = array();
            #if ($first) { file_put_contents('/data/www/crm/tmp/elem.json', json_encode($elem));$first=false; }
            foreach($items as $name) {
		$val = $elem->get($name);
		$row[$name] = $val;
            }
            array_push($result, $row);
        }

        return $result;
    }
}
