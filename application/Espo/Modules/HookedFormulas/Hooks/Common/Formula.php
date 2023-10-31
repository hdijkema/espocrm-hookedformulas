<?php
# vim: set tabstop=3:softtabstop=3:shiftwidth=3:noexpandtab
namespace Espo\Modules\HookedFormulas\Hooks\Common;

use Espo\ORM\Entity;
use Espo\ORM\Query\Select;

use Espo\ORM\Repository\Option\Traits\Options;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\ORM\Repository\Option\RemoveOptions;
use Espo\ORM\Repository\Option\RelateOptions;
use Espo\ORM\Repository\Option\UnrelateOptions;
use Espo\ORM\Repository\Option\MassRelateOptions;

use Espo\Core\Hook\Hook\BeforeSave;
use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\Hook\Hook\BeforeRemove;
use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Core\Hook\Hook\AfterRelate;
use Espo\Core\Hook\Hook\AfterUnrelate;
use Espo\Core\Hook\Hook\AfterMassRelate;

use Espo\Core\Formula\Manager as FormulaManager;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Metadata;

class Formula extends \Espo\Hooks\Common\Formula
		implements 	BeforeSave, AfterSave, 
						BeforeRemove, AfterRemove, 
						AfterRelate, AfterUnrelate, AfterMassRelate
{

    private Metadata $metadata;
    private FormulaManager $formulaManager;
    private Log $log;


    public function __construct(Metadata $metadata, FormulaManager $formulaManager, Log $log)
    {
		parent::__construct($metadata, $formulaManager, $log);
      $this->metadata = $metadata;
      $this->formulaManager = $formulaManager;
      $this->log = $log;
    }

    protected function get_metadata()
    {
        if (isset($this->metadata)) {
           # EspoCRM >= 6.0.0
           return $this->metadata;
        } else { 
           # EspoCRM < 6.0.0
           return $this->getMetadata();
        }
    }

    protected function get_formula_manager() {
        if (isset($this->formulaManager)) {
           # EspoCRM >= 6.0.0
           return $this->formulaManager;
        } else {
           # EspoCRM < 6.0.0
           return $this->getFormulaManager();
        }
    }

    private function runScript(string $script, Entity $entity, stdClass $variables): void
    {
        try {
            $this->formulaManager->run($script, $entity, $variables);
        }
        catch (Exception $e) {
            $this->log->error('Before-save formula script failed: ' . $e->getMessage());
        }
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

    protected function executeFormula(Entity $entity, $options)
    {
		if ($options->get('skipFormula')) { return; }

      $hook = $options->get('hf_hook');
		if ($options->has('vars')) {
	   	$variables = $options->get('vars');
      } else {
         $variables = (object)[];
      }

      if ($hook == 'beforeSave') {
         $scriptList = $this->get_metadata()->get(['formula', $entity->getEntityType(), 'beforeSaveScriptList'], []);
         foreach ($scriptList as $script) {
				$this->runScript($script, $entity, $variables);
         }
      }

      $customScript = $this->get_metadata()->get(['formula', $entity->getEntityType(), 'beforeSaveCustomScript']);
      if ($customScript) {
			$customScript = $this->getScript($customScript, $hook);
         if ($customScript) {
            try {
               $this->get_formula_manager()->run($customScript, $entity, $variables);
            } catch (\Exception $e) {
               $GLOBALS['log']->error('Formula failed (hook=' . $hook . '): ' . $e->getMessage());
            }
         }
      }
 	}

   public function beforeSave(Entity $entity, SaveOptions $options) : void
   {
		$options = $options->with('hf_hook', 'beforeSave');
      $this->executeFormula($entity, $options);
   }

   public function afterSave(Entity $entity, SaveOptions $options) : void
   {
		$options = $options->with('hf_hook', 'afterSave');
      $this->executeFormula($entity, $options);
   }

   public function beforeRemove(Entity $entity, RemoveOptions $options) : void
   {
		$options = $options->with('hf_hook', 'beforeRemove');
      $this->executeFormula($entity, $options);
   }

   public function afterRemove(Entity $entity, RemoveOptions $options) : void
   {
		$options = $options->with('hf_hook', 'afterRemove');
		$this->executeFormula($entity, $options);
   }

   public function afterRelate(Entity $entity, string $relationName, Entity $relatedEntity, array $columnData, RelateOptions $options) : void
   {
		$options = $options->with('hf_hook', 'afterRelate');
      $options['vars'] = (object) $hookdata;
      $this->executeFormula($entity, $options);
   }

   public function afterUnrelate(Entity $entity, string $relationName, Entity $relatedEntity, UnrelateOptions $options) : void
   {
		$options = $options->with('hf_hook', 'afterRelate');
		$options = $options->with('relationName', $relationName);
		$options = $options->with('relatedEntity', $relatedEntity);
		$options = $options->with('foreignEntity', $relatedEntity);

		$vars = (object) [ 'relationName' =>  $relationName, 'foreignEntity' => $relatedEntity, 'relatedEntity' => $relatedEntity ];
		$options = $options->with('vars', (object) $vars);

      $this->executeFormula($entity, $options);
   }

   public function afterMassRelate(Entity $entity, string $relationName, Select $query, array $columndata, MassRelateOptions $options) : void
   {
		$options = $options->with('hf_hook', 'afterMassRelate');
		$options = $options->with('relationName', $relationName);
		$options = $options->with('query', $query);
		$options = $options->with('columndata', $columndata);

		$vars = (object) [ 'relationName' =>  $relationName ];
		$options = $options->with('vars', (object) $vars);

		$this->executeFormula($entity, $options);
	}

}

?>
