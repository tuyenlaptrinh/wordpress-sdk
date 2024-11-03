<?php

declare(strict_types=1);

namespace Tuyenlaptrinh\WordPress\Exceptions;

use Tuyenlaptrinh\WordPress\Objects\WordPressError;

class TermExistsException extends WordPressException
{
    public $data;
    public function __construct(WordPressError $error)
    {
        parent::__construct($error, 400);
        $this->data = $error->data;
    }

    public function getData()
    {
        return $this->data;
    }
}
