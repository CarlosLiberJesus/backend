<?php

namespace App\Http\Controllers;

use App\Models\Application;
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

        $validator = Validator::make($request->all(), [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'USER.VALIDATION.FAILURE',
                'errors' => $validator->errors(),
            ], 422); // Unprocessable Entity
        }

        $credentials = $request->only(['email', 'password']);

        if (!$token = Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'USER.INVALID',
            ], 401);
        }

        $user = Auth::user();
        if (!$user) {
            auth()->logout();
            return response()->json([
                'message' => 'USER.NOT_FOUND',
            ], 401);
        }

        $profile = $user->profiles()->where('app_id', Application::where('uuid',$request->header('APP_UUID'))->first()->id)->first();
        if (!$profile) {
            auth()->logout();
            return response()->json([
                'message' => 'USER.PROFILE.NOT_FOUND',
            ], 401);
        }

        if ($profile->status->id !== 1) {
            auth()->logout();
            // TODO case we want to do more stuff :) add logs
            return response()->json([
                'message' => 'USER.PROFILE.NOT_ACTIVE',
            ], 401);
        }

        $user->touch();
        return $this->jsonResponse($token);
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

            $profile = $user->profiles()->where('app_id', Application::where('uuid',$request->header('APP_UUID'))->first()->id)->first();
            if (!$profile) {
                return response()->json([
                    'message' => 'USER.PROFILE.NOT_FOUND',
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

        return response()->json(['message' => "LOGOUT"], 200);
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
                'message' => 'USER.FAILURE',
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
