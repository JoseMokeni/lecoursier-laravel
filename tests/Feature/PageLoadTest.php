<?php

it('loads the home page')
    ->get('/')
    ->assertStatus(200)
    ->assertViewIs('pages.landing');

it('loads the privacy page')
    ->get('/privacy-policy')
    ->assertStatus(200)
    ->assertViewIs('pages.privacy');

it('loads the login page')
    ->get('/login')
    ->assertStatus(200)
    ->assertViewIs('pages.login');

it('loads the register page')
    ->get('/register')
    ->assertStatus(200)
    ->assertViewIs('pages.register');

it('loads the tenant inactive error page')
    ->get('/errors/tenant-inactive')
    ->assertStatus(200)
    ->assertViewIs('errors.tenant-inactive');

it('loads the tenant required error page')
    ->get('/errors/tenant-required')
    ->assertStatus(200)
    ->assertViewIs('errors.tenant-required');
