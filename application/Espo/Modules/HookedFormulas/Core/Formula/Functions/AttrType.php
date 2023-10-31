<?php
# vim: set tabstop=3:softtabstop=3:shiftwidth=3:noexpandtab

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Formula\Parser\Ast\Variable;
use \Espo\Core\Formula\Parser\Ast\Attribute;

class AttrType extends \Espo\Core\Formula\Functions\AttributeType
{
    public function process(\StdClass $item)
    {
         if (!property_exists($item, 'value')) {
            throw new Error();
         }

         if (!is_array($item->value)) {
            throw new Error();
         }

         if (count($item->value) < 2) {
            throw new Error();
         }

         $entity = $this->evaluate($item->value[0]);
         $attr = $this->evaluate($item->value[1]);

         if (!$entity) throw new Error("Formula attr: Empty entity.");
         if (!$attr) throw new Error("Formula attr: Empty attribute.");

			return $entity->get($attr);
    }
}
