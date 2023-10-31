<?php
# vim: set tabstop=3:softtabstop=3:shiftwidth=3:noexpandtab

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Formula\Parser\Ast\Variable; 
use \Espo\Core\Formula\Parser\Ast\Attribute;

class StrAddType extends \Espo\Core\Formula\Functions\Base
{
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
        $type= '';

        foreach ($item->value as $subItem) {
            if ($first) {
	       		if (!($subItem instanceof Variable || $subItem instanceof Attribute)) {
                   throw new Error('First argument of \'strAdd\' must be a variable or an attribute');
               }
				   if ($subItem instanceof Variable) { $type = 'variable'; }
					else { $type = 'attribute'; }
	       		$var = $subItem->getName();
               $first = false;
            } 
            $part = $this->evaluate($subItem);

            if (!is_string($part)) {
                $part = strval($part);
            }

            $result .= $part;
        }

		  if ($type == 'attribute') {
            $this->getEntity()->set($var, $result);
        } else {
            $this->getVariables()->$var = $result;
        }
	
        return $result;
    }
}

