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

use Espo\Core\Exceptions\Error;
use Espo\Core\Formula\Parser\Ast\Attribute;
use Espo\Core\Formula\Parser\Ast\Variable;

class LogAddType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\ContextFunction
{

    public function __construct(
        string $name,
        \Espo\Core\Formula\Processor $processor,
        ?\Espo\ORM\Entity $entity,
        ?\stdClass $variables,
        protected \Espo\Core\Utils\Config $config
    ) {
        parent::__construct($name, $processor, $entity, $variables);
    }


    protected function getConfigManager()
    {
        return $this->config;
    }

    protected function processContext(\stdClass $item): mixed
    {
        if (!property_exists($item, 'value')) {
            return '';
        }

        if (!is_array($item->value)) {
            throw new Error('Value for \'logAdd\' item is not array.');
        }

        $result = '';
        $var = '';
        $first = true;
        $second = true;
        $type = '';
        $kind = 'info';

        foreach ($item->value as $subItem) {
            if ($first) {

               $data = $this->getArgumentData($subItem);

               if ($data instanceof Attribute) {
                  $type = 'attribute';
                  $var = $data->getName();
                  $var_value = $this->evaluate($subItem);
               } else if ($data instanceof Variable) {
                  $type = 'variable';
                  $var = $data->getName();
                  $var_value = $this->evaluate($subItem);
               } else {
                   throw new Error('First argument of \'logAdd\' must be a variable or an entity');
               }
               $first = false;

            } else if ($second) {

               $k = $this->evaluate($subItem);
               if ($k == 'info' || $k == 'error' || $k == 'warning') {
                  $kind = $k;
	       } else if (preg_match('/^#[0-9a-fA-F]{6}$/', $k)) {
                  $kind = $k;
               } else {
                  $result .= $k;
               }
               $second = false;

            } else {

               $part = $this->evaluate($subItem);
               if (!is_string($part)) {
                   $part = strval($part);
               }
               $result .= $part;

            }
        }

        $config = $this->getConfigManager();
        $my_tz = $config->get('timeZone');
        $tz = date_default_timezone_get();
        date_default_timezone_set($my_tz);
        $dt = date("Y-m-d H:i:s"); 
        date_default_timezone_set($tz);

        $log_line = "";
        if ($result != '') {
            if (preg_match('/^#[0-9a-fA-F]{6}$/', $kind)) {
               $log_line = "$var_value<tr><td class=\"time\">$dt</td><td style=\"background: $kind;\">".htmlentities($result)."</td></tr>";
            } else {
               $log_line = "$var_value<tr><td class=\"time\">$dt</td><td class=\"$kind\">".htmlentities($result)."</td></tr>";
            }
        }

        if ($type == 'attribute') {
            $this->getEntity()->set($var, $log_line);
        } else {
            $this->getVariables()->$var = $log_line;
        }

        if ($kind == 'info') {
            $GLOBALS['log']->info("Formula: $result");
        } else if ($type == 'warning') {
            $GLOBALS['log']->warning("Formula: $result");
        } else if ($type == 'error') {
            $GLOBALS['log']->warning("Formula: $result");
        } 

        return $log_line;
    }
}
