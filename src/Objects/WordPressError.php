<?php

declare(strict_types=1);

namespace Tuyenlaptrinh\WordPress\Objects;

use stdClass;

class WordPressError extends WordPressObject
{
    public string $code;

    public string $message;

    public ?stdClass $data;
}
