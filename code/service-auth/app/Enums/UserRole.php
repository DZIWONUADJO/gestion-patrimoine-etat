<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'ADMIN';
    case Manager = 'GESTIONNAIRE';
    case ServiceChief = 'CHEF_SERVICE';
}
