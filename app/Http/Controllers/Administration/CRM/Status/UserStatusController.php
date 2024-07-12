<?php

namespace App\Http\Controllers\Administration\CRM\Status;

use App\Http\Controllers\Controller;
use App\Models\Application;

use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserStatusController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * A description of the entire PHP function.
     *
     * @param Request $request
     * @throws \Throwable description of exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'message' => 'USER.VALIDATION.FAILURE',
                ], 401);
            }

            $profile = $user->profiles()->where('app_id', Application::where('uuid',$request->header('APP_UUID'))->first()->id)->first();
            if (!$profile) {
                return response()->json([
                    'message' => 'USER.VALIDATION.FAILURE',
                ], 401);
            }

            if (!in_array($profile->role->name, ['root'])) {
                return response()->json([
                    'message' => 'USER.VALIDATION.FAILURE',
                ], 401);
            }

            $statuses = Status::all();

            $formatedStatus = [];
            foreach ($statuses as &$status) {
                array_push($formatedStatus, [
                    "uuid" => $status->uuid,
                    "name" => $status->name,
                    "color" => $status->color,
                ]);
            }

            return response()->json([
                'statuses' => $formatedStatus
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => basename($th->getFile())
            ], 500);
        }
    }

}
