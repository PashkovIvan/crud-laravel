<?php

namespace App\Domain\Common\Constants;

// problem: type hints?
class PaginationConstants
{
    public const DEFAULT_PER_PAGE = 15;
    public const MAX_PER_PAGE = 100;
    public const MIN_PER_PAGE = 1;
    public const RECENT_TASKS_LIMIT = 5;
}
