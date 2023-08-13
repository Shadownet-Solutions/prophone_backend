<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender',
        'number',
        'content',
        'sender',
        'receiver',
        'workspace',
        'status',
        'type'
    ];

    public function getInbox($number){
        $latestMessages = Message::select('messages.*')
            ->where('number', $number)
            ->joinSub(
                Message::select('receiver', DB::raw('MAX(created_at) as max_created_at'))
                    ->groupBy('receiver'),
                'latest',
                function ($join) {
                    $join->on('messages.receiver', '=', 'latest.receiver')
                        ->andOn('messages.created_at', '=', 'latest.max_created_at');
                }
            )
            ->orderBy('messages.created_at', 'desc')
            ->take(10)
            ->get();
            
            return $latestMessages;
    }
}
