<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    public const STATUSES = [
        'new',
        'contacted',
        'completed',
        'spam',
    ];

    public const SERVICES = [
        'personal_signature' => 'Chu ky so ca nhan',
        'business_signature' => 'Chu ky so doanh nghiep',
        'e_invoice' => 'Hoa don dien tu',
        'e_contract' => 'Hop dong dien tu',
        'other' => 'Khac',
    ];

    protected $fillable = [
        'name',
        'email',
        'phone',
        'company',
        'service',
        'message',
        'status',
    ];

    public function serviceLabel(): string
    {
        return self::SERVICES[$this->service] ?? 'Chua chon';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'contacted' => 'Da lien he',
            'completed' => 'Hoan thanh',
            'spam' => 'Spam',
            default => 'Moi',
        };
    }
}
