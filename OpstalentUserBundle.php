<?php

namespace Opstalent\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class OpstalentUserBundle
 * @package OpstalentUserBundle
 */
class OpstalentUserBundle extends Bundle
{
    /**
     * @return string
     */
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
