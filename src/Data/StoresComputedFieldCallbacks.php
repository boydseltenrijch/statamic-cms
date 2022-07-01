<?php

namespace Statamic\Data;

use Closure;
use Exception;
use Statamic\Support\Str;

trait StoresComputedFieldCallbacks
{
    protected $computedFieldCallbacks;
    protected $scopeComputedFieldCallbacks = false;

    public function computed(...$args)
    {
        $numArgsRequired = $this->scopeComputedFieldCallbacks ? 3 : 2;

        if (func_num_args() !== $numArgsRequired) {
            throw new Exception("Number of arguments required: {$numArgsRequired}");
        }

        func_num_args() === 3
            ? $this->setScopedComputedCallback(...$args)
            : $this->setComputedCallback(...$args);
    }

    protected function setComputedCallback(string $field, Closure $callback)
    {
        $this->computedFieldCallbacks[$field] = $callback;
    }

    protected function setScopedComputedCallback(string $scope, string $field, Closure $callback)
    {
        $this->computedFieldCallbacks["$scope.$field"] = $callback;
    }

    public function getComputedCallbacks($fieldPrefix = null)
    {
        $fieldPrefix = Str::ensureRight((string) $fieldPrefix, '.');

        return collect($this->computedFieldCallbacks)->keyBy(function ($callback, $fieldPath) {
            return collect(explode('.', $fieldPath))->last();
        });
    }
}