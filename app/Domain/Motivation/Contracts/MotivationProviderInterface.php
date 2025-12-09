<?php

namespace App\Domain\Motivation\Contracts;

use App\Domain\Motivation\Enums\MotivationType;

interface MotivationProviderInterface
{
    public function generate(MotivationType $type): ?string;
}

