<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Http\Response as HttpResponse;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginTest extends TestCase
{

    use DatabaseTransactions;
    protected $initialTournamentNum = 6;
    protected $defaultPagintation = 25;


    /** @test */
    public function cant_access_route_without_token()
    {

        $response = $this->call('GET', '/tournaments');
        dd($response);
        // I should be blocked
        $this->assertEquals(HttpResponse::HTTP_UNAUTHORIZED, $response->status());
    }

    /**
     * User may want to access the main admin page.
     * For this, they will pass a JWT token
     */
    /** @test */
    public function getMainAuthenticated()
    {
        $credentials = JWTAuth::attempt(['email' => 'superuser@kendozone.dev', 'password' => 'superuser@kendozone.dev']);

        // as a user, I try to access the admin panels without a JWT token
        $response = $this->call(
            'GET',
            '/tournaments',
            [], //parameters
            [], //cookies
            [], // files
            ['HTTP_Authorization' => 'Bearer ' . $credentials], // server
            []
        );
        // I should be accepted
        $this->assertEquals(HttpResponse::HTTP_OK, $response->status());
    }

    /**
     * User may want to login, but using wrong credentials.
     * This route should be free for all unauthenticated users.
     * Users should be warned when login fails
     */
    /** @test */
    public function LoginWithWrongData()
    {
        // as a user, I wrongly type my email and password
        $data = ['email' => 'email', 'password' => 'password'];
        // and I submit it to the login api
        $response = $this->call('POST', '/auth/login', $data);
        // I shouldnt be able to login with wrong data
        $this->assertEquals(HttpResponse::HTTP_UNAUTHORIZED, $response->status());
    }

    /**
     * User may want to login.
     * This route should be free for all unauthenticated users.
     * User should receive an JWT token
     */
    /** @test */
    public function LoginSuccesfull()
    {
        // as a user, I wrongly type my email and password
        $data = ['email' => 'superuser@kendozone.dev', 'password' => 'superuser@kendozone.dev'];
        // and I submit it to the login api
        $response = $this->call('POST', '/auth/login', $data);
        // I should be able to login
        $this->assertEquals(HttpResponse::HTTP_ACCEPTED, $response->status());
        // assert there is a TOKEN on the response
        $content = json_decode($response->getContent());
        $this->assertObjectHasAttribute('token', $content);
        $this->assertNotEmpty($content->token);
    }
}
