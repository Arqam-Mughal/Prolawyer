<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{

    protected $table = 'notifications';

    // Fields that can be mass-assigned
    protected $fillable = [
        'user_id',
        'worklist_id',
        'title',
        'message',
        'scheduled_at',
        'expires_at',
        'status',
    ];

    // Relationships

    /**
     * The user who receives the notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The related worklist (if any).
     */
    public function worklist()
    {
        return $this->belongsTo(Worklist::class);
    }
}
