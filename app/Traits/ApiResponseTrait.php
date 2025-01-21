<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{

    /**
     * return api response success
     *
     * @param Array $data
     * @param String $message
     * @param Int $code
     * @param Array $additional_data
     *
     * @return JsonResponse
     */
    protected function api_response_success(String $message, array $data = [], array $additional_data = [], Int $response_code = 200): JsonResponse
    {
        return response()->json(
            array_merge([
                'metadata' => [
                    'status' => 'success',
                    'message' => $message,
                    'errors' => [],
                ],
                'response' => $data,
            ], $additional_data),
            $response_code
        );
    }

    /**
     * return api response error
     *
     * @param Array $data
     * @param String $message
     * @param Int $code
     * @param Array $errors
     *
     * @return JsonResponse
     */
    protected function api_response_error(String $message, array $data = [], array $errors = [], Int $response_code = 400): JsonResponse
    {
        /*
        * Temporary Handling for Forbidden Access (HTTP 403)
        *
        * During development, some routes may unexpectedly return a 403 error,
        * which can disrupt the flow of other processes in the front end.
        * To mitigate this, we allow specific routes to return a 400 Bad Request
        * instead of a 403 Forbidden, based on the environment configuration.
        *
        * The 'FORBIDDEN_ACCESS_TO_400' environment variable contains a comma-separated
        * list of route names that should be treated this way. This is a temporary solution
        * to be used only during development and should be removed or corrected for production.
        */
        if ($response_code == 403) {
            $arr_forbidden_to_400 = explode(',', env('FORBIDDEN_ACCESS_TO_400', ''));
            if (in_array(request()->path(), $arr_forbidden_to_400)) {
                $response_code = 400;
            }
        }
        /* End of temporary handling */

        return response()->json([
            'metadata' => [
                'status' => 'error',
                'message' => $message,
                'errors' => $errors,
            ],
            'response' => $data,
        ], $response_code);
    }
}
