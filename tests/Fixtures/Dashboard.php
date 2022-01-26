<?php

namespace HeadlessLaravel\Cards\Tests\Fixtures;

use HeadlessLaravel\Cards\Card;
use HeadlessLaravel\Cards\Cards;
use Illuminate\Support\Facades\Request;

class Dashboard extends Cards
{
    public function rules(): array
    {
        return [
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ];
    }

    public function cards(): array
    {
        return [
            Card::make('Total Users')
                ->value(function () {
                    return 50;
                }),

            Card::make('Total Orders')
                ->link('/orders')
                ->component('custom-component')
                ->value(function () {
                    return 150;
                }),

            Card::make('Total Accounts')
                ->props(['class' => 'bg-red-500'])
                ->span(5),

            Card::make('Restricted')
                ->can('viewAny'),

            Card::make('With Filters Value', 'filter_value_card')
                ->cache(5)
                ->value(function () {
                    return Request::input('from')
                        .Request::input('to', ' | fallback for to');
                }),
        ];
    }
}
