<?php

namespace HeadlessLaravel\Cards;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class Cards
{
    public $wrap = 'data';

    protected $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function key(): string
    {
        return Str::of(class_basename(static::class))->snake();
    }

    public function cards(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function validate()
    {
        if (! $this->authorize()) {
            throw new AuthorizationException();
        }

        return Validator::make(
            Request::all(),
            $this->rules()
        )->validate();
    }

    public function guessEndpointName()
    {
        return Str::of(class_basename($this))
            ->snake()
            ->slug();
    }

    public function get($key): array
    {
        $key = str_replace('-', '_', $key);

        foreach ($this->cards() as $card) {
            if ($card->key === $key) {
                if (! $card->allowed) {
                    throw new AuthorizationException();
                }

                return $this->resolveCard($card);
            }
        }

        return [];
    }

    public function resolve($key = null): array
    {
        if ($key) {
            return $this->get($key);
        }

        $cards = [];

        foreach ($this->cards() as $card) {
            if ($card->allowed) {
                $cards[] = $this->resolveCard($card);
            }
        }

        $cards = array_values($cards);

        if ($this->wrap) {
            return [$this->wrap => $cards];
        }

        return $cards;
    }

    public function resolveCard($card)
    {
        $card = $card->resolve();

        $endpoint = $this->key().'/'.$card['cardKey'];

        if ($prefix = $this->manager->prefix($this->key())) {
            $endpoint = "$prefix/$endpoint";
        }

        $card['endpoint'] = $endpoint;

        return $card;
    }

    public static function make(): array
    {
        return app(static::class)->resolve();
    }
}
