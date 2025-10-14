<?php

use App\Models\Client;
use App\Models\User;

use function Pest\Laravel\actingAs;

test('admin user can view clients page', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get(route('clients.index'))
        ->assertStatus(200);
});

test('admin user can view create clients page', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get(route('clients.create'))
        ->assertStatus(200);
});

test('admin user can create clients', function () {
    $admin = User::factory()->admin()->create();

    $newClient = [
        'contact_name'          => "john doe",
        'contact_email'         => 'john.doe@gmail.com',
        'contact_phone_number'  => '623487234',
        'company_name'          => 'Foo company',
        'company_address'       => 'Foo address',
        'company_city'          => 'buea',
        'company_zip'           => '45234123',
        'company_vat'           => 12,
    ];

    actingAs($admin)
        ->post(route('clients.store'), $newClient)
        ->assertRedirect();

    $latestClient = Client::latest()->first();
    expect($latestClient)
        ->contact_name->toBe($newClient['contact_name'])
        ->contact_email->toBe($newClient['contact_email'])
        ->contact_phone_number->toBe($newClient['contact_phone_number'])
        ->company_name->toBe($newClient['company_name'])
        ->company_address->toBe($newClient['company_address'])
        ->company_city->toBe($newClient['company_city'])
        ->company_zip->toBe($newClient['company_zip'])
        ->company_vat->toBe($newClient['company_vat']);
});


test('admin user can edit clients', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    $updatedClient = [
        'contact_name'          => "john doe",
        'contact_email'         => 'john.doe@gmail.com',
        'contact_phone_number'  => '623487234',
        'company_name'          => 'Foo company',
        'company_address'       => 'Foo address',
        'company_city'          => 'buea',
        'company_zip'           => '45234123',
        'company_vat'           => 12,
    ];

    actingAs($admin)
        ->put(route('clients.update', $client), $updatedClient)
        ->assertRedirect();

    $latestClient = Client::latest()->first();
    expect($latestClient)
        ->contact_name->toBe($updatedClient['contact_name'])
        ->contact_email->toBe($updatedClient['contact_email'])
        ->contact_phone_number->toBe($updatedClient['contact_phone_number'])
        ->company_name->toBe($updatedClient['company_name'])
        ->company_address->toBe($updatedClient['company_address'])
        ->company_city->toBe($updatedClient['company_city'])
        ->company_zip->toBe($updatedClient['company_zip'])
        ->company_vat->toBe($updatedClient['company_vat']);
});

test('admin user can delete clients', function () {
    $admin = User::factory()->admin()->create();
    $client = Client::factory()->create();

    actingAs($admin)
        ->delete(route('clients.destroy', $client))
        ->assertRedirect(route('clients.index'));

    $this->assertSoftDeleted($client);
});

test('non admin user cannot delete clients', function () {
    $user = User::factory()->user()->create();
    $client = Client::factory()->create();

    actingAs($user)
        ->delete(route('clients.destroy', $client))
        ->assertStatus(403);
});

test('non admin user can not edit clients', function () {
    $client = Client::factory()->create();

    $updatedClient = [
        'contact_name'          => "john doe",
        'contact_email'         => 'john.doe@gmail.com',
        'contact_phone_number'  => '623487234',
        'company_name'          => 'Foo company',
        'company_address'       => 'Foo address',
        'company_city'          => 'buea',
        'company_zip'           => '45234123',
        'company_vat'           => 12,
    ];

    $this->get(route('clients.edit', $client))
        ->assertRedirect(route('login'));

    $this->put(route('clients.update', $client), $updatedClient)
        ->assertRedirect(route('login'));
});

test('non admin user can not create clients', function () {

    $newClient = [
        'contact_name'          => "john doe",
        'contact_email'         => 'john.doe@gmail.com',
        'contact_phone_number'  => '623487234',
        'company_name'          => 'Foo company',
        'company_address'       => 'Foo address',
        'company_city'          => 'buea',
        'company_zip'           => '45234123',
        'company_vat'           => 12,
    ];

    $this->get(route('clients.create'))
        ->assertRedirect(route('login'));

    $this->post(route('clients.store'), $newClient)
        ->assertRedirect(route('login'));
});

test('admin user can not create clients with invalid data', function () {
    $admin = User::factory()->admin()->create();

    $newClient = [
        'contact_name'          => "john doe",
        'contact_email'         => 'john.doe@gmail.com',
        'contact_phone_number'  => '623487234',

        'company_address'       => 'Foo address',
        'company_city'          => 'buea',
        'company_zip'           => '45234123',
        'company_vat'           => 12,
    ];

    actingAs($admin)
        ->post(route('clients.store'), $newClient)
        ->assertRedirect()
        ->assertSessionHasErrors([
            'company_name' => 'The company name field is required.',
        ]);
});
