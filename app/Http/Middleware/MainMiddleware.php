<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Application;
use App\Models\Log;


class MainMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {

        $environment = env('APP_ENV');
        $start_time = microtime(true);
        $url = "/".$request->path();

        // Handle the preflight request
        if ($request->isMethod('OPTIONS')) {
            return response('', 200)
                    ->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, App-Uuid');
        }

        // Bloqueo se pedido não tiver chave-aplicação correcta
        if (!$this->checkIncomingRequest($request)) {
            if ($environment === 'local') {
                error_log('##################################' . PHP_EOL . '######### Check App_Key ##########' . PHP_EOL . '##################################');
            }
            return response('', 500)
                    ->header('Access-Control-Allow-Origin', '*')
                    ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
                    ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, App-Uuid');
        }

        try {
            if ($environment === 'local') {
                error_log('Requested data @'. $url . PHP_EOL . 'Incoming Body: ' . json_encode($request->all(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            // Processo do pedido no controlador
            $response = $next($request);

            // Valida se pedido de utilizador em sessão, refresh de ultimo acesso
            $user = auth()->user();
            $user_id = $user ? $user->getAuthIdentifier() : null;
            if ($user) {
                $user->touch();
            }
            $message = null;
            $params = null;
            $reply = null;

            // valida reposta do controlador
            $code = $response->getStatusCode();
            if ($code !== 200) {
                $message = "ERROR.".$code;
                $reply = json_decode($response->getContent(), true);
                $params = $request->except('password');
            }

            $end_time = microtime(true);
            $processing_time = round(($end_time - $start_time) * 1000);

            // ao logo do tempo temos recolhido os elementos principais
            $logRequest = [
                'user_id' => $user_id,
                'app_id' => $request->header('App-Uuid'),
                'code' => $code,
                'url' => $url,
                'message' => $message,
                'params' => $params ? json_encode($params) : null,
                'reply' => $reply ? json_encode($reply) : null,
                'time' => $processing_time
            ];
            Log::create($logRequest);

            $response = response()->json([
                'success' => $code === 200,
                'code' => $code,
                'message' => $message,
                'url' => $url,
                'exception' => $reply,
                'data' => $code === 200 ? json_decode($response->getContent(), true) : null
            ], 200);

            if ($environment === 'local') {
                error_log('Replied with: ' . PHP_EOL . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, App-Uuid');
            return $response;

       // Caso o normal não tenha corrido bem
       } catch (\Exception $e) {
            // Captura de erros
            $reply = [
                'message' => $e->getMessage(),
                'file' => basename($e->getFile()),
                'line' => $e->getLine(),
            ];

            $processing_time = round(($end_time - $start_time) * 1000);
            $logRequest = [
                'user_id' => auth()->user()->getAuthIdentifier() ?? null,
                'app_id' => $request->header('App-Uuid') ?? Application::find(1)->first()->id,
                'code' => 500,
                'url' => "/".$request->path(),
                'message' => 'MIDDLEWARE.500',
                'params' => json_encode($request->except('password')),
                'reply' => json_encode($reply),
                'time' => $processing_time
            ];
            Log::create($logRequest);

            $response = response()->json([
                'message' => 'MIDDLEWARE.500',
                'exception' => $reply,
            ], 500);

            if ($environment === 'local') {
                error_log('Not good news: ' . PHP_EOL . json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
            }

            $response->header('Access-Control-Allow-Origin', '*');
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, App-Uuid');

            return $response;
        }
    }


    private function checkIncomingRequest(Request $request)
    {
        $valid = true;
        if (!$request->header('Content-Type') || $request->header('Content-Type') != 'application/json') {
            $valid = false;
        }
        if (!$request->header('App-Uuid')) {
            $valid = false;
        }
        $application = Application::where('uuid', $request->header('App-Uuid'))->first();
        if (!$application) {
            $valid = false;
        }

        return $valid;
    }
}
