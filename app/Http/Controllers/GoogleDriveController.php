<?php

namespace App\Http\Controllers;
// require '/home3/famcamb/public_html/backend/vendor/vendor/autoload.php';
require '../vendor/vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
class GoogleDriveController extends Controller
{
    
//     protected $storageClient;
//     protected $bucketName;
//     protected $cdnUrl;
    
    
//     public function __construct()
//     {
//         $this->storageClient = new StorageClient([
//             'projectId' => "famcam-bluestoneapps",
//             'keyFilePath' => "/home3/famcamb/public_html/backend/config/service-account-key.json",
//         ]);

//         $this->bucketName = "fam-cam-input";
//         $this->cdnUrl = "https://cdn.famcam.betaplanets.com/";
        
//     }
 
//   public function uploadFileToCloud(Request $request)
// {
//     try {
//         $request->validate([
//             'file' => 'required|file',
//         ]);

//         $file = $request->file('file');
//         $extension = $file->getClientOriginalExtension();

//         // Determine folder based on file extension
//         $folder = $this->getFolderName($extension);
//         $userId = Auth::id();
//         $fileNameWithoutExtension = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

//         // Construct the base path to match the desired URL structure
//         $basePath = $folder . '/user_' . $userId . '/' . $fileNameWithoutExtension . '/' . $file->getClientOriginalName();
//         $bucket = $this->storageClient->bucket($this->bucketName);

//         // Define file paths
//         $originalFilePath = $basePath;
//         $cdnFilePath = $this->cdnUrl . '/' . $basePath;

//         // Upload file to Google Cloud Storage
//         $object = $bucket->upload(fopen($file->getPathname(), 'r'), [
//             'name' => $originalFilePath
//         ]);

//         if ($folder === 'videos') {
//             $compressedFileName = $fileNameWithoutExtension . '_compressed.mp4';
//             $compressedFilePath = $folder . '/user_' . $userId . '/' . $fileNameWithoutExtension . '/' . $compressedFileName;
//             $this->compressVideo($file->getPathname(), $compressedFilePath);

//             $thumbnailFileName = 'thumbnail_' . uniqid() . '.jpeg';
//             $thumbnailFilePath = $folder . '/user_' . $userId . '/' . $fileNameWithoutExtension . '/' . $thumbnailFileName;
//             $this->generateThumbnail($file->getPathname(), $thumbnailFilePath);

//             $response = [
//                 'message' => 'Video uploaded successfully',
//                 'original' => $cdnFilePath,
//                 'compressed' => $this->cdnUrl . '/' . $compressedFilePath,
//                 'thumbnail' => $this->cdnUrl . '/' . $thumbnailFilePath,
//             ];
//         } else {
//             $response = [
//                 'message' => 'File uploaded successfully',
//                 'file' => $cdnFilePath,
//             ];
//         }

//         return response()->json($response);
//     } catch (\Exception $e) {
//         return response()->json(['error' => 'File upload failed: ' . $e->getMessage()], 500);
//     }
// }
 
    
//       function getFolderName($extension)
//     {
//         switch ($extension) {
//             case 'jpg':
//             case 'jpeg':
//             case 'png':
//             case 'gif':
//                 return 'images';
//             case 'mp4':
//             case 'mov':
//                 return 'videos';
//             case 'pdf':
//             case 'docx':
//             case 'txt':
//             case 'xlsx':
//                 return 'documents';
//             default:
//                 return 'other';
//         }
//     }
//         function compressVideo($sourceFilePath, $destinationFilePath)
//     {
//         $ffmpegCmd = "/usr/bin/ffmpeg -i $sourceFilePath -vcodec h264 -acodec mp2 $destinationFilePath";
//         exec($ffmpegCmd);
//     }

//     // Function to generate thumbnail from video (using FFmpeg)
//   function generateThumbnail($sourceFilePath, $thumbnailFilePath)
// {
//     $ffmpegCmd = "/usr/bin/ffmpeg -i $sourceFilePath -ss 00:00:01 -vframes 1 $thumbnailFilePath";
//     exec($ffmpegCmd);
// }

//      function generateUniqueFileName($file)
//     {
//         return rand(1111, 5555) . time() . '_' . $file->getClientOriginalName();
//     }


// public function getAllUploadedFiles()
// {
//     try {
//         // Create a StorageClient instance
//         $storageClient = new StorageClient([
//             'projectId' => "famcam-bluestoneapps",
//             'keyFilePath' => "/home3/famcamb/public_html/backend/config/service-account-key.json",
//         ]);

//         // Specify the bucket name
//         $bucketName = "fam-cam-input";

//         // Initialize arrays for different file types
//         $videos = [];
//         $images = [];

//         // Get the bucket object
//         $bucket = $storageClient->bucket($bucketName);

//         // Get all objects in the bucket
//         $objects = $bucket->objects();

//         // Iterate over the objects and categorize by file type
//         foreach ($objects as $object) {
//             $objectName = $object->name();

//             // Check if the object is a video
//             if (strpos($objectName, 'videos/') === 0) {
//                 // Add CDN URL to the videos array
//                 $videos[] = $this->cdnUrl . $objectName;
//             }
            
//             // Check if the object is an image (assuming images are also inside videos folder)
//             if (strpos($objectName, 'videos/') === 0 && ends_with($objectName, ['.jpg','.jpeg','.png'])) {
//                 $images[] = $this->cdnUrl . $objectName;
//             }
//         }

//         // Return response with categorized URLs
//         return response()->json([
//             'videos' => $videos,
//             'images' => $images,
//         ]);
//     } catch (\Google\Cloud\Core\Exception\ServiceException | \InvalidArgumentException $e) {
//         return response()->json([
//             'error' => 'Failed to fetch files: ' . $e->getMessage(),
//         ], 500);
//     }
// }
}

    
    
    

