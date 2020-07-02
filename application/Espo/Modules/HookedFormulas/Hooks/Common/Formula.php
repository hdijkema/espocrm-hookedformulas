<?php

namespace Espo\Modules\HookedFormulas\Hooks\Common;

use \Espo\ORM\Entity;

class Formula extends \Espo\Hooks\Common\Formula
{

    protected function getScript($script, $hook)
    {
        $hooks = [ 'afterSave', 'beforeRemove', 'afterRemove', 'afterRelate', 'afterUnrelate', 'afterMassRelate' ];
        if ($hook != 'beforeSave') { array_unshift($hooks, $hook); }

        $i = 0;
        $n = count($hooks);
        for($i = 0; $i < $n; $i++) {
           $h = $hooks[$i];
           $begin_needle = "begin:$h";
           $end_needle = "end:$h";
           $idx_begin = strpos($script, $begin_needle);
           $idx_end = strpos($script, $end_needle);
           if ($idx_begin === false || $idx_end === false) {
              if ($h == $hook) { return false; }
           } else {
              $idx = $idx_begin + strlen($begin_needle);
              $len = $idx_end - $idx;
              $part = substr($script, $idx, $len);
              $script = substr($script, 0, $idx_begin) . substr($script, $idx_end + strlen($end_needle));
              if ($h == $hook) { return $part; }
           }
        }

        if ($hook == 'beforeSave') {
           // return what's left of $script
           // remove possible beforeSave / afterSave tags.
           $script = preg_replace('/(begin|end)[:]beforeSave/', '', $script);
           return $script;
        }
    }


    protected function executeFormula(Entity $entity, array $options = array())
    {
        if (!empty($options['skipFormula'])) return;

        $hook = $options['hook'];
        if ($options['vars']) {
           $variables = $options['vars'];
        } else {
           $variables = (object)[];
        }

        if ($hook == 'beforeSave') {
            $scriptList = $this->getMetadata()->get(['formula', $entity->getEntityType(), 'beforeSaveScriptList'], []);
            foreach ($scriptList as $script) {
                try {
                    $this->getFormulaManager()->run($script, $entity, $variables);
                } catch (\Exception $e) {
                    $GLOBALS['log']->error('Formula failed: ' . $e->getMessage());
                }
            }
        }

        $customScript = $this->getMetadata()->get(['formula', $entity->getEntityType(), 'beforeSaveCustomScript']);
        if ($customScript) {
            $customScript = $this->getScript($customScript, $hook);
            if ($customScript) {
               try {
                   $this->getFormulaManager()->run($customScript, $entity, $variables);
               } catch (\Exception $e) {
                   $GLOBALS['log']->error('Formula failed: ' . $e->getMessage());
               }
            }
        }
    }


    public function beforeSave(Entity $entity, array $options = array())
    {
        $options['hook'] = 'beforeSave';
        $this->executeFormula($entity, $options);
    }

    public function afterSave(Entity $entity, array $options = array())
    {
        $options['hook'] = 'afterSave';
        $this->executeFormula($entity, $options);
    }

    public function beforeRemove(Entity $entity, array $options = array())
    {
        $options['hook'] = 'beforeRemove';
        $this->executeFormula($entity, $options);
    }

    public function afterRemove(Entity $entity, array $options = array())
    {
        $options['hook'] = 'afterRemove';
        $this->executeFormula($entity, $options);
    }

    public function afterRelate(Entity $entity, array $options = array(), array $hookdata = array())
    {
        $options['hook'] = 'afterRelate';
        $options['vars'] = (object) $hookdata;
        $this->executeFormula($entity, $options);
    }

    public function afterUnrelate(Entity $entity, array $options = array(), array $hookdata = array())
    {
        $options['hook'] = 'afterUnrelate';
        $options['vars'] = (object) $hookdata;
        $this->executeFormula($entity, $options);
    }

    public function afterMassRelate(Entity $entity, array $options = array(), array $hookdata = array())
    {
        $options['hook'] = 'afterMassRelate';
        $options['vars'] = (object) $hookdata;
        $this->executeFormula($entity, $options);
    }

}

?>
