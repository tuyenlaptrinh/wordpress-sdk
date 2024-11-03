<?php

declare(strict_types=1);

namespace Tuyenlaptrinh\WordPress\Exceptions;

use Tuyenlaptrinh\WordPress\Objects\WordPressError;

class BadRequestException extends WordPressException
{
    public function __construct(WordPressError $error)
    {
        parent::__construct($error, 400);
    }
}
