<?php

namespace App\Interfaces;

interface GetUserJobServiceInterface
{
    public function getUsersJobs(int $user_id);

    public function getAllJobs($request, $limit = null);
}