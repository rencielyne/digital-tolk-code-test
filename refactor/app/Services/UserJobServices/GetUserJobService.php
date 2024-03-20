<?php

namespace App\Services\UserJobService;

use App\Exceptions\JobsExceptions\JobNotFoundException;
use App\Interfaces\GetUserJobServiceInterface;
use DTApi\Repository\BookingRepository;
use DTApi\Repository\UserRepository;

class GetUserJobService implements GetUserJobServiceInterface
{
    public function __construct(
        private readonly BookingRepository $bookingRepository,
        private readonly UserRepository $userRepository,
    ){}

    /**
     * @param int $user_id
     * @return array
     */
    public function getUsersJobs(int $user_id)
    {
        $users = $this->getUsers($user_id);

        $userType = $users->is('customer') ? 'customer' : 'translator';
        $emergencyJobs = array();
        $normalJobs = array();

        $jobs = $this->getJobs($users);

        if (isset($jobs) === false) {
            throw new JobNotFoundException('Job not found');
        }
        
        $emergencyJobs = $this->getEmergencyJobs($jobs);
        $normalJobs = $this->getNormalJobs($jobs);
        
        return [
            'emergencyJobs' => $emergencyJobs, 
            'normalJobs'    => $normalJobs, 
            'users'         => $users, 
            'userType'      => $userType
        ];
    }

    public function getAllJobs($request, $limit = null)
    {
        $requestData = $request->all();
        $user = $request->__authenticatedUser->consumer_type;
        $consumerType = $user->consumer_type;

        if ($user && $user->user_type == env('SUPERADMIN_ROLE_ID')) {
           return $this->bookingRepository->getAllJobsForSuperAdmin($requestData, $limit);
        }

        return $this->bookingRepository->getAllJobs($requestData, $limit, $consumerType);
    }

    private function getUsers(int $user_id)
    {
        $users = $this->userRepository->findUser($user_id);

        if (isset($users) === false) {
            throw new NotFoundResourceException('User not found');
        }

        return $users;
    }

    private function getJobs(Model $users)
    {
        $status = [
            'pending', 
            'assigned', 
            'started'
        ];

        if ($users->is('customer')) {
            return $this->bookingRepository->getCustomerJobs($users, $status);
        } 

        return $this->bookingRepository->getTranslatorJobs($users->id)
                ->pluck('jobs')
                ->all();
    }

    private function getEmergencyJobs(array $jobs)
    {
        return array_filter($jobs, function($jobItem) {
            return $jobItem->immediate === 'yes';
        });
    }

    private function getNormalJobs(array $jobs)
    {
        $normalJobs = array_filter($jobs, function($jobItem) {
            return $jobItem->immediate !== 'yes';
        });

        return collect($normalJobs)->each(function ($item, $key) use ($user_id) {
                    $item['usercheck'] = $this->bookingRepository->checkParticularJob($user_id, $item);
                })
                ->sortBy('due')
                ->all();
    }
}