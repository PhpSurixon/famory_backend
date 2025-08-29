<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
 

    
    protected $table = "posts"; 
     
     
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'file',
        'video_formats',
        'post_type',
        'tag_id',
        'album_id',
        'media_type'
    ];

    protected $casts = [
        'video_formats' => 'array',
    ];
    protected $hidden = [
        'created_at',
        // 'updated_at',
        
    ];
    
    public function user() {
        return $this->belongsTo(User::class,'user_id','id')->select('id','first_name','last_name','image');
    }
   function album()
    {
        return $this->belongsTo(Album::class); // A post belongs to an album
    }

    
    public function scheduling_post() {
        return $this->hasOne(SchedulingPost::class,'post_id','id');
    }
    
    
    public function getFileAttribute($value)
    {
        // Define the CloudFront base URL
        $newBaseUrl = config('services.s3.cdn_url');

        // Check if the current value contains the old S3 URL
        $oldBaseUrl = 'https://fam-cam-output.s3.amazonaws.com';

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
    
    public function getVideoFormatsAttribute($value)
    {
        // Initialize as an empty array if $value is null
        $videoFormats = is_string($value) ? json_decode($value, true) : $value;

        // Check if $videoFormats is null and initialize as an empty array
        if ($videoFormats === null || (is_array($videoFormats) && empty($videoFormats))) {
            return null; // Return null instead of an empty array
        }

        // Define the old and new base URLs
        $oldBaseUrl = 'https://fam-cam-output.s3.amazonaws.com';
        $newBaseUrl = config('services.s3.cdn_url');

        // Iterate over the video formats and update the URLs
        foreach ($videoFormats as $key => $format) {
            if (is_array($format)) {
                // Recursively update the thumbnails if it's an array
                foreach ($format as $size => $url) {
                    $videoFormats[$key][$size] = $this->updateUrl($url, $oldBaseUrl, $newBaseUrl);
                }
            } else {
                // Update the URL for original and compressed formats
                $videoFormats[$key] = $this->updateUrl($format, $oldBaseUrl, $newBaseUrl);
            }
        }

        return $videoFormats ?? null;
    }

    // Helper method to update URL
    private function updateUrl($url, $oldBaseUrl, $newBaseUrl)
    {
        // If the URL contains the old base URL, replace it with the new base URL
        if (strpos($url, $oldBaseUrl) !== false) {
            return str_replace($oldBaseUrl, $newBaseUrl, $url);
        }

        // If it's a relative URL, prepend the new base URL
        if (strpos($url, '/') === 0) {
            return $newBaseUrl . $url;
        }

        // Otherwise, return the original value
        return $url ?? null;
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }
}
