<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Traits\OneSignalTrait;
use App\Traits\FormatResponseTrait;
use DB;
use App\Services\UploadImage;

class PostController extends Controller
{
    use OneSignalTrait;
    use FormatResponseTrait;

    public function __construct(UploadImage $UploadImage)
    {
        $this->UploadImage = $UploadImage;
    }

    function getFolderName($extension)
    {
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'bmp':
            case 'webp':
            case 'tiff':
            case 'svg':
            case 'heif':
                return 'images';
            case 'mp4':
            case 'mov':
            case 'wmv':
            case 'avi':
            case 'MOV':
            case 'mkv':
            case 'flv':
            case 'webm':
            case 'mpeg':
            case 'mpg':
            case '3gp':
                return 'videos';
            case 'mp3':
            case 'wav':
            case 'aac':
            case 'flac':
            case 'ogg':
            case 'm4a':
            case 'wma':
            case 'alac':
                return 'audio';

            case 'pdf':
            case 'docx':
            case 'doc':
            case 'txt':
            case 'xlsx':
            case 'xls':
            case 'ppt':
            case 'pptx':
            case 'csv':
                return 'documents';
            default:
                return 'other';
        }
    }

    function getFileType($extension) {
        $extension = strtolower($extension);
        switch ($extension) {
            case 'jpg':
            case 'jpeg':
            case 'png':
            case 'gif':
            case 'bmp':
            case 'webp':
            case 'tiff':
            case 'svg':
            case 'heif':
                return 'images';
            case 'mp4':
            case 'mov':
            case 'wmv':
            case 'avi':
            case 'mkv':
            case 'flv':
            case 'webm':
            case 'mpeg':
            case 'mpg':
            case '3gp':
                return 'videos';
            case 'mp3':
            case 'wav':
            case 'aac':
            case 'flac':
            case 'ogg':
            case 'm4a':
            case 'wma':
            case 'alac':
                return 'audio';
            default:
                return 'unknown';
        }
    }

    public function create(Request $request)
    {
        try 
        {
            $validator = Validator::make($request->all(), [
                'title'         => 'required',
                'media_type'    => 'required|in:audio,video,picture,note',
                'schedule_type' => 'required|in:now,schedule_post,when_i_pass',
                'post_type'     => 'required|in:public,private,add_album',
                'tag_id'        => 'nullable',
                'description'      => 'required',
                'reoccurring_type' => 'required|in:no,yes',
                'media'            => 'nullable|file',
                'album_id'         => 'nullable|exists:albums,id',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first(), 'status' => 'failed'], 400);
            }

            if ($request->media_type =="audio"|| $request->media_type =="video"|| $request->media_type =="picture") 
            {
                // code...
            }
            
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $exception->getMessage(), 'status' => 'failed'], 500);
        }
    }


}
