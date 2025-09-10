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


}
