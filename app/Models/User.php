<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'role_id',
        'phone',
        'image',
        'ban_user',
        'username',
        'gender',
        'dob', 
        'is_dead', 
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'created_at',
        'updated_at',
        // 'deleted_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }


    public function assignusergroup()
    {
        return $this->hasMany(AssignUserGroup::class, 'user_id', 'id');
    }

    public function commentlikes()
    {
        return $this->hasMany(CommentLike::class);
    }


    public function group()
    {
        return $this->hasMany(AssignUserGroup::class, 'user_id', 'id');
    }

    // public function getImageAttribute($value)
    // {
    //     return $value ?? '';
    // }
    public function album()
    {
        return $this->hasMany(Album::class);
    }

    public function post()
    {
        return $this->hasMany(Post::class);
    }

    public function burialinfo()
    {
        return $this->hasOne(BurialInfo::class);
    }
    public function last_will_url()
    {
        return $this->hasOne(AddLastWord::class);
    }

    public function userLiveStatus()
    {
        return $this->hasOne(UserLiveStatus::class);
    }

    public function stripeDetails()
    {
        return $this->hasMany(StripeDetail::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->select('id', 'user_id', 'subscription', 'platform', 'expiry_date');
    }

    public function tagCollaborators()
    {
        return $this->hasMany(TagCollaborator::class, 'user_id');
    }

    public function savedTags()
    {
        return $this->hasMany(SavedTag::class, 'user_id', 'id');
    }
    
    public function getImageAttribute($value)
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


    public function getLastWillAttribute($value)
    {
        // Define the base URL you want to prepend to the last_will path
        $newBaseUrl = config('services.s3.cdn_url');

        // Check if the last_will is not null and prepend the base URL
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


    // Followers (users who follow me)
    public function followers()
    {
        return $this->hasMany(Follow::class, 'following_id');
    }

    // Following (users I follow)
    public function following()
    {
        return $this->hasMany(Follow::class, 'follower_id');
    }
    
    
    // Helper: full name
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }




}
