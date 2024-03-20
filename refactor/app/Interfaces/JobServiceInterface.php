<?php

namespace App\Interfaces;

interface JobServiceInterface
{
    public function storeJobEmail(array $data);
    
    public function acceptJob($data, $user);

    public function acceptJobWithId($job_id, $cuser);

    public function cancelJobAjax($data, $user);

    public function endJob($post_data);

    public function getPotentialJobs($cuser);

    public function customerNotCall($post_data);
}