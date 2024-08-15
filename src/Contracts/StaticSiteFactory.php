<?php

namespace Suzunone\Hibana\Contracts;

interface StaticSiteFactory
{
    public function url(): string;

    public function savePath(): string;

}
