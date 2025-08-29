<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Album extends Model
{

    protected $fillable = [
        'album_name',
        'album_cover',
    ];
    
    
    protected $hidden = [
        'created_at',
        'updated_at',
    ];
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'album_posts', 'album_id', 'post_id')
                    ->withPivot('user_id')  // Including user_id in the pivot table
                    ->withTimestamps();     // Optionally track created/updated timestamps
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
        public function getAlbumCoverAttribute($value)
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

