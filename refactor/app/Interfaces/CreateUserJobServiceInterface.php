<?php

namespace App\Interfaces;

interface CreateUserJobServiceInterface
{
    public function createUsersJobs($user, $data);
}