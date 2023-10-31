<?php
# vim: set tabstop=3:softtabstop=3:shiftwidth=3:noexpandtab

namespace Espo\Modules\HookedFormulas\Core\Formula\Functions;

use \Espo\Core\Exceptions\Error;
use \Espo\Core\Formula\Parser\Ast\Variable;
use \Espo\Core\Formula\Parser\Ast\Attribute;

class ConfigClearCacheType extends Config
{
    public function process(\StdClass $item)
    {
    	parent::clearCache();
    }
}
