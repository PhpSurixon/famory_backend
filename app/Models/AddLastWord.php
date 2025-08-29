<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AddLastWord extends Model
{
    use HasFactory;

    protected $fillable = [
        'video',
        'user_id',
    ];
    protected $hidden = [
      
        'created_at',
    ];
    
    protected $appends = ['original']; 
    
    public function getVideoAttribute($value)
    {
        // Define the CloudFront base URL
        $newBaseUrl = config('services.s3.cdn_url');

        // Check if the current value contains the old S3 URL
        $oldBaseUrl = 'https://famorys3.s3.amazonaws.com';

        // If the image path starts with a slash (relative path), prepend the new base URL
        if (strpos($value, '/') === 0) {
            return $newBaseUrl . $value;
        }

        // If the image path contains the old S3 URL, replace it with the new CloudFront URL
        if (strpos($value, $oldBaseUrl) !== false) {
            return str_replace($oldBaseUrl, $newBaseUrl, $value);
        }

        // Otherwise, return the original value
        return $value ?? '';
    }
    
    
    public function getOriginalAttribute()
    {
        // Define the CloudFront base URL
        $newBaseUrl = config('services.s3.cdn_url'); // Fetch the base URL from the config
        
        // The value you want to dynamically generate (e.g., video path)
        $value = "/videos/user_{$this->id}/1728025378535/video.mp4"; // You can modify the path generation logic based on your app

        // Old S3 base URL
        $oldBaseUrl = 'https://famorys3.s3.amazonaws.com';

        // Check if the current value is a relative path and add the new base URL
        if (strpos($value, '/') === 0) {
            return $newBaseUrl . $value;
        }

        // If the path contains the old S3 URL, replace it with the new CloudFront URL
        if (strpos($value, $oldBaseUrl) !== false) {
            return str_replace($oldBaseUrl, $newBaseUrl, $value);
        }

        // Return the generated path or empty string if null
        return $value ?? '';
    }


    
}
