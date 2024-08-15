<?php

namespace Suzunone\Hibana\Contracts;

use Generator;

interface StaticSiteGenerator
{
    /**
     * * @return \Generator<\Suzunone\Hibana\Contracts\StaticSiteFactory>
     */
    public function execute(): Generator;
}
