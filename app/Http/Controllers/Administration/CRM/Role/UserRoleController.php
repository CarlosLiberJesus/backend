<?php

namespace App\Http\Controllers\Administration\CRM\Role;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserRoleController extends Controller
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

            $roles = Role::all();

            $formatedRoles = [];
            foreach ($roles as &$role) {
                array_push($formatedRoles, [
                    "uuid" => $role->uuid,
                    "name" => $role->name,
                    "color" => $role->color,
                    "code" => $role->code,
                    "description" => $role->description
                ]);
            }

            return response()->json([
                'roles' => $formatedRoles
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
