<?php

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\Base;

use Espo\Core\Utils\Metadata;
use Espo\ORM\EntityManager;

abstract class MetadataEntityManagerBase extends EntityManagerBase
{
    public function __construct(
        EntityManager $entityManager,
        protected Metadata $metadata
    ) {
        parent::__construct($entityManager);
    }
}
