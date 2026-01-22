<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cloth extends Model
{
    use HasFactory;

    protected $table = 'clothes';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'gender',
        'brand',
        'fabric',
        'color',
        'bottom_type',
        'size',
        'fit_type',
        'condition',
        'defects',
        'is_cleaned',
        'is_approved',
        'resubmission_count',
        'rent_price',
        'purchase_value',
        'is_purchased',
        'security_deposit',
        'is_available',
        'chest_bust',
        'waist',
        'length',
        'shoulder',
        'sleeve_length',
    ];

    protected $casts = [
        'is_cleaned' => 'boolean',
        'is_available' => 'boolean',
        'is_purchased' => 'boolean',
        'rent_price' => 'decimal:2',
        'purchase_value' => 'decimal:2',
        'security_deposit' => 'decimal:2',
    ];

    /**
     * Get the user that owns the cloth.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the images for the cloth.
     */
    public function images()
    {
        return $this->hasMany(ClothImage::class);
    }

    /**
     * Get the cart items for this cloth.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the availability blocks for this cloth.
     */
    public function availabilityBlocks()
    {
        return $this->hasMany(AvailabilityBlock::class);
    }

    /**
     * Get the reviews for this cloth.
     */
    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    /**
     * Get the questions for this cloth.
     */
    public function questions()
    {
        return $this->hasMany(ProductQuestion::class);
    }

    /**
     * Get the average rating for this cloth.
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Get the total reviews count for this cloth.
     */
    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }

    // Optimization Relationships
    public function categoryRef() { return $this->belongsTo(Category::class, 'category'); }
    public function fabricRef() { return $this->belongsTo(FabricType::class, 'fabric'); }
    public function colorRef() { return $this->belongsTo(Color::class, 'color'); }
    public function sizeRef() { return $this->belongsTo(Size::class, 'size'); }
    public function bottomTypeRef() { return $this->belongsTo(BottomType::class, 'bottom_type'); }
    public function fitTypeRef() { return $this->belongsTo(BodyTypeFit::class, 'fit_type'); }
} 