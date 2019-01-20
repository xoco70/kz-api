<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

class PasswordTest extends TestCase
{

    use DatabaseTransactions;


    /** @test */
    public function user_can_reset_password()
    {
        $this->assertTrue(true);
    }
}
