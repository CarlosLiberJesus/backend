<?php
namespace App\Http\Controllers\Administration\CRM;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
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

            if (!in_array($profile->role->name, ['root', 'admin'])) {
                return response()->json([
                    'message' => 'USER.VALIDATION.FAILURE',
                ], 401);
            }

            $page = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);

            $searchText = $request->input('text');
            $usersQuery = User::with(['profiles.app', 'profiles.role', 'profiles.status'])
                ->whereHas('profiles', function ($query) use ($request) {
                    $query->where('app_id', Application::where('uuid', $request->header('APP_UUID'))->first()->id);
                });

            if ($searchText) {
                $usersQuery->where(function ($query) use ($searchText) {
                    $query->where('name', 'like', "%$searchText%")
                        ->orWhere('email', 'like', "%$searchText%");
                });
            }
            $users = $usersQuery->paginate($perPage, ['*'], 'page', $page);

            $formatedUsers = [];
            foreach ($users as &$user) {
                $nameParts = explode(" ", $user->name);
                $profile = $user->profiles->first();
                array_push($formatedUsers, [
                    "uuid" => $user->uuid,
                    "email" => $user->email,
                    "fullname" => $user->name,
                    "firstname" => $nameParts[0],
                    "lastname" => sizeof($nameParts) > 1 ? end($nameParts) : '',
                    "profile" =>  [
                            'app_uuid' => $profile->app->uuid,
                            'role' => [
                                'color' => $profile->role->color,
                                'name' => $profile->role->name
                            ],
                            'status' => [
                                'color' => $profile->status->color,
                                'name' => $profile->status->name,
                            ],
                            'rating' => $profile->rating,
                            'avatar' => $profile->avatar ? @base64_encode(file_get_contents(app_path('../public/avatars/'.$profile->avatar))) : null,
                            'rgbd' => $profile->rgbd,
                            'permissions' => [],
                        ],
                    "lastLogin" => $profile->status->id !== 2 ? $user->updated_at->toIso8601String() : null,
                    "details" => $user->detail,
                ]);
            }

            return response()->json([
                'users' => $formatedUsers,
                "pagination" => [
                    "page"=>$users->currentPage(),
                    "perPage"=>$users->perPage(),
                    "total"=>$users->total()
                ]
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => basename($th->getFile())
            ], 500);
        }
    }

    /**
     * A description of the entire PHP function.
     *
     * @param Request $request
     * @throws \Throwable description of exception
     * @return \Illuminate\Http\JsonResponse
     */
    public function get(Request $request)    {
        try {
            $uuid = request('uuid');
            if(!$uuid) {
                return response()->json([
                    'message' => 'USER.NOT_FOUND',
                ], 500);
            }

            $authUser = Auth::user();
            if (!$authUser) {
                return response()->json([
                    'message' => 'USER.VALIDATION.FAILURE',
                ], 401);
            }
            $authProfile = $authUser->profiles()->where('app_id', Application::where('uuid',$request->header('APP_UUID'))->first()->id)->first();
            if (!$authProfile) {
                return response()->json([
                    'message' => 'USER.VALIDATION.FAILURE',
                ], 401);
            }

            $user = User::with(['profiles.app', 'profiles.role', 'profiles.status'])
                ->whereHas('profiles', function ($query) use ($request) {
                    $query->where('app_id', Application::where('uuid', $request->header('APP_UUID'))->first()->id);
                })->where('uuid', $uuid)->first();
            if (!$user) {
                return response()->json([
                    'message' => 'USER.NOT_FOUND',
                ], 500);
            }
            $userProfile = $user->profiles()->where('app_id', Application::where('uuid',$request->header('APP_UUID'))->first()->id)->first();
            if (!$userProfile) {
                return response()->json([
                    'message' => 'USER.NOT_FOUND',
                ], 500);
            }

            if ($authProfile->role->id > 2 && $user->id !== $authUser->id) {
                return response()->json([
                    'message' => 'USER.VALIDATION.FAILURE',
                ], 401);
            } else if($userProfile->role->id === 1 && $user->id !== $authUser->id) {
                return response()->json([
                    'message' => 'USER.VALIDATION.FAILURE',
                ], 401);
            }
            $nameParts = explode(" ", $user->name);
            return response()->json([
                "uuid" => $user->uuid,
                    "email" => $user->email,
                    "fullname" => $user->name,
                    "firstname" => $nameParts[0],
                    "lastname" => sizeof($nameParts) > 1 ? end($nameParts) : '',
                    "profile" =>  [
                            'app_uuid' => $userProfile->app->uuid,
                            'role' => [
                                'color' => $userProfile->role->color,
                                'name' => $userProfile->role->name
                            ],
                            'status' => [
                                'color' => $userProfile->status->color,
                                'name' => $userProfile->status->name,
                            ],
                            'rating' => $userProfile->rating,
                            'avatar' => $userProfile->avatar ? @base64_encode(file_get_contents(app_path('../public/avatars/'.$userProfile->avatar))) : null,
                            'rgbd' => $userProfile->rgbd,
                            'permissions' => [],
                        ],
                    "lastLogin" => $userProfile->status->id !== 2 ? $user->updated_at->toIso8601String() : null,
                    "details" => $user->detail,
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
