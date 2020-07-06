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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\RecordGroup;

use Espo\Core\Exceptions\Error;

class FetchManyHashType extends FetchRecords
{
    public function process(\StdClass $item)
    {
        $obj = $this->FetchRecs($item);

        $e = $obj->elements;
        $items = $obj->items;

        $result = array();
        $first = true;
        foreach($e as $elem) {
            $row = array();
            if ($first) { file_put_contents('/data/www/crm/tmp/elem.json', json_encode($elem));$first=false; }
            foreach($items as $name) {
		$val = $elem->get($name);
		$row[$name] = $val;
            }
            array_push($result, $row);
        }

        return $result;
    }
}
