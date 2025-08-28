<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{

    protected $fillable = [
        'ad_name',
        "start_date",
        "expiration",
        "zip_code",
        "action_button_text",
        "action_button_link",
        "full_screen_image",
        "banner_image",
        "user_id",
        "is_national",
        "reminder_email_sent",
        "is_archieved",
    ];
    
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
   
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function getFullScreenImageAttribute($value)
    {
        return $this->formatImageUrl($value);
    }
    
    public function getBannerImageAttribute($value)
    {
        return $this->formatImageUrl($value);
    }

    public function formatImageUrl($value)
    {
        // Define the CloudFront base URL
        $newBaseUrl = config('services.s3.cdn_url');

        // Check if the current value contains the old S3 URL
        $oldBaseUrl = 'https://fam-cam-output.s3.amazonaws.com';

        // Check if the value is null or empty
        if (empty($value)) {
            return ''; // Return an empty string if there is no image value
        }

        // If the image path starts with a slash (relative path), prepend the new base URL
        if (strpos($value, '/') === 0) {
            // dd($newBaseUrl.$value);
            return $newBaseUrl.$value; // Use ltrim to remove the leading slash
        }

        // If the image path contains the old S3 URL, replace it with the new CloudFront URL
        if (strpos($value, $oldBaseUrl) !== false) {
            return str_replace($oldBaseUrl, $newBaseUrl, $value);
        }

        // Otherwise, return the original value
        return $value;
    }
    
}

