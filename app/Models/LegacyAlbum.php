<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LegacyAlbum extends Model
{
    protected $table = "legacy_albums"; 
    protected $fillable = [
        'user_id',
        'shared_with_id',
        'title',
        'conver_image',
        'type',
        'approval_status',
        'payment_status',
        'payment_id',
        'is_deleted',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function sharedWith()
    {
        return $this->belongsTo(User::class, 'shared_with_id');
    }

    public function posts()
    {
        return $this->hasMany(LegacyAlbumPost::class, 'legacy_album_id');
    }

    public function getConverImageAttribute($value)
    {
        // Define the CloudFront base URL
        $newBaseUrl = config('services.s3.cdn_url');

        // Check if the current value contains the old S3 URL
        $oldBaseUrl = 'https://famorys3.s3.amazonaws.com';

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
