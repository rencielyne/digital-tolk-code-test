<?php

namespace App\Services\UserJobService;

use App\Interfaces\CreateUserJobServiceInterface;

class CreateUserJobService implements CreateUserJobServiceInterface
{
    public function createUsersJobs($user, $data)
    {
        $immediateTime = 5;
        $response = [];
        $consumerType = $user->userMeta->consumer_type;

        if ($user->user_type !== env('CUSTOMER_ROLE_ID')) {
            $response['status'] = 'fail';
            $response['message'] = "Translator can not create booking";

            return $response;
        }

        $data['customer_phone_type'] = (isset($data['customer_phone_type']) === true) ? 'yes' : 'no';
        $data['customer_physical_type'] = (isset($data['customer_physical_type']) === true) ? 'yes' : 'no';
        $data['gender'] = (in_array('male', $data['job_for']) ===  true) ? 'male' : 'female';
        $data['certified']  = $this->getCertifiedJobType($data['job_for']);
        $data['b_created_at'] = date('Y-m-d H:i:s');
        $data['job_type'] = $this->getJobType($consumerType);

        $dueDateData = $this->getDueDateData($data, $immediateTime);
        $data = array_merge($data, $dueDateData['data']);

        $job = $user->jobs()->create($data);

        $response = array_merge($response, $dueDateData['response']);
        $response['status'] = 'success';
        $response['id'] = $job->id;
        
        return $response;
    }

    private function getCertifiedJobType(array $job_for)
    {        
        if (in_array('certified', $job_for)) {
            return match (true) {
                in_array('normal', $job_for)              => 'both',
                in_array('certified_in_law', $job_for)    => 'n_law',
                in_array('certified_in_health', $job_for) => 'n_health',
                default => null,
            };
        }

        return match (true) {
            in_array('normal', $job_for)              => 'normal',
            in_array('certified', $job_for)           => 'yes',
            in_array('certified_in_law', $job_for)    => 'law',
            in_array('certified_in_health', $job_for) => 'health',
            default => null,
        };
    }

    private function getJobType(string $consumer_type)
    {
        return match ($consumer_type) {
            'rwsconsumer' => 'rws',
            'ngo'         => 'unpaid',
            'paid'        => 'paid',
            default       => null,
        };
    }

    private function getDueDateData(array $data, $immediateTime)
    {
        if ($data['immediate'] === 'yes') {
            $due_carbon = Carbon::now()->addMinute($immediateTime);
            $data['due'] = $due_carbon->format('Y-m-d H:i:s');
            $data['immediate'] = 'yes';
            $data['customer_phone_type'] = 'yes';
            $response['type'] = 'immediate';

            return [
                'response' => $response,
                'data'     => $data
            ];
        } 

        $due = $data['due_date'] . " " . $data['due_time'];
        $response['type'] = 'regular';
        $due_carbon = Carbon::createFromFormat('m/d/Y H:i', $due);
        $data['due'] = $due_carbon->format('Y-m-d H:i:s');
        $data['will_expire_at'] = TeHelper::willExpireAt($due, $data['b_created_at']);
        $data['by_admin'] = isset($data['by_admin']) ? $data['by_admin'] : 'no';

        if (($data['immediate'] === 'no') && $due_carbon->isPast()) {
            $response['status'] = 'fail';
            $response['message'] = "Can't create booking in past";
            return $response;
        }

        return [
            'response' => $response,
            'data'     => $data
        ];
    }
}