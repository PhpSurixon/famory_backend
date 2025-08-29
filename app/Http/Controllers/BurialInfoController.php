<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\BurialInfo;
use App\Models\AddLastWord;
use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
// require '/home3/famcamb/public_html/backend/vendor/vendor/autoload.php';

require '../vendor/autoload.php';
use Google\Cloud\Storage\StorageClient;
use Illuminate\Support\Facades\Validator;
// require '/home3/famcamb/public_html/backend/vendor/domPDF/autoload.php';

 require '../vendor/domPDF/autoload.php';
use Dompdf\Options;
use Dompdf\Dompdf;
use App\Services\UploadImage;

class BurialInfoController extends Controller
{
    
    
    protected $storageClient;

    public function __construct(UploadImage $UploadImage)
    {
        $this->UploadImage = $UploadImage;
        
    }
 
    
    private function getFolderName($extension)
    {
        $videoExtensions = ['mp4', 'mov', 'avi','MP4','MOV','AVI'];
        if (in_array($extension, $videoExtensions)) {
            return 'videos';
        } else {
            return 'files';
        }
    }

    
    
    public function createBurialInfo(Request $request)
    {
         try {
        $current_user = Auth::user();
        $validatedData = $request->validate([
            'funeral_home' => 'nullable|string',
            'address' => 'nullable|string',
            'plot_number' => 'nullable|string',
            'contact' => 'nullable|numeric',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        if($request->id){
            $burialInfo = BurialInfo::find($request->id);
            if(!$burialInfo){
                return response()->json(['message' => 'Burial information not available', "status" => "success", "error_type" => "", 'data' => null], 200);
            }
            $burialInfo->funeral_home = $validatedData['funeral_home'] ?? null;
            $burialInfo->address = $validatedData['address']?? null;
            $burialInfo->plot_number = $validatedData['plot_number']?? null;
            $burialInfo->contact = $validatedData['contact']?? null;
            $burialInfo->latitude = $validatedData['latitude']?? null;
            $burialInfo->longitude = $validatedData['longitude']?? null;
            $burialInfo->notes = $validatedData['notes'] ?? null;
            $burialInfo->user_id = (string)$burialInfo->user_id ?? null;
            $burialInfo->family_member_id = (string)Auth::user()->id ?? null;
            $pdfUrl = $this->getBurailpdf($current_user,$burialInfo);
            $burialInfo->burial_pdf_url = $pdfUrl ?? null;
            $burialInfo->save();
        }else{
            $burialInfo = $current_user->burialinfo;
            if ($burialInfo) {
                $burialInfo->update([
                    'funeral_home' => $validatedData['funeral_home'] ?? null,
                    'address' => $validatedData['address'] ?? null,
                    'plot_number' => $validatedData['plot_number'] ?? null,
                    'contact' => $validatedData['contact'] ?? null,
                    'latitude' => $validatedData['latitude'] ?? null,
                    'longitude' => $validatedData['longitude'] ?? null,
                    'notes' => $validatedData['notes'] ?? null,
                ]); 
                $burial_pdf_url =  $this->getBurailpdf($current_user,$burialInfo) ?? null;
                
                $burialInfo->update(['burial_pdf_url' =>$burial_pdf_url]);
                
            } else {
                // Create a new BurialInfo instance
                $burialInfo = new BurialInfo();
                $burialInfo->funeral_home = $validatedData['funeral_home'] ?? null;
                $burialInfo->address = $validatedData['address'] ?? null;
                $burialInfo->plot_number = $validatedData['plot_number'] ?? null;
                $burialInfo->contact = $validatedData['contact'] ?? null;
                $burialInfo->latitude = $validatedData['latitude'] ?? null;
                $burialInfo->longitude = $validatedData['longitude'] ?? null;
                $burialInfo->notes = $validatedData['notes'] ?? null;
                $pdfUrl = $this->getBurailpdf($current_user, $burialInfo);
                $burialInfo->burial_pdf_url = $pdfUrl ?? null;
                $current_user->burialinfo()->save($burialInfo);
            }
        
        }

        return response()->json(['message' => 'Burial information ' . ($burialInfo->wasRecentlyCreated ? 'added' : 'updated') . ' successfully', "status" => "success",
                    "error_type" => "", 'data' => $burialInfo], 201);
         } catch (\Exception $e) {
        return response()->json([
            'message' => 'Error occurred while saving burial information',
            'status' => 'error',
            'error_type' => get_class($e),
            'error_message' => $e->getMessage()
        ], 500);
    }
    }
    

    public function AddLastWords(Request $request)
    {
    $validator = Validator::make($request->all(), [
        'video' => 'required|file',
    ]);
    if ($validator->fails()) {
        $errors = $validator->errors();
        foreach ($errors->all() as $key => $value) {
            return response()->json(['message' => $value, 'status' => 'failed'], 400);
        }
    }

    try {
        $current_user = Auth::user();
        $userId = $current_user->id;
         
        if ($request->hasFile('video') && $request->file('video')->isValid()) {
            $file = $request->file('video');
           
            $extension = $file->getClientOriginalExtension();
            $folder = $this->getFolderName($extension);
            $userId = Auth::id();
            $res = $this->UploadImage->saveMedia($file,$userId);
            
            $addLastWord = AddLastWord::where('user_id', $userId)->first();
            if($addLastWord){
              $addLastWord->video = $res['original']; 
               $addLastWord->save(); 
            }else{
                $addLastWord = new AddLastWord();
                $addLastWord->video = $res['original'];
                $addLastWord->user_id = $current_user->id;
                 $addLastWord->save(); 
            }
            
            // $addLastWord->save();
            
            return response()->json([
                'message' => 'Video uploaded successfully.',
                'status' => 'success',
                'error_type' => '',
                'data' => [
                    'id' => $addLastWord->id,
                    'user_id' => (string)$addLastWord->user_id,
                    'video' => $addLastWord->video,
                    'original' => $res['original'],
                    'updated_at' => $addLastWord->updated_at,
                ]
            ], 200);

        } else {
            throw new \Exception('No file uploaded.');
        }
    } catch (\Exception $e) {
        return response()->json([
            'message' => $e->getMessage(),
            'status' => 'fail',
            'error_type' =>"",
            'data' => null
        ], 500);
    }
}


    public function getBurailpdf($current_user, $burialInfo)
    {
        $current_user = $current_user;
        $burialInfo = $burialInfo;
        $user = User::find($current_user->id);
        $username = trim($user->first_name . ' ' . $user->last_name);
        $latitude = $burialInfo->latitude;
        $longitude = $burialInfo->longitude;
        $mapUrl = "https://static-maps.yandex.ru/1.x/?ll=$longitude,$latitude&size=600,300&z=15&l=map&pt=$longitude,$latitude,pm2rdm";
       
        $mapImage = file_get_contents($mapUrl);
        $mapBase64 = base64_encode($mapImage);
        $mapImgSrc = "data:image/png;base64,$mapBase64";
        $data = [
            'burialInfo' => $burialInfo,
            'username' => $username,
            'userImage' => $user->image,
            'mapImgSrc' => $mapImgSrc,
        ];
        $pdf = new \Dompdf\Dompdf();
        $pdf->setPaper('A4', 'portrait');
        $view = view('admin.burialInfo', $data)->render();
        $pdf->loadHtml($view);
        $pdf->render();
        
        
        
        $canvas = $pdf->getCanvas(); 
         
        // Get height and width of page 
        $w = $canvas->get_width(); 
        $h = $canvas->get_height(); 
         
        // Specify watermark image 
        $imageURL = 'https://admin.famoryapp.com/assets/img/fam-cam-logo.png'; 
        $imgWidth = 300; 
        $imgHeight = 300; 
         
        // Set image opacity 
        $canvas->set_opacity(.10); 
         
        // Specify horizontal and vertical position 
        $x = (($w-$imgWidth)/2); 
        $y = (($h-$imgHeight)/2); 
         
        // Add an image to the pdf 
        $canvas->image($imageURL, $x, $y, $imgWidth, $imgHeight); 
        $pdfContent = $pdf->output();
        $app_url = config('app.url');
        // $app_url = "https://admin.famoryapp.com";
        $pdfDirectory = public_path('report');
        
        $pdfFilename = 'burial_info_' . time() . '.pdf';
    
        if (!is_dir($pdfDirectory)) {
            mkdir($pdfDirectory, 0755, true);
        }
    
        file_put_contents($pdfDirectory . '/' . $pdfFilename, $pdfContent);
        // $pdfUrl = $app_url . '/report/' . $pdfFilename;
        $pdfUrl = '/report/' . $pdfFilename;
        return $pdfUrl;
        // return response()->json(["message" => "PDF generated successfully", "status" => "success", "data" => ['pdf_url' => $pdfUrl, 'mapUrl'=> $mapUrl]], 200);
    }

    
}
