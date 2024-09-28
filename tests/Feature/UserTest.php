<?php

use App\Models\User;
use function Pest\Laravel\actingAs;

test('admin user can access users list page and see users', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get(route('users.index'))
        ->assertStatus(200)
        ->assertSee($admin->email);
});

test('non-admin user cannot access users list page', function () {
    $user = User::factory()->user()->create();

    actingAs($user)
        ->get(route('users.index'))
        ->assertStatus(403);
});

test('admin user can create users', function () {
    $admin = User::factory()->admin()->create();

    actingAs($admin)
        ->get(route('users.create'))
        ->assertStatus(200);

    $newUser = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'johndoe@example.com',
        'password' => 'password',
    ];
    actingAs($admin)
        ->post(route('users.store'), $newUser)
        ->assertRedirect();

    $latestUser = User::latest('id')->first();
    expect($latestUser)
        ->first_name->toBe($newUser['first_name'])
        ->last_name->toBe($newUser['last_name'])
        ->email->toBe($newUser['email']);
});

test('non-admin user cannot create users', function () {
    $user = User::factory()->user()->create();

    actingAs($user)
        ->get('/users/create')
        ->assertStatus(403);

    actingAs($user)
        ->post(route('users.store'), [])
        ->assertStatus(403);
});

test('creating user fails with invalid data', function () {
    $admin = User::factory()->admin()->create();

    $newUser = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        // no email provided
        'password' => 'password',
    ];
    actingAs($admin)
        ->post(route('users.store'), $newUser)
        ->assertRedirect();

    $latestUser = User::latest('id')->first();
    expect($latestUser)
        ->first_name->not->toBe($newUser['first_name'])
        ->last_name->not->toBe($newUser['last_name']);
});

test('admin can update users', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->user()->create();

    actingAs($admin)
        ->put(route('users.update', $user), [
            'first_name' => 'John Updated',
            'last_name' => 'Doe',
            'email' => 'johndoe@example.com',
        ])
        ->assertRedirect();

    $updatedUser = User::find($user->id);
    expect($updatedUser)
        ->first_name->toBe('John Updated')
        ->last_name->toBe('Doe');
});

test('non-admin user cannot update users', function () {
    $user = User::factory()->user()->create();
    $otherUser = User::factory()->user()->create();

    actingAs($user)
        ->get(route('users.edit', $otherUser))
        ->assertStatus(403);

    actingAs($user)
        ->put(route('users.update', $otherUser))
        ->assertStatus(403);
});

test('admin can delete user', function () {
    $admin = User::factory()->admin()->create();
    $user = User::factory()->user()->create();

    actingAs($admin)
        ->delete(route('users.destroy', $user))
        ->assertRedirect(route('users.index'));

    $this->assertSoftDeleted($user);
});

test('non-admin user cannot delete users', function () {
    $user = User::factory()->user()->create();
    $otherUser = User::factory()->user()->create();

    actingAs($user)
        ->delete(route('users.destroy', $otherUser))
        ->assertStatus(403);
});
