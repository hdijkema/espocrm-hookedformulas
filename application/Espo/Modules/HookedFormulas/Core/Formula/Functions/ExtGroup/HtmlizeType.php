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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\ExtGroup;

use Espo\Core\Exceptions\Error;

#
# arg1 - template
# arg2 - entityType
# arg3 - entity Id
#

class HtmlizeType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\EvaluatedFunction
{

    public function __construct(
        protected \Espo\ORM\EntityManager $entityManager,
        protected \Espo\Core\Htmlizer\HtmlizerFactory $htmlizerFactory
    ) {}



    protected function processEvaluated(\stdClass $item): mixed
    {
        $args = $this->fetchArguments($item);

        if (count($args) != 3) throw new Error("Formula ext\\htmlize: wrong arguments.");

        $template = array_shift($args);
        $entity_type = array_shift($args);
        $entity_id = array_shift($args);

        if (
            is_string($template) &&
            preg_match('/<body\b[^>]*>(.*)<\/body\s*>/is', $template, $matches)
        ) {
            $template = $matches[1];
        }

        $em = $this->entityManager;

        $entity = $em->getEntity($entity_type, $entity_id);

        $htmlizer = $this->createHtmlizer();

        return $htmlizer->render($entity, $template);
    }

    protected function createHtmlizer()
    {
        return $this->htmlizerFactory->create();
    }

}
