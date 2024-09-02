<?php
# vim: set tabstop=3:softtabstop=3:shiftwidth=3:noexpandtab

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Formula\Parser\Ast\Variable;
use \Espo\Core\Formula\Parser\Ast\Attribute;

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
            throw new Error('Value for \'strAdd\' item is not array.');
        }

        $result = '';
        $var = '';
        $first = true;
        $second = true;
        $type = '';
        $kind = 'info';

        foreach ($item->value as $subItem) {
            if ($first) {
              if (!($subItem instanceof Variable || $subItem instanceof Attribute)) {
                   throw new Error('First argument of \'logAdd\' must be the variable or attribute');
               } else {
                   $var = $subItem->getName();
						 if ($subItem instanceof Variable) { $type = 'variable'; }
						 else { $type = 'attribute'; }
                   $var_value = $this->evaluate($subItem);
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

