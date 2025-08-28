<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BurialInfo extends Model
{
    use HasFactory;

    protected $fillable = [
    'funeral_home',
    'address',
    'plot_number',
    'contact',
    'latitude',
    'longitude',
    'notes',
    'user_id',
    'burial_pdf_url'
];
  protected $hidden = [
        'created_at',
        'updated_at'
    ];
    
    
    public function getBurialPdfUrlAttribute($value)
    {
        // Define the CloudFront base URL
        $newBaseUrl = config('app.url');

        // Check if the current value contains the old S3 URL
        $oldBaseUrl = 'https://admin.famoryapp.com';

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


}
