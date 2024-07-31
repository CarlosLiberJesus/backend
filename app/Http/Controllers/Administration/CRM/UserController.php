<?php
namespace App\Http\Controllers\Administration\CRM;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravolt\Avatar\Avatar as Avatar;
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

            $authUser = Auth::user();
            if (!$authUser) {
                return response()->json([
                    'message' => 'Nenhum user autenticado, não pode processir',
                ], 401);
            }

            $authRoles = $authUser->roles()->with('role')->get()->pluck('role.name')->toArray();
            if (in_array('NO-PL', $authRoles)) {
                return response()->json([
                    'message' => 'Utilizador não aceder a dados de utilizadores',
                ], 401);
            }

            $page = $request->input('page', 1);
            $perPage = $request->input('perPage', 10);

            $searchText = $request->input('text');
            $usersQuery = User::where('app_id', Application::where('uuid',$request->header('APP_UUID'))->first()->id)
                                ->with('profile', 'roles', 'permissions');
            if ($searchText) {
                $usersQuery->where(function ($query) use ($searchText) {
                    $query->where('name', 'like', "%$searchText%")
                        ->orWhere('email', 'like', "%$searchText%");
                });
            }
            // TODO adicionar filtros de procura de districto...
            $users = $usersQuery->paginate($perPage, ['*'], 'page', $page);

            $formatedUsers = [];
            foreach ($users as &$user) {
                $nameParts = explode(" ", $user->name);
                $profile = $user->profile;

                array_push($formatedUsers, [
                    "uuid" => $user->uuid,
                    "email" => $user->email,
                    "fullname" => $user->name,
                    "firstname" => $nameParts[0],
                    "lastname" => sizeof($nameParts) > 1 ? end($nameParts) : '',
                    "profile" =>  [
                            'location' => [
                                'distrito' => [
                                'uuid' => $profile->freguesia->distrito->uuid,
                                'name' => $profile->freguesia->distrito->name,
                                'concelho' => [
                                    'uuid' => $profile->freguesia->concelho->uuid,
                                    'name' => $profile->freguesia->concelho->name,
                                    'freguesia' => [
                                        'uuid' => $profile->freguesia->uuid,
                                        'name' => $profile->freguesia->name
                                    ]
                                ]
                            ]
                            ],
                            'status' => [
                                'color' => $profile->status->color,
                                'name' => $profile->status->name,
                            ],
                            'rating' => $profile->rating,
                            'avatar' => $profile->avatar ? @base64_encode(file_get_contents(app_path('../public/avatars/'.$profile->avatar))) : null,
                            'rgbd' => $profile->rgbd,
                        ],
                        'roles' => $user->roles->map(function ($role) {
                            return [
                                'uuid' => $role->role->uuid,
                                'code' => $role->role->code,
                                'color' => $role->role->color,
                                'name' => $role->role->name
                            ];
                        }),
                        'permissions' => $user->permissions->map(function ($permission) {
                            return [
                                'uuid' => $permission->permission->uuid,
                                'code' => $permission->permission->code,
                                'name' => $permission->permission->name
                            ];
                        }),
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
                    'message' => 'Parametro Utilizador uuid não encontrado',
                ], 500);
            }

            $authUser = Auth::user();
            if (!$authUser) {
                return response()->json([
                    'message' => 'Nenhum user autenticado, não pode processir',
                ], 401);
            }

            $authRoles = $authUser->roles()->with('role')->get()->pluck('role.name')->toArray();

            $user = User::where('uuid', $request->uuid)
                        ->where('app_id', Application::where('uuid',$request->header('APP_UUID'))->first()->id)
                        ->with('profile', 'roles', 'permissions')
                        ->first();

            if (!$user){
                return response()->json([
                    'message' => 'Nenhum utilizador encontrado',
                ], 404);
            };

            if ((in_array('COMEL', $authRoles) || in_array('PLTOP', $authRoles))
                && $authUser->id !== $user->id) {
                return response()->json([
                    'message' => 'Utilizador não pode editar outros',
                ], 401);
            }

            $profile = $user->profile;
            if (!$profile) {
                return response()->json([
                    'message' => 'Perfil de utilizador não encontrado',
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
                        'location' => [
                            'distrito' => [
                            'uuid' => $profile->freguesia->distrito->uuid,
                            'name' => $profile->freguesia->distrito->name,
                            'concelho' => [
                                'uuid' => $profile->freguesia->concelho->uuid,
                                'name' => $profile->freguesia->concelho->name,
                                'freguesia' => [
                                    'uuid' => $profile->freguesia->uuid,
                                    'name' => $profile->freguesia->name
                                ]
                            ]
                        ]
                        ],
                        'status' => [
                            'color' => $profile->status->color,
                            'name' => $profile->status->name,
                        ],
                        'rating' => $profile->rating,
                        'avatar' => $profile->avatar ? @base64_encode(file_get_contents(app_path('../public/avatars/'.$profile->avatar))) : null,
                        'rgbd' => $profile->rgbd,
                    ],
                    'roles' => $user->roles->map(function ($role) {
                        return [
                            'uuid' => $role->role->uuid,
                            'code' => $role->role->code,
                            'color' => $role->role->color,
                            'name' => $role->role->name
                        ];
                    }),
                    'permissions' => $user->permissions->map(function ($permission) {
                            return [
                                'uuid' => $permission->permission->uuid,
                                'code' => $permission->permission->code,
                                'name' => $permission->permission->name
                            ];
                        }),
                "lastLogin" => $profile->status->id !== 2 ? $user->updated_at->toIso8601String() : null,
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

    public function emailCheck(Request $request) {

        try {
            $user = User::where('email', $request->email)
                        ->where('app_id', Application::where('uuid',$request->header('APP_UUID'))->first()->id)
                        ->first();
            return response()->json([
                'valid' => $user ? false : true
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => basename($th->getFile())
            ], 500);
        }
    }

    public function register(Request $request) {

        $avatarPath = './avatars/';
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255',
                'password' => 'required|string|min:8',
                'freguesia' => 'required|string|uuid',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'valid' => false,
                    'message' => $validator->errors(),
                    'line' => 237,
                    'file' => 'UserController@register'
                ], 422);
            }


            $user = User::create([
                'uuid' => Str::uuid(),
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'app_id' => $request->header('APP_UUID'),
                'updated_at' => null,
            ]);


            if (!$user) {
                return response()->json([
                    'valid' => false,
                ], 500);
            }

            $fileName = $user->uuid.'.png';
            $nameParts = explode(" ", $user->name);
            $name = $nameParts[0] . " " . $nameParts[sizeof($nameParts) - 1];
            (new Avatar(include '../config/laravolt/avatar.php'))->create($name)->save($avatarPath.$fileName);

            // TODO change the status to mail confim
            $userProfile = $user->profile()->create([
                'user_id' => $user->id,
                'status_id'=> 1,
                'freguesia_id' => $request->freguesia,
                'rgbd' => 1,
                'avatar' => $fileName
            ]);

            if (!$userProfile) {
                return response()->json([
                    'valid' => false,
                ], 500);
            }

            $userRole = $user->roles()->create([
                'user_id' => $user->id,
                'role_id' => 8
            ]);

            if (!$userRole) {
                return response()->json([
                    'valid' => false,
                ], 500);
            } else {
                return response()->json([
                    'valid' => true,
                ], 200);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'message' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => basename($th->getFile())
            ], 500);
        }
    }

}
