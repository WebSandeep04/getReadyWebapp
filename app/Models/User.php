<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'address',
        'state_id',
        'city_id',
        'city',
        'age',
        'gstin',
        'is_gst',
        'gst_number',
        'aadhaar_number',
        'is_aadhaar_verified',
        'gender',
        'password',
        'profile_image',
        'last_login_at',
        'gst_legal_name',
        'gst_trade_name',
        'gst_constitution_of_business',
        'gst_status',
        'gst_registration_date',
        'gst_principal_address',
        'gst_nature_of_business',
        'gst_members',
        'gst_details',
        'aadhaar_masked_number',
        'aadhaar_address',
        'aadhaar_dob',
        'aadhaar_care_of',
        'aadhaar_xml_link',
        'aadhaar_pdf_link',
        'aadhaar_image_base64',
        'aadhaar_details',
    ];

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'gst_nature_of_business' => 'array',
            'gst_members' => 'array',
            'gst_details' => 'array',
            'aadhaar_address' => 'array',
            'aadhaar_details' => 'array',
        ];
    }

    /**
     * Get the cart items for the user.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the notifications for the user.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadNotificationsCount()
    {
        return $this->notifications()->unread()->count();
    }

    /**
     * Get the clothes for the user.
     */
    public function clothes()
    {
        return $this->hasMany(Cloth::class);
    }
    /**
     * Get the replies for the user.
     */
    public function replies()
    {
        return $this->hasMany(Reply::class);
    }

    public function ratingsReceived()
    {
        return $this->hasMany(Rating::class, 'rated_user_id');
    }

    public function getAverageRatingAttribute()
    {
        return round($this->ratingsReceived()->avg('rating') ?? 0, 1);
    }
}
