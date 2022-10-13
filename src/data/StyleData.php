<?php

namespace markhuot\igloo\data;

class StyleData
{
    protected $data = [];

    protected $path;

    function __construct(array $data, string $path=null)
    {
        $this->data = $data;
        $this->path = $path;
    }

    function __isset($key)
    {
        return true;
    }

    function __get($key)
    {
        $path = implode('.', array_filter([$this->path, $key]));
        $value = $this->getPath($path);

        if (is_array($value)) {
            return new self($this->data, $path);
        }

        return $value ?? new self($this->data, $path);
    }

    function getPath($path)
    {
        $segments = explode('.', $path);
        $context = $this->data;

        foreach ($segments as $segment) {
            $context = $context[$segment] ?? null;
        }

        return $context ?? null;
    }

    function __toString()
    {
        return $this->getPath($this->path) ?? '';
    }
}
