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

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\EntityGroup;

use Espo\Core\Exceptions\Error;
use Espo\Services\Record;

class GetType extends \Espo\Modules\HookedFormulas\Core\Formula\Functions\Base\MetadataEntityManagerBase
{

    private function getEntityManager()
    {
        $entitymgr = $this->entityManager;
        return $entitymgr;
    }

    private function getMetadata()
    {
        return $this->metadata;
    }


    private function loadEmailAddressField($entity)
    {
        $fieldDefs = $this->getMetadata()->get('entityDefs.' . $entity->getEntityType() . '.fields', array());
        if (!empty($fieldDefs['emailAddress']) && $fieldDefs['emailAddress']['type'] == 'email') {
            $dataAttributeName = 'emailAddressData';
            $emailAddressData = $this->getEntityManager()->getRepository('EmailAddress')->getEmailAddressData($entity);
            $entity->set($dataAttributeName, $emailAddressData);
            $entity->setFetched($dataAttributeName, $emailAddressData);
        }
    }

    private function loadPhoneNumberField($entity)
    {
        $fieldDefs = $this->getMetadata()->get('entityDefs.' . $entity->getEntityType() . '.fields', array());
        if (!empty($fieldDefs['phoneNumber']) && $fieldDefs['phoneNumber']['type'] == 'phone') {
            $dataAttributeName = 'phoneNumberData';
            $phoneNumberData = $this->getEntityManager()->getRepository('PhoneNumber')->getPhoneNumberData($entity);
            $entity->set($dataAttributeName, $phoneNumberData);
            $entity->setFetched($dataAttributeName, $phoneNumberData);
        }
    }

    private function loadLinkFields($entity)
    {
        $fieldDefs = $this->getMetadata()->get('entityDefs.' . $entity->getEntityType() . '.fields', array());
        $linkDefs = $this->getMetadata()->get('entityDefs.' . $entity->getEntityType() . '.links', array());
        foreach ($fieldDefs as $field => $defs) {
            if (isset($defs['type']) && $defs['type'] === 'link') {
                if (!empty($defs['noLoad'])) continue;
                if (empty($linkDefs[$field])) continue;
                if (empty($linkDefs[$field]['type'])) continue;
                if ($linkDefs[$field]['type'] !== 'hasOne') continue;

                $entity->loadLinkField($field);
            }
        }
    }


    private function loadLinkMultipleFields($entity)
    {
        $fieldDefs = $this->getMetadata()->get('entityDefs.' . $entity->getEntityType() . '.fields', array());
        foreach ($fieldDefs as $field => $defs) {
            if (isset($defs['type']) && in_array($defs['type'], ['linkMultiple', 'attachmentMultiple']) && empty($defs['noLoad'])) {
                $columns = null;
                if (!empty($defs['columns'])) {
                    $columns = $defs['columns'];
                }
                $entity->loadLinkMultipleField($field, $columns);
            }
        }
    }



    protected function processEvaluated(\stdClass $item): mixed
    {
        if (!property_exists($item, 'value')) {
            throw new Error();
        }

        if (!is_array($item->value)) {
            throw new Error();
        }

        if (count($item->value) < 2) {
            throw new Error();
        }

        $entityType = $this->evaluate($item->value[0]);
        $id = $this->evaluate($item->value[1]);

        if (!$entityType) throw new Error("Formula record\\attribute: Empty entityType.");
        if (!$id) return null;


        $entity = $this->getEntityManager()->getEntity($entityType, $id);
        if ($entity != null) {
           $this->loadPhoneNumberField($entity);
           $this->loadEmailAddressField($entity);
           $this->loadLinkFields($entity);
           $this->loadLinkMultipleFields($entity);
        }


        return $entity;
    }
}
