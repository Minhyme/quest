<?php declare(strict_types = 1);

namespace Quest\Tests;

use Quest\ServiceProvider;
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\DB;

class PackageTest extends TestCase
{

    /**
     * Setup the test environment.
     *
     **/
    protected function setUp() : void
    {
        parent::setUp();

        app()['config']->set('database.default', 'mysql');
        app()['config']->set('database.connections.mysql', [
            'driver'         => 'mysql',
            'url'            => env('DATABASE_URL'),
            'host'           => env('DB_HOST', '127.0.0.1'),
            'port'           => env('DB_PORT', 3306),
            'database'       => env('DB_DATABASE', 'testing'),
            'username'       => env('DB_USERNAME', 'root'),
            'password'       => env('DB_PASSWORD', ''),
            'unix_socket'    => env('DB_SOCKET', ''),
            'charset'        => 'utf8mb4',
            'collation'      => 'utf8mb4_unicode_520_ci',
            'prefix'         => '',
            'prefix_indexes' => true,
            'strict'         => true,
            'engine'         => null,
        ]);

        (new ServiceProvider(app()))->boot();

        $this->loadMigrationsFrom(__DIR__ . '/../support/migrations');

        DB::table('users')->truncate();

        DB::table('users')->insert(['name' => 'John Doe', 'country' => 'United States']);
        DB::table('users')->insert(['name' => 'Jane Doe', 'country' => 'United Kingdom']);
        DB::table('users')->insert(['name' => 'Fred Doe', 'country' => 'France']);
        DB::table('users')->insert(['name' => 'William Doe', 'country' => 'Italy']);
    }



    /** @test */
    public function it_can_perform_a_fuzzy_search_and_receive_one_result()
    {
        $results = DB::table('users')
                 ->whereFuzzy('users.name', 'jad')
                 ->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Jane Doe', $results->first()->name);
    }



    /** @test */
    public function it_can_perform_a_fuzzy_search_and_receive_multiple_results()
    {
        $results = DB::table('users')
                 ->whereFuzzy('name', 'jd')
                 ->get();

        $this->assertCount(2, $results);
        $this->assertEquals('John Doe', $results[0]->name);
        $this->assertEquals('Jane Doe', $results[1]->name);
    }



    /** @test */
    public function it_can_perform_a_fuzzy_search_and_paginate_multiple_results()
    {
        $results = DB::table('users')
                 ->whereFuzzy('name', 'jd')
                 ->simplePaginate(1, ['*'], 'page', 1);

        $this->assertEquals('John Doe', $results->items()[0]->name);

        $results = DB::table('users')
                 ->whereFuzzy('name', 'jd')
                 ->simplePaginate(1, ['*'], 'page', 2);

        $this->assertEquals('Jane Doe', $results->items()[0]->name);
    }



    /** @test */
    public function it_can_perform_a_fuzzy_search_across_multiple_fields()
    {
        $results = DB::table('users')
                 ->whereFuzzy('name', 'jd')
                 ->whereFuzzy('country', 'uk')
                 ->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Jane Doe', $results[0]->name);
    }



    /** @test */
    public function it_can_order_a_fuzzy_search_by_one_field()
    {
        $results = DB::table('users')
                 ->whereFuzzy('name', 'jd')
                 ->whereFuzzy('country', 'un')
                 ->orderByFuzzy('country')
                 ->get();

        $this->assertCount(2, $results);
        $this->assertEquals('John Doe', $results[0]->name);
        $this->assertEquals('Jane Doe', $results[1]->name);
    }



    /** @test */
    public function it_can_order_a_fuzzy_search_by_multiple_fields()
    {
        $results = DB::table('users')
                 ->whereFuzzy('name', 'jd')
                 ->whereFuzzy('country', 'un')
                 ->orderByFuzzy(['name', 'country'])
                 ->get();

        $this->assertCount(2, $results);
        $this->assertEquals('John Doe', $results[0]->name);
        $this->assertEquals('Jane Doe', $results[1]->name);
    }



    /** @test */
    public function it_can_perform_an_eloquent_fuzzy_search()
    {
        $results = User::whereFuzzy('name', 'jad')
                 ->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Jane Doe', $results->first()->name);
    }
}
