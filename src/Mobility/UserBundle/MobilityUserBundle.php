<?php

namespace Mobility\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class MobilityUserBundle extends Bundle
{
    public function getParent() {
        return 'FOSUserBundle';
    }
}
