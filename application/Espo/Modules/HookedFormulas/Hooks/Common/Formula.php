<?php
# vim: set noai ts=4 sw=4: 

namespace Espo\Modules\HookedFormulas\Hooks\Common;

use Espo\Core\Formula\Manager as FormulaManager;
use Espo\Core\Utils\Log;
use Espo\Core\Utils\Metadata;
use Espo\ORM\Entity;
use Espo\ORM\Query\Select;

use Espo\Core\Hook\Hook\AfterMassRelate;
use Espo\Core\Hook\Hook\AfterRelate;
use Espo\Core\Hook\Hook\AfterRemove;
use Espo\Core\Hook\Hook\AfterSave;
use Espo\Core\Hook\Hook\AfterUnrelate;
use Espo\Core\Hook\Hook\BeforeRemove;
use Espo\Core\Hook\Hook\BeforeSave;

use Espo\ORM\Repository\Option\MassRelateOptions;
use Espo\ORM\Repository\Option\RelateOptions;
use Espo\ORM\Repository\Option\RemoveOptions;
use Espo\ORM\Repository\Option\SaveOptions;
use Espo\ORM\Repository\Option\UnrelateOptions;

################################################################
# As of EspoCRM 7.4.6 (AFAIK), the Hooks are more structured.
# Stronger typed.
#
# Relation with Espo\Core\Hook\GeneralInvoker
#
# This general invoker looks if a $hook is an instance of something,
# e.g. instanceof BeforeSave. 
#
# It also looks at instances of: 
#   - AfterSave, 
#   - BeforeRemove, AfterRemove, 
#   - AfterRelate, AfterUnrelate, AfterMassRelate
#
# Now, the Formula class of EspoCRM implements 'BeforeSave' and 
# nothing else. We derive from this Formula, but we also support
# the other hooks.
#
# So it looks like we want to implement the other interfaces
# to make things better.
#
################################################################

class HDI_Options
{
	private $opts;
	
	public function __construct($o) 
	{
		$this->opts = $o;
	}
	
	public function has(string $key): bool
	{ 
		return $this->opts->has($key); 
	}
	
	public function set(string $opt, mixed $val): void
	{
		$this->opts = $this->opts->with($opt, $val);
	}
	
	public function unset(string $opt): void
	{
		$this->opts = $this->opts->without($opt);
	}
	
	public function get(string $opt): mixed
	{
		return $this->opts->get($opt);
	}
}


class Formula extends \Espo\Hooks\Common\Formula
  implements BeforeSave, AfterSave, 
             BeforeRemove, AfterRemove,
             AfterRelate, AfterUnrelate,
             AfterMassRelate
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


    protected function executeFormula(string $hook, Entity $entity, HDI_Options $options) : void
    {
		if ($options->has('skipFormula')) {
			if (!empty($options->get('skipFormula'))) {
				return;
			}
		}

		if ($options->has('vars')) {
			$variables = $options->get('vars');
        } else {
           $variables = (object) [];
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
	
    public function beforeSave(Entity $entity, SaveOptions $options): void
    {
		$opts = new HDI_Options($options);
        $this->executeFormula('beforeSave', $entity, $opts);
    }

    public function afterSave(Entity $entity, SaveOptions $options): void
    {
		$opts = new HDI_Options($options);
        $this->executeFormula('afterSave', $entity, $opts);
    }

    public function beforeRemove(Entity $entity, RemoveOptions $options): void
    {
		$opts = new HDI_Options($options);
        $this->executeFormula('beforeRemove', $entity, $opts);
    }

    public function afterRemove(Entity $entity, RemoveOptions $options): void
    {
		$opts = new HDI_Options($options);
        $this->executeFormula('afterRemove', $entity, $opts);
    }

    public function afterRelate(Entity $entity, string $relationName, Entity $relatedEntity, array $columnData, RelateOptions $options): void
    {
		$hookData = [
			'relationName' => $relationName,
			'foreignEntity' => $relatedEntity,
			'relationData' => $columnData
		];

		$opts = new HDI_Options($options);
		$opts->set('vars', (object) $hookData);

        $this->executeFormula('afterRelate', $entity, $opts);
    }

    public function afterUnrelate(Entity $entity, string $relationName, Entity $relatedEntity, UnrelateOptions $options): void
    {
		$hookData = [
			'relationName' => $relationName,
			'foreignEntity' => $relatedEntity
		];
		
		$opts = new HDI_Options($options);
		$opts->set('vars', (object) $hookData);
		
        $this->executeFormula('afterUnrelate', $entity, $opts);
    }
	
    public function afterMassRelate(Entity $entity, string $relationName, Select $query, array $columnData, MassRelateOptions $options): void
    {
		$hookData = [
			'relationName' => $relationName,
			'query' => $query,
			'relationData' => $columnData
		];
		
		$opts = new HDI_Options($options);
		$opts->set('vars', (object) $hookData);
		
        $this->executeFormula('afterMassRelate', $entity, $opts);
    }

}

?>
