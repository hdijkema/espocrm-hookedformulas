<?php
/************************************************************************
 * This file is part of EspoCRM.
 *
 * EspoCRM - Open Source CRM application.
 * Copyright (C) 2014-2020 Yuri Kuznetsov, Taras Machyshyn, Oleksiy Avramenko
 * Website: https://www.espocrm.com
 *
 * EspoCRM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * EspoCRM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with EspoCRM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "EspoCRM" word.
 ************************************************************************/

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\ExtGroup;

use Espo\Core\Exceptions\Error;

#
# arg1 - template
# arg2 - entityType
# arg3 - entity Id
#

class HtmlizeType extends \Espo\Core\Formula\Functions\Base
{
    protected function init()
    {
        $this->addDependency('entityManager');
        $this->addDependency('serviceFactory');
        $this->addDependency('htmlizerFactory');
    }

    public function process(\StdClass $item)
    {
        $args = $this->fetchArguments($item);

        if (count($args) != 3) throw new Error("Formula ext\\pdf\\fillin: wrong arguments.");

        $template = array_shift($args);
        $entity_type = array_shift($args);
        $entity_id = array_shift($args);

        $em = $this->getInjection('entityManager');

        $entity = $em->getEntity($entity_type, $entity_id);

        $htmlizer = $this->createHtmlizer();

        return $htmlizer->render($entity, $template);
    }

    protected function createHtmlizer()
    {
        return $this->getInjection('htmlizerFactory')->create();
    }

}
