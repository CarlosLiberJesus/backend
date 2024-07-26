<?php

namespace App\Http\Controllers\UserLocation\Freguesia;

use App\Http\Controllers\Controller;
use App\Models\Freguesia;
use Illuminate\Http\Request;

class FreguesiaController extends Controller
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

            $freguesias = Freguesia::all();

            $formatedFreguesias = [];
            foreach ($freguesias as &$freguesia) {
                array_push($formatedFreguesias, [
                    "uuid" => $freguesia->uuid,
                    "name" => $freguesia->name,
                ]);
            }

            return response()->json([
                'freguesias' => $formatedFreguesias
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
