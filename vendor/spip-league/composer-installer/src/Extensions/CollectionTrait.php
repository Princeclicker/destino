<?php

namespace SpipLeague\Composer\Extensions;

/**
 * Implémentation concrète de \Countable, \Iterator, \JsonSerializable, \ArrayAccess.
 *
 * @since 0.7.0
 */
trait CollectionTrait
{
    protected int $position = 0;

    /**
     * @var string[]
     */
    protected array $keys = [];

    /**
     * @var array<string,SpecificationInterface>
     */
    protected array $collection = [];

    public function count(): int
    {
        return \count($this->collection);
    }

    public function jsonSerialize(): mixed
    {
        return $this->collection;
    }

    /**
     * @codeCoverageIgnore
     */
    public function current(): SpecificationInterface
    {
        return $this->collection[$this->keys[$this->position]];
    }

    /**
     * @codeCoverageIgnore
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @codeCoverageIgnore
     */
    public function valid(): bool
    {
        return isset($this->keys[$this->position]);
    }

    /**
     * @codeCoverageIgnore
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @codeCoverageIgnore
     */
    public function key(): string
    {
        return $this->keys[$this->position];
    }

    public function offsetExists(mixed $offset): bool
    {
        return isset($this->collection[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->collection[$offset] ?? null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (!$value instanceof SpecificationInterface) {
            throw new InvalidSpecificationException('A collection must only contain valid specifications.', 3);
        }

        if ($offset === null || !\is_string($offset)) {
            $offset = $value->getPrefix();
        }

        $this->collection[$offset] = $value;
        $this->keys = \array_keys($this->collection);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->collection[$offset]);
        $this->keys = \array_keys($this->collection);
    }
}
