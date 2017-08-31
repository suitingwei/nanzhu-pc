<?php

namespace App\Exceptions;

use Exception;

class FriendException extends Exception
{
    const USER_HAD_BEEN_FRIEND         = 10;
    const PHONE_OWNER_NOT_REGISTER     = 20;
    const FRIEND_APPLICATION_APPROVING = 30;


}
