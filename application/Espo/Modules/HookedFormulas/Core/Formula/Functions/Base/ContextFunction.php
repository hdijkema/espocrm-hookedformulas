<?php

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\Base;

use Espo\Core\Formula\Argument;
use Espo\Core\Formula\ArgumentList;
use Espo\Core\Formula\Functions\BaseFunction;
use Espo\Core\Formula\Parser\Ast\Attribute;
use Espo\Core\Formula\Parser\Ast\Variable;
use stdClass;

/**
 * Base for functions that need raw arguments, the current entity or variables.
 */
abstract class ContextFunction extends BaseFunction
{
    final public function process(ArgumentList $arguments): mixed
    {
        return $this->processContext((object) [
            'value' => iterator_to_array($arguments),
        ]);
    }

    abstract protected function processContext(stdClass $item): mixed;

    /** @return mixed[] */
    protected function fetchArguments(stdClass $item): array
    {
        $result = [];

        foreach ($item->value ?? [] as $argument) {
            $result[] = $this->evaluate($argument);
        }

        return $result;
    }

    protected function getArgumentData(Argument $argument): mixed
    {
        return $argument->getData();
    }

    protected function getArgumentName(Argument $argument): string
    {
        $data = $argument->getData();

        if ($data instanceof Attribute || $data instanceof Variable) {
            return $data->getName();
        }

        $this->throwBadArgumentType(0, 'attribute or variable');
    }
}
