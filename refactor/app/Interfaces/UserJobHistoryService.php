<?php

namespace App\Interfaces;

interface UserJobHistoryServiceInterface
{
    public function getUsersJobsHistory(int $user_id, int $page);
}