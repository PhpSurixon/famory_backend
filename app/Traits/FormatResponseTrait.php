<?php
namespace App\Traits;


use Symfony\Component\HttpFoundation\Response;
use Willywes\AgoraSDK\RtcTokenBuilder;
use App\Models\DeviceDetail;
use App\Models\Notification;
use Illuminate\Support\Carbon;
use App\Traits\ServiceBuilder;

trait FormatResponseTrait {
    
    public function successResponse($message = '', $statusCode, $data = NULL, $paginate = array()){
        $response                = array();
        $response['message']     = $message;
        // $response['status_code'] = $statusCode;
        $response['status'] = "success";
        $response['error_type']  = '';
        $response['data']        = $data;

        if(!empty($paginate)){
            $response['total_records']  = $paginate->total();
            $response['total_pages']    = $paginate->lastPage();
            $response['current_page']   = $paginate->currentPage();
            $response['per_page']      = $paginate->perPage();
        }
        return response()->json($response, $statusCode);
    }
    
    public function errorResponse($message = '', $type = '', $statusCode, $data=NULL){
        $response               = array();
        // $response['status_code']     = $statusCode;
        $response['status'] = "fail";
        $response['message']    = $message;
        $response['error_type'] = $type;
        $response['data']       = $data;
       
        return response()->json($response, $statusCode);
    }

    public function successResponseWithPagintaion($message = '', $statusCode, $data = NULL)
{
    $response = [
        'message' => $message,
        'status' => "success",
        'error_type' => '',
        'data' => $data->items()
    ];

    // Check if the data is paginated (i.e., an instance of LengthAwarePaginator)
    if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
        $response['total_records'] = $data->total();
        $response['total_pages'] = $data->lastPage();
        $response['current_page'] = $data->currentPage();
        $response['per_page'] = $data->perPage();
    }

    // Return the response as a JSON response with the provided status code
    return response()->json($response, $statusCode);
}

}