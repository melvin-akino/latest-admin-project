<?php

namespace Tests\Feature;

use App\Models\AdminUser;
use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\{DB, Auth};
use Laravel\Passport\ClientRepository;

class AdminAccountTest extends TestCase
{
    use WithFaker;

    protected $followRedirects = false;

    public $user;

    public function initialUser()
    {
        $clientRepository = new ClientRepository();
        $client = $clientRepository->createPersonalAccessClient(
            null, 'Test Personal Access Client', env('MIX_API_URL')
        );

        DB::table('oauth_personal_access_clients')
            ->insert([
               'client_id'  => $client->id,
               'created_at' => Carbon::now(),
               'updated_at' => Carbon::now(),
           ]);

        $data = $this->data();
        $user = new AdminUser([
            'name'                  => $data['name'],
            'email'                 => $data['email'],
            'password'              => bcrypt($data['password'])
        ]);
        $user->save();

        $response = $this->post('/api/login', [
            'email'    => $data['email'],
            'password' => $data['password']
        ]);

        $this->loginJsonResponse = json_decode($response->getContent(), false);
    }

    /** Faker generated data */
    protected function data()
    {
        return [
            'email'                     => $this->faker->email,
            'password'                  => 'secret',
            'password_confirmation'     => 'secret',
            'name'                      => $this->faker->lexify('??????????'),
        ];
    }
}
