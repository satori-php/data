<?php

/**
 * @author    Yuriy Davletshin <yuriy.davletshin@gmail.com>
 * @copyright 2017 Yuriy Davletshin
 * @license   MIT
 */

declare(strict_types=1);

namespace Satori\Data;

/**
 * Extendable Data Object.
 */
class Xdo
{
    /**
     * @var callable Closure or invokable object that implements XSS protection.
     */
    protected $protect;

    /**
     * @var array<string, mixed> Contains properties.
     */
    protected $properties = [];

    /**
     * @var array<string, string> Contains property aliases.
     */
    protected $aliases = [];

    /**
     * @var array<string, callable> Contains custom methods.
     */
    protected $methods = [];

    /**
     * Constructor.
     *
     * @param callable              $protected
     *    The closure or invokable object that implements XSS protection.
     * @param array<string, mixed>  $properties  The properties.
     * @param array<string, string> $aliases     The property aliases.
     */
    public function __construct(callable $protect, array $properties, array $aliases = null)
    {
        $this->protect = $protect;
        $this->properties = $properties;
        $this->aliases = $aliases ?? [];
    }

    /**
     * Sets property aliases.
     *
     * @param array<string, string> $aliases The property aliases.
     */
    public function setAliases(array $aliases)
    {
        $this->aliases = $aliases;
    }

    /**
     * Returns a protected property value.
     *
     * @param string $name The unique name of the property.
     *
     * @throws \LogicException If the property is not defined.
     *
     * @return mixed
     */
    public function __get(string $name)
    {
        $name = $this->aliases[$name] ?? $name;
        if (array_key_exists($name, $this->properties)) {
            $value = $this->properties[$name];

            return ($this->protect)($value);
        }
        throw new \LogicException(sprintf('Property "%s" is not defined.', $name));
    }

    /**
     * Returns original property values.
     *
     * @return array
     */
    public function getRawData(): array
    {
        return $this->properties;
    }

    /**
     * Calls a custom method.
     *
     * @param string       $name The unique name of the custom method.
     * @param array<mixed> $args The arguments of the custom method.
     *
     * @throws \LogicException If the custom method is not defined.
     *
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        if (isset($this->methods[$name])) {
            return $this->methods[$name](...$args);
        }
        throw new \LogicException(sprintf('Custom method "%s" is not defined.', $name));
    }

    /**
     * Sets a custom method.
     *
     * @param string   $name           The unique name of the custom method.
     * @param \Closure $implementation The implementation of the custom method.
     */
    public function addMethod(string $name, \Closure $implementation)
    {
        $this->methods[$name] = \Closure::bind($implementation, $this, get_class());
    }
}
