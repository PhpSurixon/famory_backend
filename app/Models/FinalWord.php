<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinalWord extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'title', 'video_path','video_formats','isPotrait'];

    protected $casts = [
        'video_formats' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getVideoPathAttribute($value)
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

    public function getVideoFormatsAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        // Decode only if JSON
        if (is_string($value)) {
            $decoded = json_decode($value, true);

            // If not valid JSON, treat it as a single URL
            if (json_last_error() !== JSON_ERROR_NONE) {
                return $this->updateUrl(
                    $value,
                    'https://famorys3.s3.amazonaws.com',
                    config('services.s3.cdn_url')
                );
            }

            $videoFormats = $decoded;
        } else {
            $videoFormats = $value;
        }

        // Final safety check
        if (!is_array($videoFormats) || empty($videoFormats)) {
            return null;
        }

        $oldBaseUrl = 'https://famorys3.s3.amazonaws.com';
        $newBaseUrl = config('services.s3.cdn_url');

        foreach ($videoFormats as $key => $format) {
            if (is_array($format)) {
                foreach ($format as $size => $url) {
                    $videoFormats[$key][$size] = $this->updateUrl($url, $oldBaseUrl, $newBaseUrl);
                }
            } elseif (is_string($format)) {
                $videoFormats[$key] = $this->updateUrl($format, $oldBaseUrl, $newBaseUrl);
            }
        }

        return $videoFormats;
    }


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
}
