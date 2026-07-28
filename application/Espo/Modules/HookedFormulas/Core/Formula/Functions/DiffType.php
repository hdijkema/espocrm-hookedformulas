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

class DiffType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\EvaluatedFunction
{
    private function getValue($v, $level = 1) 
    {
       if (is_string($v)) { 
          return "'" . $v . "'"; 
       } else if (is_object($v)) {
          $v = (array) $v;
          return $this->getArrayValues($v, $level);
       } else if (is_array($v)) {
          return $this->getArrayValues($v, $level);
       } else {
          return $v;
       }
    }

    private function getArrayValues($a, $level) 
    {
       if ($level > 2) { return ""; }

       $v = "[";
       $comma = "";
       foreach($a as $e) {
          $e = $this->getValue($e, $level + 1);
          $v .= $comma . $e;
          $comma = ", ";
       }
       $v .= "]";
       return $v;
    }

    protected function processEvaluated(\stdClass $item): mixed
    {
        if (!property_exists($item, 'value')) {
            throw new Error("Formula diff needs two entities as parameters");
        }

        if (!is_array($item->value)) {
            throw new Error("Formula diff needs two entities as parameters");
        }

        if (count($item->value) != 2) {
            throw new Error("Formula diff needs two entities as parameters");
        }

        $entity_old = $this->evaluate($item->value[0]);
        $entity_new = $this->evaluate($item->value[1]);

        if (!$entity_old && !$entity_new) throw new Error("Formula diff: At least one parameter needs to be an entity.");

        $diff_txt = "";

        $obj = (!$entity_old) ? $entity_new : $entity_old;

        $e_fields = $obj->getAttributeList();
        $e_rels = $obj->getRelationList();
        $fields = $e_fields;
	$fields = array_merge($fields, $e_rels);

	foreach($fields as $field) {
           if ($field != 'modifiedAt' && $field != 'teamsIds' && $field != 'teamsNames') {
              if (!$this->has($entity_old, $field)) {
                 if ($this->has($entity_new, $field)) {
                    $value = $this->getValue($entity_new->get($field));
                    $diff_txt .= "Alleen in nieuw: " . $field . " = ". $value . "\n";
                 }
              } else if (!$this->has($entity_new, $field)) {
                 if ($this->has($entity_old, $field)) {
                    $value = $this->getValue($entity_old->get($field));
                    $diff_txt .=   "Alleen in oud  : " . $field . " = " . $value . "\n";
                  }
              } else {
                 $value = $this->getValue($entity_old->get($field));
                 $v = $this->getValue($entity_new->get($field));
                 if ($v != $value) {
                   $diff_txt .= "Wijziging      : " . $field . " = " . $value . " -> " . $v . "\n";
                 }
              }
           }
        }

        $diff_txt = trim($diff_txt);
        if ($diff_txt != "") { $diff_txt = "```\n" . $diff_txt . "\n```\n"; }
        return $diff_txt;
    }

    private function has($e, $f) 
    {
       if (is_object($e)) { return $e->has($f); } else { return false; }
    }
}
