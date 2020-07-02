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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\GlobalGroup;

use Espo\Core\Exceptions\Error;

$global_group_global_vars = [ ];

class Globals extends \Espo\Core\Formula\Functions\Base
{
    public function process(\StdClass $item)
    {
    }

    protected function doSet($a, $b) 
    {
       global $global_group_global_vars;
       $global_group_global_vars[$a] = $b;
    }

    protected function doGet($a) 
    {
       global $global_group_global_vars;
       return $global_group_global_vars[$a];
    }

    protected function doesExist($a) 
    {
       global $global_group_global_vars;
       return isset($global_group_global_vars[$a]);
    }

    protected function doUnset($a)
    {
       global $global_group_global_vars;
       unset($global_group_global_vars[$a]);
    }
}
