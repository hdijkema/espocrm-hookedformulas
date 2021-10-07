<?php
namespace Espo\Modules\HookedFormulas\Hooks\Common;

use Espo\Core\Formula\Manager as FormulaManager;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Metadata;
use \Espo\ORM\Entity;

class Formula extends \Espo\Hooks\Common\Formula
{
    private $_formulaManager;
    private $_metadata;

    public function __construct(Metadata $metadata, FormulaManager $formulaManager, Log $log)
    {
        $this->_metadata = $metadata;
        $this->_formulaManager = $formulaManager;

        parent::__construct($metadata, $formulaManager, $log);
    }

    protected function get_metadata()
    {
        return $this->_metadata;
    }

    protected function get_formula_manager() {
        return $this->_formulaManager;
    }

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
              if ($h == $hook) { return trim($part); }
           }
        }

        if ($hook == 'beforeSave') {
           // return what's left of $script
           // remove possible beforeSave / afterSave tags.
           $script = preg_replace('/(begin|end)[:]beforeSave/', '', $script);
           return trim($script);
        }
    }


    protected function executeFormula(Entity $entity, array $options = array())
    {
        if (!empty($options['skipFormula'])) return;

        $hook = $options['hook'];
        if (array_key_exists('vars', $options)) {
           $variables = $options['vars'];
        } else {
           $variables = (object)[];
        }

        if ($hook == 'beforeSave') {
            $scriptList = $this->get_metadata()->get(['formula', $entity->getEntityType(), 'beforeSaveScriptList'], []);
            foreach ($scriptList as $script) {
                try {
                    $this->get_formula_manager()->run($script, $entity, $variables);
                } catch (\Exception $e) {
                    $GLOBALS['log']->error('Formula failed: ' . $e->getMessage());
                }
            }
        }

        $customScript = $this->get_metadata()->get(['formula', $entity->getEntityType(), 'beforeSaveCustomScript']);
        if ($customScript) {
            $customScript = $this->getScript($customScript, $hook);
            if ($customScript) {
               try {
                   $this->get_formula_manager()->run($customScript, $entity, $variables);
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
