<?php

namespace HeadlessLaravel\Cards;

use HeadlessLaravel\Cards\Http\Controllers\CardsController;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

class Manager
{
    protected $cards = [];

    public $prefix;

    public function register(Router $router, $handler)
    {
        $prefix = $router->getLastGroupPrefix();

        $key = app($handler)->key();

        $this->cards[$key] = [
            'handler' => $handler,
            'prefix'  => $prefix,
        ];

        $root = config('headless-cards.root', 'cards');

        $router->addRoute(['GET', 'HEAD'], "$root/$key", [CardsController::class, 'index'])
            ->setDefaults(['key' => $key])
            ->name("cards.$key.index");

        $router->addRoute(['GET', 'HEAD'], "$root/$key/{cardKey}", [CardsController::class, 'show'])
            ->setDefaults(['key' => $key])
            ->name("cards.$key.show");
    }

    public function current(): Cards
    {
        $key = Route::current()->parameter('key');

        return $this->get($key);
    }

    public function get($key): Cards
    {
        if ($handler = Arr::get($this->cards, "$key.handler")) {
            return app($handler);
        }

        abort(500, "No card group with key: $key");
    }

    public function prefix($key)
    {
        return Arr::get($this->cards, "$key.prefix");
    }
}
