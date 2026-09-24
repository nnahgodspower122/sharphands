<?php

namespace App\Enums;

enum UserRole: string
{
    case User = 'user';
    case Worker = 'worker';
    case Admin = 'admin';
}
