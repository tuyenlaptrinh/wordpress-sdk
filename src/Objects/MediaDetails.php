<?php

declare(strict_types=1);

namespace Tuyenlaptrinh\WordPress\Objects;

use stdClass;

class MediaDetails extends WordPressObject
{
    public int $width;

    public int $height;

    public string $file;

    public ?int $filesize = null;

    public stdClass $sizes;

    public stdClass $image_meta;
}
