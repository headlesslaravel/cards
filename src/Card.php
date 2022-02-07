<?php

namespace HeadlessLaravel\Cards;

use HeadlessLaravel\Metrics\Metric;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class Card
{
    public $key;

    public $value;

    public $cache = 0;

    public $title;

    public $component;

    public $link;

    public $span;

    public $props;

    public $rules;

    public $allowed = true;

    public function init($title, $key): self
    {
        $this->title = $title;

        $this->key = is_null($key) ? Str::snake($title) : $key;

        return $this;
    }

    public static function make(string $title, $key = null): self
    {
        return (new self())->init($title, $key);
    }

    public function value(callable $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function component($component): self
    {
        $this->component = $component;

        return $this;
    }

    public function as($component): self
    {
        return $this->component($component);
    }

    public function link($link): self
    {
        $this->link = $link;

        return $this;
    }

    public function span($span): self
    {
        $this->span = $span;

        return $this;
    }

    public function props(array $props): self
    {
        $this->props = $props;

        return $this;
    }

    public function rules($rules): self
    {
        $this->rules = $rules;

        return $this;
    }

    public function view($name): self
    {
        return $this->value(function () use ($name) {
            return View::make($name);
        });
    }

    public function http($url, $path = null): self
    {
        return $this->value(function () use ($url, $path) {
            return Http::get($url)->json($path);
        });
    }

    public function can($ability, $arguments = []): self
    {
        $this->allowed = Gate::allows($ability, $arguments);

        return $this;
    }

    public function cache(int $seconds): self
    {
        $this->cache = $seconds;

        return $this;
    }

    public function getCachedValue()
    {
        if (! $this->cache) {
            return $this->resolveClosureValue();
        }

        return Cache::remember('cards.'.$this->key, $this->cache, function () {
            return $this->resolveClosureValue();
        });
    }

    protected function resolveClosureValue(): mixed
    {
        $value = value($this->value);

        if ($value instanceof Metric) {
            return $value->render();
        }

        return $value;
    }

    public function resolve(): array
    {
        $card = $this->toArray();

        $card = array_merge($card, [
            'cardKey' => Str::slug($this->key),
            'value' => $this->getCachedValue(),
        ]);

        return array_filter($card);
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'title' => $this->title,
            'props' => $this->props,
            'component' => $this->component,
            'span' => $this->span,
            'link' => $this->link,
            'allowed' => $this->allowed,
        ];
    }
}
