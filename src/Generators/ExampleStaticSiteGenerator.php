<?php

namespace Suzunone\Hibana\Generators;

use Generator;
use Suzunone\Hibana\Factories\ExampleStaticSiteFactory;
use Suzunone\Hibana\Contracts\StaticSiteGenerator;

class ExampleStaticSiteGenerator implements StaticSiteGenerator
{
    /**
     * @return \Generator<\Suzunone\Hibana\Contracts\StaticSiteFactory>
     */
    public function execute(): Generator
    {
        yield new ExampleStaticSiteFactory();
    }
}
