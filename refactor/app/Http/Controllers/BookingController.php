<?php

namespace DTApi\Http\Controllers;

use App\Interfaces\CreateUserJobServiceInterface;
use App\Interfaces\GetUserJobServiceInterface;
use App\Interfaces\UpdateJobServiceInterface;
use App\Interfaces\UserJobHistoryServiceInterface;
use Illuminate\Http\Request;
use DTApi\Repository\BookingRepository;

/**
 * Class BookingController
 * @package DTApi\Http\Controllers
 */
class BookingController extends Controller
{
    /**
     * BookingController constructor.
     * @param BookingRepository $bookingRepository
     */
    public function __construct(
        protected readonly BookingRepository $repository,
        protected readonly UserJobHistoryServiceInterface $userJobHistoryService,
        protected readonly GetUserJobServiceInterface $getUserJobService,
        protected readonly CreateUserJobServiceInterface $createUserJobService,
        protected readonly UpdateJobServiceInterface $updateJobService,
    ){}

    /**
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        if ($user_id = $request->get('user_id')) {
            $response = $this->getUserJobService->getUsersJobs($user_id);

            return response()->json($response);
        }
        
        $response = $this->getUserJobService->getAllJobs($request);

        return response()->json($response);
    }

    /**
     * @param $id
     * @return mixed
     */
    public function show($id)
    {
        $relationship = ['translatorJobRel.user'];
        $job = $this->repository->getJobByIdWithRelationship($id, $relationship);

        return response()->json($job);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_language_id'       => 'required',
            'immediate'              => 'required|string|in:yes,no',
            'duration'               => 'required',
            'due_date'               => 'required_if:immediate,no|date',
            'due_time'               => 'required_if:immediate,no|date|date_format:H:i',
            'customer_phone_type'    => 'required_if:immediate,no',
            'customer_physical_type' => 'required_with:customer_phone_type',
        ],
        [
            'customer_phone_type'      => 'Du måste göra ett val här'
        ]);

        if ($validator->fails() === true) {
            $response = [
                'status'     => 'fail',
                'message'    => $validator->errors()->first(),
                'field_name' => $validator->errors()->keys()[0],
            ];

            return response()->json($response);
        }

        $response = $this->createUserJobService->createUsersJobs($request->__authenticatedUser, $request->all());

        return response()->json($response);
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     */
    public function update($id, Request $request)
    {
        $data = $request->except('_token', 'submit');
        $user = $request->__authenticatedUser;
        $job = $this->repository->getJobById($id);
        $response = $this->updateJobService->updateJob($job, $data, $user);

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getHistory(Request $request)
    {
        if($user_id = $request->get('user_id')) {
            $page = $request->get('page') ?? 1;
            $response = $this->userJobHistoryService->getUsersJobsHistory($user_id, $page);

            return response()->json($response);
        }

        return null;
    }

}
