<?php

use App\Category;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Tests\Concerns\AttachJwtToken;
use Illuminate\Http\Response as HttpResponse;

/**
 * List of User Test
 *
 * it_add_a_user_to_tournament_category()
 * it_removes_a_user_from_tournament_category()
 * you_must_own_tournament_or_be_superuser_to_add_or_remove_user_from_tournament
 * you_can_confirm_a_user
 *
 * User: juliatzin
 * Date: 10/11/2015
 * Time: 23:14
 */
class CategoryTest extends TestCase
{
    use DatabaseTransactions, AttachJwtToken; // it migrates but it doesn't seed

    protected $user, $users, $addUser, $editUser, $root, $simpleUser;


    public function setUp()
    {
        parent::setUp();
        $this->root = factory(User::class)->create(['role_id' => Config::get('constants.ROLE_SUPERADMIN')]);
        Auth::login($this->root);
    }

    /** @test */
    public function it_can_retrieve_categories_and_is_limited_to_10_results()
    {
        $this->json('GET', '/categories');
        $categories = json_decode($this->response->content(), true);
        $this->assertResponseOk();
        $this->assertTrue(count($categories) <= 10);
    }


    /** @test */
    public function it_can_add_a_new_category()
    {
        $category = factory(Category::class)->make();
        $this->json('POST', '/categories/', $category->toArray())
            ->assertResponseStatus(HttpResponse::HTTP_CREATED);
        $this->seeInDatabase('category', $category->toArray());

    }
}
