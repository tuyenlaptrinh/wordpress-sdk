<?php

declare(strict_types=1);

namespace Tuyenlaptrinh\WordPress\Facades;

use Illuminate\Support\Facades\Facade;
use Tuyenlaptrinh\WordPress\Requests\Category;
use Tuyenlaptrinh\WordPress\Requests\GeneralRequest;
use Tuyenlaptrinh\WordPress\Requests\Media;
use Tuyenlaptrinh\WordPress\Requests\Post;
use Tuyenlaptrinh\WordPress\Requests\PostRevision;
use Tuyenlaptrinh\WordPress\Requests\Site;
use Tuyenlaptrinh\WordPress\Requests\Tag;
use Tuyenlaptrinh\WordPress\Requests\User;

/**
 * @method static GeneralRequest request()
 * @method static User user()
 * @method static Post post()
 * @method static PostRevision postRevision()
 * @method static Category category()
 * @method static Tag tag()
 * @method static Media media()
 * @method static Site site()
 * @method static \Tuyenlaptrinh\WordPress\WordPress instance()
 * @method static string url()
 * @method static \Tuyenlaptrinh\WordPress\WordPress setUrl(string $url)
 * @method static string username()
 * @method static \Tuyenlaptrinh\WordPress\WordPress setUsername(string $username)
 * @method static string password()
 * @method static \Tuyenlaptrinh\WordPress\WordPress setPassword(string $password)
 * @method static string|null userAgent()
 * @method static \Tuyenlaptrinh\WordPress\WordPress withUserAgent(string $userAgent)
 * @method static string prefix()
 * @method static \Tuyenlaptrinh\WordPress\WordPress setPrefix(string $prefix)
 * @method static \Tuyenlaptrinh\WordPress\WordPress prettyUrl()
 * @method static bool isPrettyUrl()
 */
class WordPress extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'wordpress';
    }
}
