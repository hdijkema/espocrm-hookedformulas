<?php

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\Base;

use Espo\Core\Formula\EvaluatedArgumentList;
use Espo\Core\Formula\Func;
use stdClass;

/**
 * Base for functions whose arguments may be evaluated before invocation.
 */
abstract class EvaluatedFunction implements Func
{
    final public function process(EvaluatedArgumentList $arguments): mixed
    {
        return $this->processEvaluated((object) [
            'value' => iterator_to_array($arguments),
        ]);
    }

    abstract protected function processEvaluated(stdClass $item): mixed;

    protected function evaluate(mixed $value): mixed
    {
        return $value;
    }

    /** @return mixed[] */
    protected function fetchArguments(stdClass $item): array
    {
        return $item->value ?? [];
    }
}
