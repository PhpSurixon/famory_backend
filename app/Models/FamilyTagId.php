<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FamilyTagId extends Model
{

    protected $table = "family_tag_ids";


    // protected $fillable = [
    //     'user_id',
    //     'image',
    //     'family_tag_id',
    //     'created_user_id'
    // ];
    protected $fillable = [
        'user_id',
        'family_tag_id',
        'image',
        'title',
        'description',
        'privacy_type',
        'avatar',
        'family_tag_id',
        'created_user_id'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',

    ];


    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id')->withTrashed()->select('id', 'first_name', 'last_name', 'image');
    }

    public function createdUser()
    {
        return $this->belongsTo(User::class, 'created_user_id', 'id')->withTrashed()->select('id', 'first_name', 'last_name', 'image');
    }

    public function tagCollaborators()
    {
        return $this->hasMany(TagCollaborator::class, 'family_tag_id');
    }

    public function familyTag()
{
    return $this->belongsTo(FamilyTagId::class, 'family_tag_id', 'id');
}

public function savedTags()
{
    return $this->hasMany(SavedTag::class, 'family_tag_id', 'id');
}

public function creator()
    {
        return $this->belongsTo(User::class, 'created_user_id');
    }
    public function getImageAttribute($value)
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

    // Accessor for 'avatar' field
    public function getAvatarAttribute($value)
    {
        // If avatar is null, use the image field
        return $this->getImageAttribute($value ?? $this->image);
    }

    // Optionally, you can define a method to automatically set avatar to image on save
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->image) {
                $model->avatar = $model->image;
            }
        });
    }




}
