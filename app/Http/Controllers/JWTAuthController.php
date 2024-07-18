<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class JWTAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['register', 'login']]);
    }

    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {

        try {
            $application = Application::where('uuid', $request->header('App-Uuid'))->first();
            //middleware já deveria ter apanhado, não custa
            if(!$application) {
                return response()->json([
                    'message' => 'Não foi entregue nenhuma app_key',
                ], 422); // Unprocessable Entity
            }

            $validator = Validator::make($request->all(), [
                'email' => 'required|string',
                'password' => 'required|string',
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Não foram entregues os campos obrigatórios',
                    'errors' => $validator->errors(),
                ], 422); // Unprocessable Entity
            }

            $credentials = $request->only(['email', 'password']);
            $credentials['app_id'] = $application->id;

            if (!$token = Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Utilizador inválido',
                ], 401);
            }

            $user = Auth::user();
            if (!$user) {
                auth()->logout();
                return response()->json([
                    'message' => 'Utilizador não encontrado',
                ], 401);
            }
            $profile = $user ? $user->profile : null;

            if (!$profile) {
                auth()->logout();
                return response()->json([
                    'message' => 'Utilizador sem perfil',
                ], 401);
            }

            if ($profile->status->id !== 1) {
                auth()->logout();
                // TODO case we want to do more stuff :) add logs
                $status = Status::where('id', $profile->status->id)->first();
                return response()->json([
                    'message' => 'Utilizador tem perfil inativo',
                    'errors' => 'Perfil actual: ' . $status->name,
                ], 401);
            }

            $user->touch();
            return $this->jsonResponse($token);
        } catch (\Exception $e) {
            $reply = [
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
            ];
            return response()->json([
                'message' => 'Erro interno',
                'errors' => $reply,
            ], 500);
        }
    }

     /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(Request $request)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'message' => 'USER.FAILURE',
                ], 401);
            }

            $profile = $user ? $user->profile : null;
            if (!$profile) {
                return response()->json([
                    'message' => 'USER.PROFILE.NOT_FOUND',
                ], 401);
            }
            $nameParts = explode(" ", $user->name);
            $roles = $user->roles()->with('role')->get()->map(function ($role) {
                return [
                    'uuid' => $role->role->uuid,
                    'code' => $role->role->code,
                    'name' => $role->role->name
                ];
            });

            $permissions = $user->permissions()->with('permission')->get()->map(function ($permission) {
                return [
                    'uuid' => $permission->permission->uuid,
                    'code' => $permission->permission->code,
                    'name' => $permission->permission->name
                ];
            });

            return response()->json([
                "uuid" => $user->uuid,
                "email" => $user->email,
                "fullname" => $user->name,
                "firstname" => $nameParts[0],
                "lastname" => sizeof($nameParts) > 1 ? end($nameParts) : '',
                "profile" =>  [
                        'freguesia' => [
                            'uuid' => $profile->freguesia->uuid,
                            'name' => $profile->freguesia->name
                        ],
                        'status' => [
                            'color' => $profile->status->color,
                            'name' => $profile->status->name,
                        ],
                        'rating' => $profile->rating,
                        'avatar' => $profile->avatar ? @base64_encode(file_get_contents(app_path('../public/avatars/'.$profile->avatar))) : null,
                        'rgbd' => $profile->rgbd,
                    ],
                    'roles' => $roles,
                    'permissions' => $permissions,
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

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => "Sessão encerrada"], 200);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'Erro a validar sessão do utilizador',
            ], 401);
        }

        return $this->jsonResponse(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function jsonResponse($token)
    {
        return response()->json([
            'accessToken' => $token,
            'tokenType'   => 'Bearer',
            'expiresIn'   => auth()->factory()->getTTL() * 60 * 4,
            'createdAt'   => \Carbon\Carbon::now()->timestamp
        ], 200);
    }
}
