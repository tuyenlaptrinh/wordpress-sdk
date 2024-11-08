<?php

declare(strict_types=1);

namespace Tuyenlaptrinh\WordPress\Requests;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator;
use stdClass;
use Tuyenlaptrinh\WordPress\Exceptions\BadRequestException;
use Tuyenlaptrinh\WordPress\Exceptions\CannotCreateException;
use Tuyenlaptrinh\WordPress\Exceptions\CannotCreateUserException;
use Tuyenlaptrinh\WordPress\Exceptions\CannotEditException;
use Tuyenlaptrinh\WordPress\Exceptions\CannotEditOthersException;
use Tuyenlaptrinh\WordPress\Exceptions\CannotUpdateException;
use Tuyenlaptrinh\WordPress\Exceptions\CannotViewUserException;
use Tuyenlaptrinh\WordPress\Exceptions\DuplicateTermSlugException;
use Tuyenlaptrinh\WordPress\Exceptions\ForbiddenException;
use Tuyenlaptrinh\WordPress\Exceptions\IncorrectPasswordException;
use Tuyenlaptrinh\WordPress\Exceptions\InvalidAuthorIdException;
use Tuyenlaptrinh\WordPress\Exceptions\InvalidParamException;
use Tuyenlaptrinh\WordPress\Exceptions\InvalidPostIdException;
use Tuyenlaptrinh\WordPress\Exceptions\InvalidPostPageNumberException;
use Tuyenlaptrinh\WordPress\Exceptions\InvalidUserIdException;
use Tuyenlaptrinh\WordPress\Exceptions\InvalidUserSlugException;
use Tuyenlaptrinh\WordPress\Exceptions\NoRouteException;
use Tuyenlaptrinh\WordPress\Exceptions\NotFoundException;
use Tuyenlaptrinh\WordPress\Exceptions\PostAlreadyTrashedException;
use Tuyenlaptrinh\WordPress\Exceptions\RestForbiddenException;
use Tuyenlaptrinh\WordPress\Exceptions\TermExistsException;
use Tuyenlaptrinh\WordPress\Exceptions\TermInvalidException;
use Tuyenlaptrinh\WordPress\Exceptions\UnauthorizedException;
use Tuyenlaptrinh\WordPress\Exceptions\UnexpectedValueException;
use Tuyenlaptrinh\WordPress\Exceptions\UnknownException;
use Tuyenlaptrinh\WordPress\Exceptions\UploadUnknownErrorException;
use Tuyenlaptrinh\WordPress\Exceptions\UserEmailExistsException;
use Tuyenlaptrinh\WordPress\Exceptions\UsernameExistsException;
use Tuyenlaptrinh\WordPress\Exceptions\WordPressException;
use Tuyenlaptrinh\WordPress\Exceptions\WpDieException;
use Tuyenlaptrinh\WordPress\Objects\WordPressError;
use Tuyenlaptrinh\WordPress\WordPress;

abstract class Request
{
    const VERSION = 'v2';

    /**
     * Create a new request instance.
     */
    public function __construct(
        protected readonly WordPress $app,
    ) {
        //
    }

    /**
     * @param  'get'|'post'|'patch'|'delete'  $method
     * @param  non-empty-string  $path
     * @param  array<string, mixed>  $options
     * @return ($method is 'delete' ? bool : ($expectArray is true ? array<int, stdClass> : stdClass))
     *
     * @throws UnexpectedValueException|WordPressException
     */
    protected function request(
        string $method,
        string $path,
        array $options = [],
        bool $expectArray = false,
    ): stdClass|array|bool {
        $http = $this->app
            ->http
            ->acceptJson()
            ->withOptions(['verify' => false])
            ->baseUrl(rtrim($this->app->url(), '/'))
            ->withoutVerifying()
            ->withoutRedirecting()
            ->withBasicAuth($this->app->username(), $this->app->password());

        if ($this->app->userAgent()) {
            $http->withUserAgent($this->app->userAgent());
        }

        if (isset($options['file']) && $options['file'] instanceof UploadedFile) {
            $http->attach(
                'file',
                $options['file']->getContent(),
                $options['file']->getClientOriginalName(),
            );

            unset($options['file']);
        }

        $response = $http->{$method}(
            $this->getUrl($path, $http),
            $options,
        );

        if (! ($response instanceof Response)) {
            $this->throwUnexpectedValueException(get_class($response) ?: '');
        }

        $payload = $response->object();

        if (! $response->successful()) {
            $this->toException(
                $payload ?: new stdClass(),
                $response->body(),
                $response->status(),
            );
        }

        if ($expectArray) {
            if (! is_array($payload)) {
                $this->throwUnexpectedValueException($response->body());
            }

            return $payload;
        }

        if (! ($payload instanceof stdClass)) {
            $this->throwUnexpectedValueException($response->body());
        }

        if ($method === 'delete') {
            return $response->successful();
        }

        return $payload;
    }

    public function getUrl(string $path, PendingRequest $http): string
    {
        $route = sprintf(
            '/wp/%s/%s',
            self::VERSION,
            ltrim($path, '/'),
        );

        if ($this->app->isPrettyUrl()) {
            return sprintf(
                '/%s/%s',
                trim($this->app->prefix(), '/'),
                ltrim($route, '/'),
            );
        }

        $http->withQueryParameters([
            'rest_route' => $route,
        ]);

        return '/';
    }

    /**
     * @throws WordPressException
     */
    protected function toException(stdClass $payload, string $message, int $status): void
    {
        if ($this->validate($payload)) {
            $error = WordPressError::from($payload);

            throw match ($error->code) {
                'duplicate_term_slug' => new DuplicateTermSlugException($error, $status),
                'existing_user_email' => new UserEmailExistsException($error, $status),
                'existing_user_login' => new UsernameExistsException($error, $status),
                'incorrect_password' => new IncorrectPasswordException($error, $status),
                'rest_already_trashed' => new PostAlreadyTrashedException($error, $status),
                'rest_cannot_create' => new CannotCreateException($error, $status),
                'rest_cannot_create_user' => new CannotCreateUserException($error, $status),
                'rest_cannot_edit' => new CannotEditException($error, $status),
                'rest_cannot_edit_others' => new CannotEditOthersException($error, $status),
                'rest_cannot_update' => new CannotUpdateException($error, $status),
                'rest_forbidden' => new RestForbiddenException($error, $status),
                'rest_invalid_author' => new InvalidAuthorIdException($error, $status),
                'rest_invalid_param' => new InvalidParamException($error, $status),
                'rest_no_route' => new NoRouteException($error, $status),
                'rest_post_invalid_id' => new InvalidPostIdException($error, $status),
                'rest_post_invalid_page_number' => new InvalidPostPageNumberException($error, $status),
                'rest_term_invalid' => new TermInvalidException($error, $status),
                'rest_upload_unknown_error' => new UploadUnknownErrorException($error, $status),
                'rest_user_cannot_view' => new CannotViewUserException($error, $status),
                'rest_user_invalid_id' => new InvalidUserIdException($error, $status),
                'rest_user_invalid_slug' => new InvalidUserSlugException($error, $status),
                'term_exists' => new TermExistsException($error, $status),
                'wp_die' => new WpDieException($error, $status),
                default => new UnknownException($error, $status),
            };
        }

        $error = WordPressError::from((object) [
            'code' => (string) $status,
            'message' => $message,
            'data' => (object) [],
        ]);

        throw match ($status) {
            400 => new BadRequestException($error),
            401 => new UnauthorizedException($error),
            403 => new ForbiddenException($error),
            404 => new NotFoundException($error),
            default => new UnknownException($error, $status),
        };
    }

    protected function validate(stdClass $data): bool
    {
        $file = realpath(
            sprintf('%s/../Schemas/exception.json', __DIR__),
        );

        if ($file === false) {
            return false;
        }

        $path = sprintf('file://%s', $file);

        $validator = new Validator();

        $validator->validate($data, ['$ref' => $path], Constraint::CHECK_MODE_NORMAL | Constraint::CHECK_MODE_VALIDATE_SCHEMA);

        return $validator->isValid();
    }

    /**
     * Throw UnexpectedValueException.
     *
     * @throws UnexpectedValueException
     */
    public function throwUnexpectedValueException(string $body = ''): void
    {
        throw new UnexpectedValueException(
            WordPressError::from((object) [
                'message' => sprintf('Unexpected value: %s', $body),
                'code' => '500',
                'data' => (object) [
                    'body' => $body,
                ],
            ]),
        );
    }
}
