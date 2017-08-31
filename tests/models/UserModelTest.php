<?php

class UserModelTest extends TestCase
{

    public function test_public_contact()
    {
        $user = App\User::first();

        $this->assertTrue($user->FID);
    }
}