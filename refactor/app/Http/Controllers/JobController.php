<?php

namespace DTApi\Http\Controllers;

use App\Interfaces\JobServiceInterface;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;
use DTApi\Repository\DistanceRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class JobController extends Controller
{
    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(
        private readonly BookingRepository $repository,
        private readonly DistanceRepository $distanceRepository,
        private readonly JobServiceInterface $jobService,
    ){}

    /**
     * @param Request $request
     * @return mixed
     */
    public function immediateJobEmail(Request $request)
    {
        $response = $this->jobService->storeJobEmail($request->all());

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function acceptJob(Request $request)
    {
        $response = $this->jobService->acceptJob($request->all(), $request->__authenticatedUser);

        return response()->json($response);
    }

    public function acceptJobWithId(Request $request)
    {
        $data = $request->get('job_id');
        $user = $request->__authenticatedUser;

        $response = $this->jobService->acceptJobWithId($data, $user);

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function cancelJob(Request $request)
    {
        $response = $this->jobService->cancelJobAjax($request->all(), $request->__authenticatedUser);

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function endJob(Request $request)
    {
        $data = $request->all();

        $response = $this->jobService->endJob($data);

        return response()->json($response);

    }

    public function customerNotCall(Request $request)
    {
        $data = $request->all();

        $response = $this->jobService->customerNotCall($data);

        return response()->json($response);

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getPotentialJobs(Request $request)
    {
        $user = $request->__authenticatedUser;

        $response = $this->jobService->getPotentialJobs($user);

        return response()->json($response);
    }

    public function distanceFeed(Request $request)
    {
        $data = $request->all();

        $distance = isset($data['distance']) ? $data['distance'] : "";
        $time = isset($data['time']) ? $data['time'] : "";
        $jobid = isset($data['jobid']) ? $data['jobid'] : "";
        $session = isset($data['session_time']) ? $data['session_time'] : "";
        
        $flagged = ($data['flagged'] ?? 'false') === 'true' ? ($data['admincomment'] !== '' ? 'yes' : 'Please, add comment') : 'no';
        $manually_handled = ($data['manually_handled'] ?? 'false') === 'true' ? 'yes' : 'no';
        $by_admin = ($data['by_admin'] ?? 'false') === 'true' ? 'yes' : 'no';
        $admincomment = isset($data['admincomment']) ? $data['admincomment'] : "";
        
        if ($time || $distance) {
            $this->distanceRepository->updateDistance();
        }
        
        if ($admincomment || $session || $flagged || $manually_handled || $by_admin) {
            $this->repository->updateAdminComment($jobid, $admincomment, $flagged, $session, $manually_handled, $by_admin);
        }
        
        return response()->json($response);
        
    }

    public function reopen(Request $request)
    {
        $data = $request->all();
        $response = $this->repository->reopen($data);

        return response($response);
    }

    public function resendNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);
        $this->repository->sendNotificationTranslator($job, $job_data, '*');

        return response(['success' => 'Push sent']);
    }

    /**
     * Sends SMS to Translator
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Symfony\Component\HttpFoundation\Response
     */
    public function resendSMSNotifications(Request $request)
    {
        $data = $request->all();
        $job = $this->repository->find($data['jobid']);
        $job_data = $this->repository->jobToData($job);

        try {
            $this->repository->sendSMSNotificationToTranslator($job);
            return response(['success' => 'SMS sent']);
        } catch (\Exception $e) {
            return response(['success' => $e->getMessage()]);
        }
    }

}
