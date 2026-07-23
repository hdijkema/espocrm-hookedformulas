<?php

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions\Base;

use Espo\Core\Select\SelectManagerFactory;
use Espo\ORM\EntityManager;

abstract class SelectBase extends EntityManagerBase
{
    public function __construct(
        EntityManager $entityManager,
        protected SelectManagerFactory $selectManagerFactory
    ) {
        parent::__construct($entityManager);
    }
}
