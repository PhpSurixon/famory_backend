<?php

namespace App\Services;
//require('stripe/autoload.php');
require_once(base_path() . '/vendor/stripe/stripe-php/init.php');
//require_once(base_path() . '/vendor/aws/vendor/autoload.php');
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stripe\Exception\CardException;
use Stripe\StripeClient;
use Illuminate\Support\Facades\Log;
use Aws\S3\S3Client;
use Illuminate\Support\Facades\File;

class UploadImage
{
    protected $s3Client;
    protected $bucket;
    protected $cdn_url;
    
    public function __construct()
    {
        // Initialize the S3 client with the necessary credentials and region
        $this->s3Client = new S3Client([
            'version'     => 'latest',
            'region'      => config('services.aws.region'),
            'credentials' => [
                'key'    => config('services.aws.key'),
                'secret' => config('services.aws.secret'),
            ],
        ]);
        
        $this->bucket = config('services.aws.bucket');
        $this->cdn_url = config('services.s3.cdn_url');
    }
    
    public function saveMediaOLD($image,$user_id){
        try {
           
            
            $file = $image;
            $thumbnailPath = null;
            $sanitizedOriginalFileName = null;
            $localImagePath = null;
            
            // $fileExtension = strtolower($file->getClientOriginalExtension());
            
            if ($image instanceof \Illuminate\Http\UploadedFile) {
                $fileExtension = strtolower($image->getClientOriginalExtension());
            } elseif (is_string($image)) {
                $fileExtension = pathinfo($image, PATHINFO_EXTENSION);
            }
            
            $userId = $user_id;
            $timestamp = time(); // Current timestamp
            $uniqueFolder = $timestamp . $userId;
            
            $imgExtensions = ['jpeg', 'png', 'jpg', 'gif', 'svg'];
            $videoExtensions = ['mp4', 'mov','MOV','mkv','avi','wmv'];
            $audioExtensions = ['mp3', 'wav', 'ogg'];

            if (in_array($fileExtension, $videoExtensions)) {
                
                $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                $videoBaseName = pathinfo($sanitizedOriginalFileName, PATHINFO_FILENAME);
                $fileExtension = pathinfo($sanitizedOriginalFileName, PATHINFO_EXTENSION);
                
                // Base directory to store assets locally
                $baseDir = public_path("assets/tmp_media/videos/user_{$userId}/{$uniqueFolder}");
                
                // Create the directory if it doesn't exist
                if (!file_exists($baseDir)) {
                    mkdir($baseDir, 0755, true);
                }
                
                // Move the uploaded video to the dynamically created folder
                $renamedVideoFilename = 'video.' . $fileExtension; // Renamed video file
                $originalVideoPath = "{$baseDir}/{$renamedVideoFilename}";
                $file->move($baseDir, $renamedVideoFilename);
                
                // Generate thumbnails using FFmpeg
                $sizes = [
                    'small' => '320x180',
                    'medium' => '640x360',
                    'large' => '1280x720'
                ];
                
                $thumbnailPaths = [];
                
                // Generate thumbnails for each size
                foreach ($sizes as $size => $dimensions) {
                    $thumbnailFilename = "{$baseDir}/{$size}.jpeg";
                    
                    // Only generate the thumbnail if it doesn't already exist
                    if (!file_exists($thumbnailFilename)) {
                        try {
                            $command = "ffmpeg -i " . escapeshellarg($originalVideoPath) . " -ss 00:00:01.000 -vframes 1 -s {$dimensions} " . escapeshellarg($thumbnailFilename);
                            shell_exec($command);
                        } catch (\Exception $e) {
                            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
                        }
                    }
                    
                    // Store the generated thumbnail path
                    $thumbnailPaths[$size] = $thumbnailFilename;
                }
                
                // Compress the original video
                $compressedVideoPath = "{$baseDir}/{$videoBaseName}_compressed.mp4";
                if (!file_exists($compressedVideoPath)) {
                    try {
                        $command = "ffmpeg -i " . escapeshellarg($originalVideoPath) . " -vcodec libx264 -crf 28 " . escapeshellarg($compressedVideoPath);
                        shell_exec($command);
                    } catch (\Exception $e) {
                        return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
                    }
                }
                
                // S3 upload process
                $s3Paths = []; // Store S3 file paths to return
                
                // Upload Original Video
                $key = "videos/user_{$userId}/{$uniqueFolder}/{$renamedVideoFilename}";
                $res = $this->uploadStreamingObject($key, $originalVideoPath);
                $responseData = json_decode($res->getContent(), true);
                if ($responseData['status'] == "success") {
                    $s3Paths['original_video'] = $responseData['data']['filePath'];
                } else {
                    return response()->json(['message' => 'Failed to upload original video', 'status' => 'failed'], 500);
                }
                
                // Upload Compressed Video
                $compressedKey = "videos/user_{$userId}/{$uniqueFolder}/{$videoBaseName}_compressed.mp4";
                $res = $this->uploadStreamingObject($compressedKey, $compressedVideoPath);
                $responseData = json_decode($res->getContent(), true);
                if ($responseData['status'] == "success") {
                    $s3Paths['compressed_video'] = $responseData['data']['filePath'];
                } else {
                    return response()->json(['message' => 'Failed to upload compressed video', 'status' => 'failed'], 500);
                }
                
                // Upload Thumbnails
                foreach ($thumbnailPaths as $size => $thumbnailPath) {
                    $thumbnailKey = "videos/user_{$userId}/{$uniqueFolder}/{$size}.jpeg";
                    $res = $this->uploadStreamingObject($thumbnailKey, $thumbnailPath);
                    $responseData = json_decode($res->getContent(), true);
                    if ($responseData['status'] == "success") {
                        $s3Paths["thumbnail_{$size}"] = $responseData['data']['filePath'];
                    } else {
                        return response()->json(['message' => "Failed to upload thumbnail: {$size}", 'status' => 'failed'], 500);
                    }
                }
                
                // Delete the local folder after successful uploads
                File::deleteDirectory($baseDir);
                
                // // Return the S3 paths of all uploaded files
                //  $s3Paths = [
                //     'original' => $s3Paths['original_video'], // Original video URL
                //     'compressed' => $s3Paths['compressed_video'], // Compressed video URL
                //     'thumbnails' => [
                //         'large' => $s3Paths['thumbnail_large'], // Large thumbnail URL
                //         'medium' => $s3Paths['thumbnail_medium'], // Medium thumbnail URL
                //         'small' => $s3Paths['thumbnail_small'] // Small thumbnail URL
                //     ]
                // ];
                
                // return $s3Paths;
                
                
                $baseUrl = "https://famorys3.s3.amazonaws.com";
                
                // Function to remove the base URL from any full S3 path
                function removeBaseUrl($url, $baseUrl) {
                    return str_replace($baseUrl, '', $url); // Remove base URL
                }
                
                // Modify the S3 paths to remove the base URL
                $s3Paths = [
                    'original' => removeBaseUrl($s3Paths['original_video'], $baseUrl), // Original video path
                    'compressed' => removeBaseUrl($s3Paths['compressed_video'], $baseUrl), // Compressed video path
                    'thumbnails' => [
                        'large' => removeBaseUrl($s3Paths['thumbnail_large'], $baseUrl), // Large thumbnail path
                        'medium' => removeBaseUrl($s3Paths['thumbnail_medium'], $baseUrl), // Medium thumbnail path
                        'small' => removeBaseUrl($s3Paths['thumbnail_small'], $baseUrl) // Small thumbnail path
                    ]
                ];
                
                return $s3Paths;
                
                
                
            
            
            }elseif (in_array($fileExtension, $imgExtensions)) {
                
                
                 // Handle image upload
                // $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    // Sanitize the original file name if it's an uploaded file
                    $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                } elseif (is_string($file)) {
                    // If it's a string (URL or file path), use basename to get the file name
                    $sanitizedOriginalFileName = $this->sanitizeFileName(basename($file));
                }
                
                
                
                $localFolderPath = public_path("assets/tmp_media/images/user_{$userId}/{$uniqueFolder}");
                $localImagePath = "{$localFolderPath}/{$sanitizedOriginalFileName}";
            
                // Move the file to the local folder
                if (!file_exists($localFolderPath)) {
                    mkdir($localFolderPath, 0755, true);  // Create the folder if it doesn't exist
                }
                
                $file->move($localFolderPath, $sanitizedOriginalFileName); // Move the file to the local path
            
                // Upload the image to S3
                $key = "images/user_{$user_id}/{$uniqueFolder}/{$sanitizedOriginalFileName}";
                $res = $this->uploadStreamingObject($key, $localImagePath);
                $responseData = json_decode($res->getContent(), true); // Decode JSON response to an associative array
                // Check if the upload was successful
                if ($responseData['status'] == "success") {
                    
                    // Delete the local image and its folder
                    if (File::exists($localImagePath)) {
                        File::delete($localImagePath); // Delete the local image
                    }
            
                    // Remove the entire folder if it exists
                    if (File::exists($localFolderPath)) {
                        File::deleteDirectory($localFolderPath); // Delete the folder and its contents
                    }
            
                    // return $responseData['data']['filePath']; // Return the S3 file path
                    
                    $fullUrl = $responseData['data']['filePath']; // Return the S3 file path
                    
                    $urlComponents = parse_url($fullUrl);

                    // $urlComponents['path'] will give you "/audio/user_9/1723017144716_audio.mp3"
                    $pathOnly = $urlComponents['path'];
                    
                    return $pathOnly;
                    
            
                } else {
                    return null; // Handle upload failure
                }
                
                
            }elseif (in_array($fileExtension, $audioExtensions)) {
                
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    // Sanitize the original file name if it's an uploaded file
                    $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                } elseif (is_string($file)) {
                    // If it's a string (URL or file path), use basename to get the file name
                    $sanitizedOriginalFileName = $this->sanitizeFileName(basename($file));
                }
                
                $audioDir = public_path("assets/tmp_media/audio/user_{$user_id}");
                // Create the directory if it does not exist
                if (!file_exists($audioDir)) {
                    mkdir($audioDir, 0755, true); // Create the directory with appropriate permissions
                }
                
                $path = "{$audioDir}/{$sanitizedOriginalFileName}";
                
                // Move the uploaded file to the local path
                $file->move($audioDir, $sanitizedOriginalFileName); 
                
                $key = "audio/user_{$user_id}/{$sanitizedOriginalFileName}";
                
                // Upload the audio file to S3
                $res = $this->uploadStreamingObject($key, $path);
                $responseData = json_decode($res->getContent(), true); // Decode JSON response to an associative array
                
                // Check if the upload was successful
                if ($responseData['status'] == "success") {
                    // Remove the local audio file
                    if (file_exists($path)) {
                        unlink($path); // Delete the local file
                    }
                
                    // Optionally, remove the entire user audio directory if it's empty
                    if (is_dir($audioDir) && count(scandir($audioDir)) === 2) { // Check if directory is empty
                        rmdir($audioDir); // Remove the directory
                    }
                
                    // return $responseData['data']['filePath']; // Return the S3 file path
                    
                    $fullUrl = $responseData['data']['filePath']; // Return the S3 file path
                    
                    $urlComponents = parse_url($fullUrl);

                    // $urlComponents['path'] will give you "/audio/user_9/1723017144716_audio.mp3"
                    $pathOnly = $urlComponents['path'];
                    
                    return $pathOnly;
                    
                    
                    
                    
                } else {
                    return "Not saved"; // Handle upload failure
                }
    
    
    
                // Include URL for the audio in the response
                return response()->json(['message' => 'Audio uploaded successfully', 'status' => 'success', 'data' => $key], 200);
    
            }
            
            
            return response()->json(['message' => 'File uploaded successfully', 'status' => 'success', 'data' => $thumbnailPath], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
            // return response()->json(['message' => "Something went wrong", 'status' => 'failed'], 500);
        }
    }

    public function saveMediaOLD11($image, $user_id)
    {
        try {
            $file = $image;

            if ($image instanceof \Illuminate\Http\UploadedFile) {
                $fileExtension = strtolower($image->getClientOriginalExtension());
            } elseif (is_string($image)) {
                $fileExtension = pathinfo($image, PATHINFO_EXTENSION);
            } else {
                throw new \Exception("Invalid file input");
            }

            $userId = $user_id;
            $timestamp = time();
            $uniqueFolder = $timestamp . $userId;

            $imgExtensions = ['jpeg', 'png', 'jpg', 'gif', 'svg'];
            $videoExtensions = ['mp4', 'mov','MOV','mkv','avi','wmv'];
            $audioExtensions = ['mp3', 'wav', 'ogg'];

            // ---------------- VIDEO ----------------
            if (in_array($fileExtension, $videoExtensions)) 
            {
                
                $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                $videoBaseName = pathinfo($sanitizedOriginalFileName, PATHINFO_FILENAME);

                $baseDir = public_path("assets/tmp_media/videos/user_{$userId}/{$uniqueFolder}");
                if (!file_exists($baseDir)) mkdir($baseDir, 0755, true);

                $renamedVideoFilename = "video.{$fileExtension}";
                $originalVideoPath = $baseDir . DIRECTORY_SEPARATOR . $renamedVideoFilename;
                $file->move($baseDir, $renamedVideoFilename);

                if (!file_exists($originalVideoPath)) {
                    throw new \Exception("Video not saved locally");
                }

                // ✅ FFmpeg path detect
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $ffmpegPath = "C:/ffmpeg/bin/ffmpeg.exe";
                } else {
                    $ffmpegPath = "ffmpeg";
                }

                // ✅ Generate thumbnails
                $sizes = ['small'=>'320x180','medium'=>'640x360','large'=>'1280x720'];
                $thumbnailPaths = [];
                foreach ($sizes as $size => $dimensions) {
                    $thumbnailFilename = $baseDir . DIRECTORY_SEPARATOR . "{$size}.jpeg";
                    $command = "$ffmpegPath -i " . escapeshellarg($originalVideoPath) .
                    " -ss 00:00:01.000 -vframes 1 -s {$dimensions} " .
                    escapeshellarg($thumbnailFilename) . " -y";
                    shell_exec($command);

                    if (!file_exists($thumbnailFilename)) {
                        throw new \Exception("Failed to generate {$size} thumbnail");
                    }
                    $thumbnailPaths[$size] = $thumbnailFilename;
                }

                // ✅ Compress video
                $compressedVideoPath = $baseDir . DIRECTORY_SEPARATOR . "{$videoBaseName}_compressed.mp4";
                $command = "$ffmpegPath -i " . escapeshellarg($originalVideoPath) .
                " -vcodec libx264 -crf 28 " . escapeshellarg($compressedVideoPath) . " -y";
                shell_exec($command);

                if (!file_exists($compressedVideoPath)) {
                    throw new \Exception("Compressed video not generated");
                }

                // ✅ Upload to S3
                $s3Paths = [];

                $key = "videos/user_{$userId}/{$uniqueFolder}/{$renamedVideoFilename}";
                $res = $this->uploadStreamingObject($key, $originalVideoPath);
                $responseData = json_decode($res->getContent(), true);
                if ($responseData['status'] !== "success") throw new \Exception("Original video upload failed");
                $s3Paths['original'] = $responseData['data']['filePath'];

                $compressedKey = "videos/user_{$userId}/{$uniqueFolder}/{$videoBaseName}_compressed.mp4";
                $res = $this->uploadStreamingObject($compressedKey, $compressedVideoPath);
                $responseData = json_decode($res->getContent(), true);
                if ($responseData['status'] !== "success") throw new \Exception("Compressed video upload failed");
                $s3Paths['compressed'] = $responseData['data']['filePath'];

                foreach ($thumbnailPaths as $size => $thumbnailPath) {
                    $thumbnailKey = "videos/user_{$userId}/{$uniqueFolder}/{$size}.jpeg";
                    $res = $this->uploadStreamingObject($thumbnailKey, $thumbnailPath);
                    $responseData = json_decode($res->getContent(), true);
                    if ($responseData['status'] !== "success") throw new \Exception("{$size} thumbnail upload failed");
                    $s3Paths['thumbnails'][$size] = $responseData['data']['filePath'];
                }

                \File::deleteDirectory($baseDir);

                $baseUrl = "https://famorys3.s3.amazonaws.com";
                $removeBase = fn($url) => str_replace($baseUrl, '', $url);

                return [
                    'original'   => $removeBase($s3Paths['original']),
                    'compressed' => $removeBase($s3Paths['compressed']),
                    'thumbnails' => array_map($removeBase, $s3Paths['thumbnails']),
                ];
            }

            // ---------------- IMAGE ----------------
            elseif (in_array($fileExtension, $imgExtensions)) {
                $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                $localFolderPath = public_path("assets/tmp_media/images/user_{$userId}/{$uniqueFolder}");
                if (!file_exists($localFolderPath)) mkdir($localFolderPath, 0755, true);

                $localImagePath = $localFolderPath . DIRECTORY_SEPARATOR . $sanitizedOriginalFileName;
                $file->move($localFolderPath, $sanitizedOriginalFileName);

                $key = "images/user_{$userId}/{$uniqueFolder}/{$sanitizedOriginalFileName}";
                $res = $this->uploadStreamingObject($key, $localImagePath);
                $responseData = json_decode($res->getContent(), true);
                if ($responseData['status'] !== "success") throw new \Exception("Image upload failed");

                \File::delete($localImagePath);
                \File::deleteDirectory($localFolderPath);

                $fullUrl = $responseData['data']['filePath'];
                return parse_url($fullUrl, PHP_URL_PATH);
            }

            // ---------------- AUDIO ----------------
            elseif (in_array($fileExtension, $audioExtensions)) {
                $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                $audioDir = public_path("assets/tmp_media/audio/user_{$userId}");
                if (!file_exists($audioDir)) mkdir($audioDir, 0755, true);

                $path = "{$audioDir}/{$sanitizedOriginalFileName}";
                $file->move($audioDir, $sanitizedOriginalFileName);

                $key = "audio/user_{$userId}/{$sanitizedOriginalFileName}";
                $res = $this->uploadStreamingObject($key, $path);
                $responseData = json_decode($res->getContent(), true);
                if ($responseData['status'] !== "success") throw new \Exception("Audio upload failed");

                unlink($path);
                if (is_dir($audioDir) && count(scandir($audioDir)) === 2) rmdir($audioDir);

                $fullUrl = $responseData['data']['filePath'];
                return parse_url($fullUrl, PHP_URL_PATH);
            }

            else {
                throw new \Exception("Unsupported file type: {$fileExtension}");
            }
        } catch (\Exception $e) {

            throw $e; // 👈 propagate to createPost()
        }
    }

    public function saveMediaOLD22($image, $user_id)
    {
        try {
            $file = $image;

            if ($image instanceof \Illuminate\Http\UploadedFile) {
                $fileExtension = strtolower($image->getClientOriginalExtension());
            } elseif (is_string($image)) {
                $fileExtension = pathinfo($image, PATHINFO_EXTENSION);
            } else {
                throw new \Exception("Invalid file input");
            }

            $userId = $user_id;
            $timestamp = time();
            $uniqueFolder = $timestamp . $userId;

            $imgExtensions = ['jpeg', 'png', 'jpg', 'gif', 'svg'];
            $videoExtensions = ['mp4', 'mov','MOV','mkv','avi','wmv'];
            $audioExtensions = ['mp3', 'wav', 'ogg'];

            // ---------------- VIDEO ----------------
            if (in_array($fileExtension, $videoExtensions)) 
            {
                $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                $videoBaseName = pathinfo($sanitizedOriginalFileName, PATHINFO_FILENAME);

                $baseDir = public_path("assets/tmp_media/videos/user_{$userId}/{$uniqueFolder}");
                if (!file_exists($baseDir)) mkdir($baseDir, 0755, true);

                $renamedVideoFilename = "video.{$fileExtension}";
                $originalVideoPath = $baseDir . DIRECTORY_SEPARATOR . $renamedVideoFilename;
                $file->move($baseDir, $renamedVideoFilename);

                if (!file_exists($originalVideoPath)) {
                    throw new \Exception("Video not saved locally");
                }

                // FFmpeg path detection
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    $ffmpegPath = "C:/ffmpeg/bin/ffmpeg.exe";
                } else {
                    $ffmpegPath = "ffmpeg";
                }

                // ------------ Generate Thumbnails ------------
                $sizes = ['small'=>'320x180','medium'=>'640x360','large'=>'1280x720'];
                $thumbnailPaths = [];

                foreach ($sizes as $size => $dimensions) {
                    $thumbnailFilename = $baseDir . DIRECTORY_SEPARATOR . "{$size}.jpeg";

                    $command = "$ffmpegPath -i " . escapeshellarg($originalVideoPath) .
                    " -ss 00:00:01.000 -vframes 1 -s {$dimensions} " .
                    escapeshellarg($thumbnailFilename) . " -y";

                    shell_exec($command);

                    if (!file_exists($thumbnailFilename)) {
                        throw new \Exception("Failed to generate {$size} thumbnail");
                    }

                    $thumbnailPaths[$size] = $thumbnailFilename;
                }

                // ------------ Compress Video ------------
                $compressedVideoPath = $baseDir . DIRECTORY_SEPARATOR . "{$videoBaseName}_compressed.mp4";

                $command = "$ffmpegPath -i " . escapeshellarg($originalVideoPath) .
                " -vcodec libx264 -crf 28 " . escapeshellarg($compressedVideoPath) . " -y";

                shell_exec($command);

                if (!file_exists($compressedVideoPath)) {
                    throw new \Exception("Compressed video not generated");
                }

                // ------------ Generate HLS (.m3u8 + .ts) ------------
                $hlsDir = $baseDir . DIRECTORY_SEPARATOR . 'hls';
                if (!file_exists($hlsDir)) mkdir($hlsDir, 0755, true);

                $hlsPlaylist = $hlsDir . DIRECTORY_SEPARATOR . "index.m3u8";

                $command = "$ffmpegPath -i " . escapeshellarg($compressedVideoPath) .
                    " -profile:v baseline -level 3.0 -start_number 0 -hls_time 6 -hls_list_size 0 " .
                    escapeshellarg($hlsPlaylist) . " -y";

                shell_exec($command);

                if (!file_exists($hlsPlaylist)) {
                    throw new \Exception('HLS playlist (m3u8) generation failed');
                }

                // ------------ Upload to S3 ------------
                $s3Paths = [];

                // Original
                $key = "videos/user_{$userId}/{$uniqueFolder}/{$renamedVideoFilename}";
                $res = $this->uploadStreamingObject($key, $originalVideoPath);
                $responseData = json_decode($res->getContent(), true);
                if ($responseData['status'] !== "success") throw new \Exception("Original video upload failed");
                $s3Paths['original'] = $responseData['data']['filePath'];

                // Compressed
                $compressedKey = "videos/user_{$userId}/{$uniqueFolder}/{$videoBaseName}_compressed.mp4";
                $res = $this->uploadStreamingObject($compressedKey, $compressedVideoPath);
                $responseData = json_decode($res->getContent(), true);
                if ($responseData['status'] !== "success") throw new \Exception("Compressed video upload failed");
                $s3Paths['compressed'] = $responseData['data']['filePath'];

                // Thumbnails
                foreach ($thumbnailPaths as $size => $thumbnailPath) {
                    $thumbnailKey = "videos/user_{$userId}/{$uniqueFolder}/{$size}.jpeg";

                    $res = $this->uploadStreamingObject($thumbnailKey, $thumbnailPath);
                    $responseData = json_decode($res->getContent(), true);

                    if ($responseData['status'] !== "success") {
                        throw new \Exception("{$size} thumbnail upload failed");
                    }

                    $s3Paths['thumbnails'][$size] = $responseData['data']['filePath'];
                }

                // HLS upload
                $hlsFiles = scandir($hlsDir);
                $s3Paths['hls'] = [];

                foreach ($hlsFiles as $fileName) {
                    if ($fileName == "." || $fileName == "..") continue;

                    $localFilePath = $hlsDir . DIRECTORY_SEPARATOR . $fileName;
                    $hlsKey = "videos/user_{$userId}/{$uniqueFolder}/hls/{$fileName}";

                    $res = $this->uploadStreamingObject($hlsKey, $localFilePath);
                    $responseData = json_decode($res->getContent(), true);

                    if ($responseData['status'] !== "success") {
                        throw new \Exception("HLS upload failed: {$fileName}");
                    }

                    $s3Paths['hls'][] = $responseData['data']['filePath'];
                }

                // Cleanup
                \File::deleteDirectory($baseDir);

                $baseUrl = "https://famorys3.s3.amazonaws.com";
                $removeBase = fn($url) => str_replace($baseUrl, '', $url);

                return [
                    'original'      => $removeBase($s3Paths['original']),
                    'compressed'    => $removeBase($s3Paths['compressed']),
                    'thumbnails'    => array_map($removeBase, $s3Paths['thumbnails']),
                    'hls_playlist'  => "videos/user_{$userId}/{$uniqueFolder}/hls/index.m3u8",
                ];
            }

            // ---------------- IMAGE ----------------
            elseif (in_array($fileExtension, $imgExtensions)) {

                $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                $localFolderPath = public_path("assets/tmp_media/images/user_{$userId}/{$uniqueFolder}");
                if (!file_exists($localFolderPath)) mkdir($localFolderPath, 0755, true);

                $localImagePath = $localFolderPath . DIRECTORY_SEPARATOR . $sanitizedOriginalFileName;
                $file->move($localFolderPath, $sanitizedOriginalFileName);

                $key = "images/user_{$userId}/{$uniqueFolder}/{$sanitizedOriginalFileName}";

                $res = $this->uploadStreamingObject($key, $localImagePath);
                $responseData = json_decode($res->getContent(), true);

                if ($responseData['status'] !== "success") {
                    throw new \Exception("Image upload failed");
                }

                \File::delete($localImagePath);
                \File::deleteDirectory($localFolderPath);

                $fullUrl = $responseData['data']['filePath'];
                return parse_url($fullUrl, PHP_URL_PATH);
            }

            // ---------------- AUDIO ----------------
            elseif (in_array($fileExtension, $audioExtensions)) {

                $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                $audioDir = public_path("assets/tmp_media/audio/user_{$userId}");
                if (!file_exists($audioDir)) mkdir($audioDir, 0755, true);

                $path = "{$audioDir}/{$sanitizedOriginalFileName}";
                $file->move($audioDir, $sanitizedOriginalFileName);

                $key = "audio/user_{$userId}/{$sanitizedOriginalFileName}";
                $res = $this->uploadStreamingObject($key, $path);
                $responseData = json_decode($res->getContent(), true);

                if ($responseData['status'] !== "success") {
                    throw new \Exception("Audio upload failed");
                }

                unlink($path);
                if (is_dir($audioDir) && count(scandir($audioDir)) === 2) rmdir($audioDir);

                $fullUrl = $responseData['data']['filePath'];
                return parse_url($fullUrl, PHP_URL_PATH);
            }

            // ---------------- UNSUPPORTED ----------------
            else {
                throw new \Exception("Unsupported file type: {$fileExtension}");
            }
        } 
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function saveMedia_old31_12_2025($image, $user_id)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');
        try {
            $file = $image;

            if ($image instanceof \Illuminate\Http\UploadedFile) {
                $fileExtension = strtolower($image->getClientOriginalExtension());
            } elseif (is_string($image)) {
                $fileExtension = pathinfo($image, PATHINFO_EXTENSION);
            } else {
                throw new \Exception("Invalid file input");
            }

            $userId = $user_id;
            $timestamp = time();
            $uniqueFolder = $timestamp . $userId;

            $imgExtensions = ['jpeg', 'png', 'jpg', 'gif', 'svg'];
            $videoExtensions = ['mp4', 'mov','MOV','mkv','avi','wmv'];
            $audioExtensions = ['mp3', 'wav', 'ogg'];

            // ---------------- VIDEO ----------------
            if (in_array($fileExtension, $videoExtensions)) 
            {
                $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                $videoBaseName = pathinfo($sanitizedOriginalFileName, PATHINFO_FILENAME);

                $baseDir = public_path("assets/tmp_media/videos/user_{$userId}/{$uniqueFolder}");
                if (!file_exists($baseDir)) mkdir($baseDir, 0755, true);

                $renamedVideoFilename = "video.{$fileExtension}";
                $originalVideoPath = $baseDir . DIRECTORY_SEPARATOR . $renamedVideoFilename;
                $file->move($baseDir, $renamedVideoFilename);

                if (!file_exists($originalVideoPath)) {
                    throw new \Exception("Video not saved locally");
                }

                        // FFmpeg path
                $ffmpegPath = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
                ? "C:/ffmpeg/bin/ffmpeg.exe"
                : "ffmpeg";

                        // ------------------------------------------
                        // GET VIDEO ORIGINAL WIDTH x HEIGHT
                        // ------------------------------------------
                        $ffprobePath = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
                        ? "C:/ffmpeg/bin/ffprobe.exe"
                        : "ffprobe";

                        $probeCmd = "$ffprobePath -v error -select_streams v:0 "
                        . "-show_entries stream=width,height "
                        . "-of csv=p=0:s=x "
                        . escapeshellarg($originalVideoPath);

                        $resolution = trim(shell_exec($probeCmd));
                        if (!$resolution) $resolution = "1280x720";

                        list($w, $h) = explode("x", $resolution);

                        // ------------------------------------------
                        // ORIENTATION SAFE THUMBNAIL LOGIC
                        // ------------------------------------------

                        // FFmpeg even-number safety
                        $even = fn($n) => floor($n / 2) * 2;

                        // Resize based on longest side (portrait / landscape auto)
                        function resizeByMaxSide($w, $h, $max)
                        {
                            if ($w >= $h) {
                        // Landscape
                                $newW = $max;
                                $newH = round(($h / $w) * $max);
                            } else {
                        // Portrait
                                $newH = $max;
                                $newW = round(($w / $h) * $max);
                            }

                            $newW = floor($newW / 2) * 2;
                            $newH = floor($newH / 2) * 2;

                            return "{$newW}x{$newH}";
                        }

                        // Thumbnail sizes
                        $sizes = [
                            'small'  => resizeByMaxSide($w, $h, 300),
                            'medium' => resizeByMaxSide($w, $h, 600),
                            'large'  => resizeByMaxSide($w, $h, 1200),
                        ];

                        // ------------------------------------------
                        // GENERATE THUMBNAILS
                        // ------------------------------------------
                        $thumbnailPaths = [];

                        foreach ($sizes as $size => $dimensions) {

                            $thumbnailFilename = $baseDir . DIRECTORY_SEPARATOR . "{$size}.jpeg";

                            $command = "$ffmpegPath -i " . escapeshellarg($originalVideoPath) .
                            " -ss 00:00:01.000 -vframes 1 -vf scale={$dimensions} " .
                            escapeshellarg($thumbnailFilename) . " -y";

                            shell_exec($command);

                            if (!file_exists($thumbnailFilename)) {
                                throw new \Exception("Failed to generate {$size} thumbnail");
                            }

                            $thumbnailPaths[$size] = $thumbnailFilename;
                        }

                        // ------------------------------------------
                        // COMPRESS VIDEO
                        // ------------------------------------------
                        $compressedVideoPath = $baseDir . DIRECTORY_SEPARATOR . "{$videoBaseName}_compressed.mp4";

                        $command = "$ffmpegPath -i " . escapeshellarg($originalVideoPath) .
                        " -vcodec libx264 -crf 28 " . escapeshellarg($compressedVideoPath) . " -y";

                        shell_exec($command);

                        if (!file_exists($compressedVideoPath)) {
                            throw new \Exception("Compressed video not generated");
                        }

                        // ------------------------------------------
                        // GENERATE HLS
                        // ------------------------------------------
                        $hlsDir = $baseDir . DIRECTORY_SEPARATOR . 'hls';
                        if (!file_exists($hlsDir)) mkdir($hlsDir, 0755, true);

                        $hlsPlaylist = $hlsDir . DIRECTORY_SEPARATOR . "index.m3u8";

                        $command = "$ffmpegPath -i " . escapeshellarg($compressedVideoPath) .
                        " -profile:v baseline -level 3.0 -start_number 0 -hls_time 2 -hls_list_size 0 "
                        . escapeshellarg($hlsPlaylist) . " -y";

                        shell_exec($command);

                        if (!file_exists($hlsPlaylist)) {
                            throw new \Exception('HLS playlist generation failed');
                        }

                        // ------------------------------------------
                        // UPLOAD TO S3
                        // ------------------------------------------
                        $s3Paths = [];

                        // Original
                        $res = $this->uploadStreamingObject(
                            "videos/user_{$userId}/{$uniqueFolder}/{$renamedVideoFilename}",
                            $originalVideoPath
                        );
                        $responseData = json_decode($res->getContent(), true);
                        $s3Paths['original'] = $responseData['data']['filePath'];

                        // Compressed
                        $res = $this->uploadStreamingObject(
                            "videos/user_{$userId}/{$uniqueFolder}/{$videoBaseName}_compressed.mp4",
                            $compressedVideoPath
                        );
                        $responseData = json_decode($res->getContent(), true);
                        $s3Paths['compressed'] = $responseData['data']['filePath'];

                        // Thumbnails
                        foreach ($thumbnailPaths as $size => $thumbnailPath) {
                            $res = $this->uploadStreamingObject(
                                "videos/user_{$userId}/{$uniqueFolder}/{$size}.jpeg",
                                $thumbnailPath
                            );
                            $responseData = json_decode($res->getContent(), true);
                            $s3Paths['thumbnails'][$size] = $responseData['data']['filePath'];
                        }

                        // HLS files
                        foreach (scandir($hlsDir) as $fileName) {
                            if ($fileName == "." || $fileName == "..") continue;

                            $this->uploadStreamingObject(
                                "videos/user_{$userId}/{$uniqueFolder}/hls/{$fileName}",
                                $hlsDir . DIRECTORY_SEPARATOR . $fileName
                            );
                        }

                        // Cleanup
                        \File::deleteDirectory($baseDir);

                        $baseUrl = "https://famorys3.s3.amazonaws.com";
                        $removeBase = fn($url) => str_replace($baseUrl, '', $url);

                        return [
                            'original'     => $removeBase($s3Paths['original']),
                            'compressed'   => $removeBase($s3Paths['compressed']),
                            'thumbnails'   => array_map($removeBase, $s3Paths['thumbnails']),
                            'hls_playlist' => "videos/user_{$userId}/{$uniqueFolder}/hls/index.m3u8",
                        ];
            }
            // ---------------- IMAGE ----------------
            elseif (in_array($fileExtension, $imgExtensions)) {

                $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                $localFolderPath = public_path("assets/tmp_media/images/user_{$userId}/{$uniqueFolder}");
                if (!file_exists($localFolderPath)) mkdir($localFolderPath, 0755, true);

                $localImagePath = $localFolderPath . DIRECTORY_SEPARATOR . $sanitizedOriginalFileName;
                $file->move($localFolderPath, $sanitizedOriginalFileName);

                $key = "images/user_{$userId}/{$uniqueFolder}/{$sanitizedOriginalFileName}";

                $res = $this->uploadStreamingObject($key, $localImagePath);
                $responseData = json_decode($res->getContent(), true);

                if ($responseData['status'] !== "success") {
                    throw new \Exception("Image upload failed");
                }

                \File::delete($localImagePath);
                \File::deleteDirectory($localFolderPath);

                $fullUrl = $responseData['data']['filePath'];
                return parse_url($fullUrl, PHP_URL_PATH);
            }

            // ---------------- AUDIO ----------------
            elseif (in_array($fileExtension, $audioExtensions)) {

                $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                $audioDir = public_path("assets/tmp_media/audio/user_{$userId}");
                if (!file_exists($audioDir)) mkdir($audioDir, 0755, true);

                $path = "{$audioDir}/{$sanitizedOriginalFileName}";
                $file->move($audioDir, $sanitizedOriginalFileName);

                $key = "audio/user_{$userId}/{$sanitizedOriginalFileName}";
                $res = $this->uploadStreamingObject($key, $path);
                $responseData = json_decode($res->getContent(), true);

                if ($responseData['status'] !== "success") {
                    throw new \Exception("Audio upload failed");
                }

                unlink($path);
                if (is_dir($audioDir) && count(scandir($audioDir)) === 2) rmdir($audioDir);

                $fullUrl = $responseData['data']['filePath'];
                return parse_url($fullUrl, PHP_URL_PATH);
            }

            else {
                throw new \Exception("Unsupported file type: {$fileExtension}");
            }
        } 
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function saveMedia($image, $user_id)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1024M');

        try {

            // ---------------- BASIC SETUP ----------------
            if ($image instanceof \Illuminate\Http\UploadedFile) {
                $file = $image;
                $fileExtension = strtolower($file->getClientOriginalExtension());
                $mimeType = $file->getMimeType();
            } else {
                throw new \Exception("Invalid file input");
            }

            $userId = $user_id;
            $timestamp = time();
            $uniqueFolder = $timestamp . $userId;

            $imgExtensions   = ['jpeg', 'png', 'jpg', 'gif', 'svg'];
            $videoExtensions = ['mp4', 'mov', 'mkv', 'avi', 'wmv'];
            $audioExtensions = ['mp3', 'wav', 'ogg', 'aac', 'm4a', 'bin'];
            // ========================= VIDEO ============================
            if (in_array($fileExtension, $videoExtensions)) 
            {

                $sanitizedOriginalFileName = $this->sanitizeFileName($file->getClientOriginalName());
                $videoBaseName = pathinfo($sanitizedOriginalFileName, PATHINFO_FILENAME);

                $baseDir = public_path("assets/tmp_media/videos/user_{$userId}/{$uniqueFolder}");
                if (!file_exists($baseDir)) mkdir($baseDir, 0755, true);

                $renamedVideoFilename = "video.{$fileExtension}";
                $originalVideoPath = "{$baseDir}/{$renamedVideoFilename}";
                $file->move($baseDir, $renamedVideoFilename);

                // FFmpeg paths
                $ffmpeg = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
                    ? "C:/ffmpeg/bin/ffmpeg.exe"
                    : "ffmpeg";

                $ffprobe = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'
                    ? "C:/ffmpeg/bin/ffprobe.exe"
                    : "ffprobe";

                // ---------------- GET RESOLUTION ----------------
                $probeCmd = "$ffprobe -v error -select_streams v:0 "
                    . "-show_entries stream=width,height -of csv=p=0:s=x "
                    . escapeshellarg($originalVideoPath);

                $resolution = trim(shell_exec($probeCmd));
                if (!$resolution) $resolution = "1280x720";
                [$w, $h] = explode("x", $resolution);

                // ---------------- THUMB SIZE LOGIC ----------------
                function resizeByMaxSide($w, $h, $max)
                {
                    if ($w >= $h) {
                        $nw = $max;
                        $nh = round(($h / $w) * $max);
                    } else {
                        $nh = $max;
                        $nw = round(($w / $h) * $max);
                    }
                    return floor($nw / 2) * 2 . "x" . floor($nh / 2) * 2;
                }

                $sizes = [
                    'small'  => resizeByMaxSide($w, $h, 300),
                    'medium' => resizeByMaxSide($w, $h, 600),
                    'large'  => resizeByMaxSide($w, $h, 1200),
                ];

                // ---------------- GENERATE THUMBNAILS ----------------
                $thumbnailPaths = [];
                foreach ($sizes as $size => $dim) {
                    $thumb = "{$baseDir}/{$size}.jpeg";
                    shell_exec("$ffmpeg -i " . escapeshellarg($originalVideoPath)
                        . " -ss 00:00:01 -vframes 1 -vf scale={$dim} "
                        . escapeshellarg($thumb) . " -y");

                    if (!file_exists($thumb)) {
                        throw new \Exception("Thumbnail failed: {$size}");
                    }
                    $thumbnailPaths[$size] = $thumb;
                }

                // ---------------- COMPRESS VIDEO ----------------
                $compressedPath = "{$baseDir}/{$videoBaseName}_compressed.mp4";
                shell_exec("$ffmpeg -i " . escapeshellarg($originalVideoPath)
                    . " -vcodec libx264 -crf 28 "
                    . escapeshellarg($compressedPath) . " -y");

                // ---------------- GENERATE HLS ----------------
                $hlsDir = "{$baseDir}/hls";
                if (!file_exists($hlsDir)) mkdir($hlsDir, 0755, true);

                $playlist = "{$hlsDir}/index.m3u8";
                shell_exec("$ffmpeg -i " . escapeshellarg($compressedPath)
                    . " -profile:v baseline -level 3.0 "
                    . " -start_number 0 -hls_time 2 -hls_list_size 0 "
                    . escapeshellarg($playlist) . " -y");

                if (!file_exists($playlist)) {
                    throw new \Exception("HLS generation failed");
                }

                // ---------------- UPLOAD TO S3 ----------------
                $s3 = [];

                $res = $this->uploadStreamingObject(
                    "videos/user_{$userId}/{$uniqueFolder}/{$renamedVideoFilename}",
                    $originalVideoPath
                );
                $s3['original'] = json_decode($res->getContent(), true)['data']['filePath'];

                $res = $this->uploadStreamingObject(
                    "videos/user_{$userId}/{$uniqueFolder}/{$videoBaseName}_compressed.mp4",
                    $compressedPath
                );
                $s3['compressed'] = json_decode($res->getContent(), true)['data']['filePath'];

                foreach ($thumbnailPaths as $size => $path) {
                    $res = $this->uploadStreamingObject(
                        "videos/user_{$userId}/{$uniqueFolder}/{$size}.jpeg",
                        $path
                    );
                    $s3['thumbnails'][$size] =
                        json_decode($res->getContent(), true)['data']['filePath'];
                }

                // ---------------- UPLOAD HLS FILES ----------------
                foreach (scandir($hlsDir) as $fileName) {
                    if (in_array($fileName, ['.', '..'])) continue;

                    $this->uploadStreamingObject(
                        "videos/user_{$userId}/{$uniqueFolder}/hls/{$fileName}",
                        "{$hlsDir}/{$fileName}"
                    );
                }

                \File::deleteDirectory($baseDir);

                return [
                    'original'     => $s3['original'],
                    'compressed'   => $s3['compressed'],
                    'thumbnails'   => $s3['thumbnails'],
                    'hls_playlist' => "videos/user_{$userId}/{$uniqueFolder}/hls/index.m3u8"
                ];
            }
            // ========================= IMAGE ============================
            elseif (in_array($fileExtension, $imgExtensions)) {

                $name = $this->sanitizeFileName($file->getClientOriginalName());
                $dir = public_path("assets/tmp_media/images/user_{$userId}/{$uniqueFolder}");
                // mkdir($dir, 0755, true);
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }

                $path = "{$dir}/{$name}";
                $file->move($dir, $name);

                $res = $this->uploadStreamingObject(
                    "images/user_{$userId}/{$uniqueFolder}/{$name}",
                    $path
                );

                \File::deleteDirectory($dir);
                return json_decode($res->getContent(), true)['data']['filePath'];
            }
            
            // ========================= AUDIO ============================
            elseif (in_array($fileExtension, $audioExtensions) || str_starts_with($mimeType, 'audio/')) {

                if ($fileExtension === 'bin' || $fileExtension === '') {
                    $fileExtension = explode('/', $mimeType)[1] ?? 'mp3';
                }

                $name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $fileName = $name.''.$timestamp;
                $final = "{$fileName}.{$fileExtension}";

                $dir = public_path("assets/tmp_media/audio/user_{$userId}");
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }

                $path = "{$dir}/{$final}";
                $file->move($dir, $final);

                $res = $this->uploadStreamingObject(
                    "audio/user_{$userId}/{$final}",
                    $path
                );

                unlink($path);
                return json_decode($res->getContent(), true)['data']['filePath'];
            }

            else {
                throw new \Exception("Unsupported file type: {$fileExtension}");
            }

        } catch (\Exception $e) {
            throw $e;
        }
    }




    
    
    private function sanitizeFileName($fileName) {
        // Replace any non-alphanumeric characters with an underscore
        $sanitizedFileName = preg_replace('/[^a-zA-Z0-9.-]/', '_', $fileName);
        return $sanitizedFileName;
    }
    

    
    public function uploadStreamingObject($key, $pathToFile)
    {
        // Open the file for reading
        $file = fopen($pathToFile, "r");
    
        try {
            // Upload the file to S3
            $result = $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key'    => $key,
                'Body'   => $file,
                // 'ACL'    => 'public-read', // Set access control as needed
            ]);
    
            fclose($file); // Close the file after the upload
    
            // Get the Object URL from the result
            $fileUrl = $result['ObjectURL']; // Use associative array access to get the URL
    
            // Return success response
            return response()->json([
                'message' => 'File uploaded successfully',
                'status' => 'success',
                'data' => [
                    'filePath' => $fileUrl,
                    'ETag' => $result['ETag'],
                    'effectiveUri' => $result['@metadata']['effectiveUri'],
                ]
            ], 200);
    
        } catch (\Exception $e) {
            fclose($file); // Ensure the file is closed even if there's an error
            return response()->json(['message' => $e->getMessage(), 'status' => 'failed'], 500);
        }
    }
    

}


