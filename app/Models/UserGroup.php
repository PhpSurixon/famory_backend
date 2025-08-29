<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserGroup extends Model
{
 

    
    protected $table = "user_groups"; 
     
     
    protected $fillable = [
        'name',
        'image',
        'user_id'
    ];


    protected $hidden = [
        'created_at',
        'updated_at',
        
    ];
    
    public function addUserGroups() {
        return $this->hasMany(AssignUserGroup::class);
    }
    
    
    public function user() {
        return $this->belongsTo(User::class)->select('id','first_name','last_name','image');
    }
    
    
    public function getImageAttribute($value)
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
