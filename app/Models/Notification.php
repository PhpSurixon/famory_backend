<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
     protected $fillable = [
        'item_id',
        'isSeen',
        'session_id',
        'receiver_id',
        'type',
        'title',
        'message',
        'sender_id',
        'marked_user_id',
        'group_id',
        'has_actioned'
    ];

    public function sender(){
        
        return $this->belongsTo(User::class, 'sender_id');
    }
    public function reciver(){
        
        return $this->belongsTo(User::class, 'receiver_id');
    }

     public function group(){
        
        return $this->belongsTo(UserGroup::class,'group_id','id')->select('id','name');
    }
    
    
}

