<?php

namespace HeadlessLaravel\Cards\Tests;

use HeadlessLaravel\Cards\Tests\Fixtures\Dashboard;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

class CardsControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('viewAny', function () {
            return false;
        });

        Route::prefix('api')->group(function () {
            Route::cards(Dashboard::class);
        });
    }

    public function test_endpoint_response_of_card_group()
    {
        $response = $this->get('api/cards/dashboard');

        $response
            ->assertJsonPath('data.0.key', 'total_users')
            ->assertJsonPath('data.0.title', 'Total Users')
            ->assertJsonPath('data.0.value', 50)
            ->assertJsonPath('data.0.endpoint', 'api/dashboard/total-users');

        $response
            ->assertJsonPath('data.1.key', 'total_orders')
            ->assertJsonPath('data.1.title', 'Total Orders')
            ->assertJsonPath('data.1.value', 150)
            ->assertJsonPath('data.1.link', '/orders')
            ->assertJsonPath('data.1.endpoint', 'api/dashboard/total-orders')
            ->assertJsonPath('data.1.component', 'custom-component');
    }

    public function test_endpoint_response_of_single_card()
    {
        $this->get('/api/cards/dashboard/total-users')
            ->assertJsonPath('key', 'total_users')
            ->assertJsonPath('title', 'Total Users')
            ->assertJsonPath('value', 50)
            ->assertJsonPath('endpoint', 'api/dashboard/total-users');
    }

    public function test_null_values_get_removed_from_responses()
    {
        $json = $this->get('/api/cards/dashboard/total-accounts')
            ->assertJsonPath('key', 'total_accounts')
            ->assertJsonPath('span', 5)
            ->assertJsonPath('props.class', 'bg-red-500')
            ->json();

        $this->assertArrayNotHasKey('value', $json);
        $this->assertArrayNotHasKey('component', $json);
        $this->assertArrayNotHasKey('link', $json);
    }

    public function test_ability_check()
    {
        $response = $this->get('/api/cards/dashboard')->json('data');

        $this->assertNotContains('restricted', collect($response)->pluck('key'));

        $this->get('/api/cards/dashboard/restricted')->assertForbidden();
    }

    public function test_passing_invalid_query_string_parameters()
    {
        $this->get('/api/cards/dashboard?from=555')
            ->assertSessionHasErrors(['from']);

        $this->get('/api/cards/dashboard/total-accounts?from=555')
            ->assertSessionHasErrors(['from']);
    }

    public function test_accessing_filter_values_within_value_callbacks()
    {
        $this->get('/api/cards/dashboard/filter-value-card?from=12/01/2021')
            ->assertSessionDoesntHaveErrors(['from'])
            ->assertJsonPath('value', '12/01/2021 | fallback for to');
    }

    public function test_caching_filter_values_within_value_callbacks()
    {
        $this->get('/api/cards/dashboard/filter-value-card?from=12/01/2021')
            ->assertJsonPath('value', '12/01/2021 | fallback for to');

        $this->travel(4)->seconds();

        $this->get('/api/cards/dashboard/filter-value-card?from=12/02/2021')
            ->assertJsonPath('value', '12/01/2021 | fallback for to'); // previous value

        $this->travel(2)->seconds();

        $this->get('/api/cards/dashboard/filter-value-card?from=12/02/2021')
            ->assertJsonPath('value', '12/02/2021 | fallback for to'); // new value
    }
}
