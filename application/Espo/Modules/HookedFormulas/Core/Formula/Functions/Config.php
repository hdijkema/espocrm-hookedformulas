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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions;

use \Espo\Core\Exceptions\Error;

$hookedformulas_config = [];

abstract class Config extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\SelectBase
{

    protected function has($key) 
    {
       global $hookedformulas_config;
       return isset($hookedformulas_config[$key]);
    }

    protected function set($key, $val)
    {
       global $hookedformulas_config;
       $hookedformulas_config[$key] = $val;
       return $val;
    }

    protected function get($key)
    {
       global $hookedformulas_config;
       return $hookedformulas_config[$key];
    }
  
    protected function clear($key) 
    {
       global $hookedformulas_config;
       unset($hookedformulas_config[$key]);
    }
    
    protected function clearCache()
    {
    	global $hookedformulas_config;
    	$hookedformulas_config = [];
    }
}
