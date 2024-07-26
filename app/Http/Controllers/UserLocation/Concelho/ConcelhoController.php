<?php

namespace App\Http\Controllers\UserLocation\Concelho;

use App\Http\Controllers\Controller;
use App\Models\Concelho;
use Illuminate\Http\Request;

class ConcelhoController extends Controller
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

            $concelhos = Concelho::all();

            $formatedConcelhos = [];
            foreach ($concelhos as &$concelho) {
                array_push($formatedConcelhos, [
                    "uuid" => $concelho->uuid,
                    "name" => $concelho->name,
                ]);
            }

            return response()->json([
                'concelhos' => $formatedConcelhos
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
