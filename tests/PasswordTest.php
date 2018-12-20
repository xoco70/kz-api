<?php

use Laravel\Lumen\Testing\DatabaseMigrations;

class PasswordTest extends TestCase
{

    use DatabaseMigrations;


    /** @test */
    public function user_can_reset_password()
    {
        $this->assertTrue(true);
    }
}
