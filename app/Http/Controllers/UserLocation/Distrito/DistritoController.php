<?php

namespace App\Http\Controllers\UserLocation\Distrito;

use App\Http\Controllers\Controller;
use App\Models\Distrito;
use Illuminate\Http\Request;

class DistritoController extends Controller
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

            $distritos = Distrito::all();

            $formatedDistritos = [];
            foreach ($distritos as &$distrito) {
                array_push($formatedDistritos, [
                    "uuid" => $distrito->uuid,
                    "name" => $distrito->name,
                ]);
            }

            return response()->json([
                'distritos' => $formatedDistritos
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
    public function dump(Request $request)
    {
        try {
            $distritos = Distrito::all();

            /*array_push($formatedDistritos, [
                    "uuid" => $distrito->uuid,
                    "name" => $distrito->name,
                ]);*/

            $allFormated = [];
            foreach ($distritos as &$distrito) {
                $distritoFormatted = [
                    "uuid" => $distrito->uuid,
                    "name" => $distrito->name,
                    "concelhos" => []
                ];

                $concelhos = $distrito->concelhos;
                foreach ($concelhos as &$concelho) {
                    $concelhoFormatted = [
                        "uuid" => $concelho->uuid,
                        "name" => $concelho->name,
                        "freguesias" => []
                    ];

                    $freguesias = $concelho->freguesias;
                    foreach ($freguesias as &$freguesia) {
                        $freguesiaFormatted = [
                            "uuid" => $freguesia->uuid,
                            "name" => $freguesia->name
                        ];
                        array_push($concelhoFormatted["freguesias"], $freguesiaFormatted);
                    }
                    array_push($distritoFormatted["concelhos"], $concelhoFormatted);
                }
                array_push($allFormated, $distritoFormatted);
            }

            return response()->json([
                'all' => $allFormated
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
