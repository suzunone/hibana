<?php

namespace Suzunone\Hibana\Factories;

use Suzunone\Hibana\Contracts\StaticSiteFactory;

class ExampleStaticSiteFactory implements StaticSiteFactory
{

    public function url(): string
    {
        return '/';
    }

    public function savePath(): string
    {
        return '/index.html';
    }
}
