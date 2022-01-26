<?php

namespace HeadlessLaravel\Cards\Http\Controllers;

use HeadlessLaravel\Cards\Manager;

class CardsController
{
    public $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function index(): array
    {
        $cards = $this->manager->current();

        $cards->validate();

        return $cards->resolve();
    }

    public function show($key): array
    {
        $cards = $this->manager->current();

        return $cards->resolve($key);
    }
}
