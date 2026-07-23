<?php

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\Base;

use Espo\Core\Select\SelectManagerFactory;
use Espo\Core\Utils\Metadata;
use Espo\ORM\EntityManager;

abstract class MetadataSelectBase extends SelectBase
{
    public function __construct(
        EntityManager $entityManager,
        SelectManagerFactory $selectManagerFactory,
        protected Metadata $metadata
    ) {
        parent::__construct($entityManager, $selectManagerFactory);
    }
}
