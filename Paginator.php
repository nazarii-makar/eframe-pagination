<?php

namespace EFrame\Pagination;

use Closure;
use Illuminate\Support\Str;
use Illuminate\Pagination\Paginator as IlluminatePaginator;

class Paginator extends IlluminatePaginator
{
    /**
     * The current let resolver callback.
     *
     * @var \Closure
     */
    protected static $currentLetResolver;

    /**
     * The current limit resolver callback.
     *
     * @var \Closure
     */
    protected static $currentLimitResolver;

    /**
     * The current let being "viewed".
     *
     * @var int
     */
    protected $currentLet;

    /**
     * The query string variable used to store the let.
     *
     * @var string
     */
    protected $letName = 'let';

    /**
     * The query string variable used to store the limit.
     *
     * @var string
     */
    protected $perPageName = 'limit';

    /**
     * Create a new paginator instance.
     *
     * @param  mixed  $items
     * @param  int  $perPage
     * @param  int|null  $currentPage
     * @param  array  $options (path, query, fragment, pageName)
     * @return void
     */
    public function __construct($items, $perPage, $currentPage = null, array $options = [])
    {
        parent::__construct($items, $perPage, $currentPage, $options);

        if (in_array('currentLet', array_keys($options))) {
            $this->currentLet = $this->setCurrentLet($options['currentLet'], $this->letName);
        }
    }

    /**
     * Set the current let resolver callback.
     *
     * @param  \Closure  $resolver
     * @return void
     */
    public static function currentLetResolver(Closure $resolver)
    {
        static::$currentLetResolver = $resolver;
    }

    /**
     * Set the current limit resolver callback.
     *
     * @param  \Closure  $resolver
     * @return void
     */
    public static function currentLimitResolver(Closure $resolver)
    {
        static::$currentLimitResolver = $resolver;
    }

    /**
     * Resolve the current let or return the default value.
     *
     * @param  string  $letName
     * @param  int  $default
     * @return int
     */
    public static function resolveCurrentLet($letName = 'let', $default = 0)
    {
        if (isset(static::$currentLetResolver)) {
            return call_user_func(static::$currentLetResolver, $letName);
        }

        return $default;
    }

    /**
     * Resolve the current limit or return the default value.
     *
     * @param  string  $limitName
     * @param  int  $default
     * @return int
     */
    public static function resolveCurrentLimit($limitName = 'limit', $default = null)
    {
        if (isset(static::$currentLimitResolver)) {
            return call_user_func(static::$currentLimitResolver, $limitName);
        }

        return $default;
    }

    /**
     * Get the current let for the request.
     *
     * @param  int  $currentLet
     * @param  string  $letName
     * @return int
     */
    protected function setCurrentLet($currentLet, $letName)
    {
        $currentLet = $currentLet ?: static::resolveCurrentLet($letName);

        return $this->isValidLetNumber($currentLet) ? (int) $currentLet : 0;
    }

    /**
     * Determine if the given value is a valid let number.
     *
     * @param  int  $let
     * @return bool
     */
    protected function isValidLetNumber($let)
    {
        return $let >= 0 && filter_var($let, FILTER_VALIDATE_INT) !== false;
    }

    /**
     * Get the URL for a given page number.
     *
     * @param  int  $page
     * @return string
     */
    public function url($page)
    {
        if ($page <= 0) {
            $page = 1;
        }

        // If we have any extra query string key / value pairs that need to be added
        // onto the URL, we will put them in query string form and then attach it
        // to the URL. This allows for extra information like sortings storage.
        $parameters = [$this->pageName => $page, $this->perPageName => $this->perPage()];

        if (count($this->query) > 0) {
            $parameters = array_merge($this->query, $parameters);
        }

        return $this->path
            .(Str::contains($this->path, '?') ? '&' : '?')
            .http_build_query($parameters, '', '&')
            .$this->buildFragment();
    }

    /**
     * Get the current let.
     *
     * @return int
     */
    public function currentLet()
    {
        return $this->currentLet;
    }

    /**
     * Get the URL for the next page.
     *
     * @return string|null
     */
    public function nextPageUrl()
    {
        if ($this->hasMorePages()) {
            return $this->url($this->currentPage() + 1);
        }
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'current_page' => $this->currentPage(),
            'current_let'  => $this->currentLet(),
            'data' => $this->items->toArray(),
            'first_page_url' => $this->url(1),
            'from' => $this->firstItem(),
            'next_page_url' => $this->nextPageUrl(),
            'path' => $this->path,
            'per_page' => $this->perPage(),
            'prev_page_url' => $this->previousPageUrl(),
            'to' => $this->lastItem(),
        ];
    }
}
