<?php

namespace DTApi\Repository;

use DTApi\Repository\BaseRepository;

class DistanceRepository extends BaseRepository
{
    public function __construct(Distance $model)
    {
        parent::__construct($model);
    }

    public function updateDistance($jobid, $distance, $time)
    {
        return Distance::where('job_id', '=', $jobid)->update(['distance' => $distance, 'time' => $time]);
    }
}