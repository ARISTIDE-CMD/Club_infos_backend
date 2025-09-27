<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Message extends Model
{
    use HasFactory;

    /**
     * Les attributs qui peuvent être assignés en masse.
     * @var array<int, string>
     */
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'content',
    ];

    /**
     * Obtenir l'expéditeur du message.
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Obtenir le destinataire du message.
     */
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
