<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TrustedPartners extends Model
{
    protected $table = "trusted_partners"; 
     
     
    protected $fillable = [
        'category',
        'company_name',
        'city',
        'state',
        'zip_code',
        'phone',
        'website',
        'logo',
        'created_by'
    ];


    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function isFeaturedPartner():HasOne
    {
        return $this->hasOne(FeaturedPartner::class,'trusted_partner_id','id');
    }
    
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    
    
    public function getLogoAttribute($value)
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