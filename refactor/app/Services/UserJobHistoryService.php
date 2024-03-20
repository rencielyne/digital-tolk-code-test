<?php

namespace App\Services;

use App\Exceptions\JobsExceptions\JobNotFoundException;
use App\Interfaces\UserJobHistoryServiceInterface;
use DTApi\Repository\BookingRepository;
use DTApi\Repository\UserRepository;

class UserJobHistoryService implements UserJobHistoryServiceInterface
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly UserRepository $userRepository,
    ){}

    /**
     * @param int $user_id
     * @return array
     */
    public function getUsersJobsHistory(int $user_id, int $page)
    {        
        $users = $this->getUsers($user_id);
        
        $userType = 'customer';
        $emergencyJobs = array();
        $normalJobs = array();
        $numPages = 0;
        $pageNum = 0;

        $jobs = $this->getJobsHistory($users);

        if (isset($jobs) === false) {
            throw new JobNotFoundException('Job not found');
        }

        if ($users->is('translator')) {
            $userType = 'translator';
            $normalJobs = $jobs;
            $totalJobs = $jobs->total();
            $numPages = ceil($totalJobs / 15);
            $pageNum = $page;
        }
        
        return [
            'emergencyJobs' => $emergencyJobs, 
            'normalJobs'    => $normalJobs, 
            'jobs'          => $jobs, 
            'users'         => $users, 
            'userType'      => $userType, 
            'numPages'      => $numPages, 
            'pageNum'       => $pageNum
        ];
    }

    private function getUsers(int $user_id)
    {
        $users = $this->userRepository->findUser($user_id);

        if (isset($users) === false) {
            throw new NotFoundResourceException('User not found');
        }

        return $users;
    }

    private function getJobsHistory(Model $users, int $page)
    {
        $status = ['completed', 'withdrawbefore24', 'withdrawafter24', 'timedout'];

        if ($users->is('customer')) {
            return $this->bookingRepository->getCustomerJobs($users, $status, true);
        } 

        return $this->bookingRepository->getTranslatorJobsHistoric($users->id, $page);
    }
}