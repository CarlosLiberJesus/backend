<?php

namespace App\Http\Controllers\Administration\CRM\Permission;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserPermissionController extends Controller
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
                    'message' => 'Utilizador não auntenticado',
                ], 401);
            }

            $roles = $user->roles()->get();
            if (!$roles) {
                return response()->json([
                    'message' => 'Utilizador sem perfis',
                ], 401);
            }

            if (!in_array($roles->code, ['COMEL'])) {
                return response()->json([
                    'message' => 'Utilizador não tem perfil para aceder',
                ], 401);
            }

            $permissions = Permission::all();

            $formatedPermissions = [];
            foreach ($permissions as &$permission) {
                array_push($formatedPermissions, [
                    "uuid" => $permission->uuid,
                    "name" => $permission->name,
                    "code" => $permission->code,
                    "description" => $permission->description
                ]);
            }

            return response()->json([
                'permissions' => $formatedPermissions
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
