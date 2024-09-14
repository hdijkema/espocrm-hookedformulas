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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions;

use Espo\Core\Exceptions\Error;
use Espo\Core\Formula\Parser\Ast\Attribute;
use Espo\Core\Formula\Parser\Ast\Variable;

class LogAddType extends \Espo\Core\Formula\Functions\Base
{
    protected function init()
    {
        parent::init();
        $this->addDependency('config');
    }

    protected function getConfigManager()
    {
        return $this->getInjection('config');
    }

    public function process(\StdClass $item)
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

               if ($subItem instanceof Attribute) {
                  $type = 'attribute';
                  $var = $subItem->getName();
                  $var_value = $this->evaluate($subItem);
               } else if ($subItem instanceof Variable) {
                  $type = 'variable';
                  $var = $subItem->getName();
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
