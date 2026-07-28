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

class CopyFileType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\EntityManagerBase
{

    protected function processEvaluated(\stdClass $item): mixed
    {

        $args = $this->fetchArguments($item);

        if (count($args) != 2) throw new Error("Formula ext\copyFile: wrong arguments, need <fileId> <Entity to relate the new copy to>.");

        $fileId = array_shift($args);
        $entity = array_shift($args);
        $entity_id = $entity->get('id');
        $entity_type = $entity->getEntityType();

        $em = $this->entityManager;

        $attachment_from = $em->getEntity('Attachment', $fileId);
        if (!$attachment_from) {
            $GLOBALS['log']->warning("Formula ext\\copyFile: file id not found.");
            return null;
        }

 	$sys = new \Espo\Core\Utils\System();
        $root_dir = $sys->getRootDir();
        $upload_dir = $root_dir . '/data/upload';
        $file=$upload_dir . '/' . $fileId;
        $size = filesize($file);
        $GLOBALS['log']->warning('file: '.$fileId.', path:'.$file.', size:'.$size);

        $contents = file_get_contents($file);

        $attachment = $em->createEntity('Attachment', [
                'name' => $attachment_from->get('name'),
                'type' => $attachment_from->get('type'),
                'contents' => $contents, 
                'relatedId' => $entity_id,
                'relatedType' => $entity_type,
                'role' => 'Attachment',
            ]);

        return $attachment->getId();
    }
}
