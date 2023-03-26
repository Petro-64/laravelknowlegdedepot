<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testBasicTest()
    {
        $response = $this->get('/');

        $response->assertStatus(302);
    }
    public function testBasicTest1()
    {
        $response = $this->get('/app');

        $response->assertStatus(200);
    }

    public function testBasicTest2()
    {
        $response = $this->get('/app');

        $response->assertStatus(200);
    }

    public function testBasicTest3()
    {
        $response = $this->get('/app/login');

        $response->assertStatus(200);
    }

    public function testBasicTest4()
    {
        $response = $this->get('/app/register');

        $response->assertStatus(200);
    }

    public function testBasicTest5()
    {
        $response = $this->get('/app/test');

        $response->assertStatus(200);
    }

    public function testBasicTest6()
    {
        $response = $this->get('/app/users');

        $response->assertStatus(200);
    }

    public function testBasicTest7()
    {
        $response = $this->post('/react/signup');

        $response->assertStatus(200);
    }
    ////
}
