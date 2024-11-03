<?php

declare(strict_types=1);

namespace Tuyenlaptrinh\WordPress\Objects;

class Tag extends WordPressObject
{
    public int $id;

    public int $count;

    public ?string $description;

    public string $link;

    public string $name;

    public string $slug;

    public string $taxonomy;
}
