<?php

namespace App\Interfaces;

interface UpdateJobServiceInterface
{
    public function updateJob($job, $data, $cuser);
}