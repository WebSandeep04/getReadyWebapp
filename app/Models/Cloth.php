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
        'category_id',
        'gender',
        'brand_id',
        'fabric_id',
        'color_id',
        'bottom_type_id',
        'size_id',
        'fit_type_id',
        'condition_id',
        'defects',
        'is_cleaned',
        'is_approved',
        'resubmission_count',
        'rent_price',
        'purchase_value',
        'is_purchased',
        'security_deposit',
        'is_available',
        'sku',
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
     * Get the order items for this cloth.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
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

    // Standard Relationships
    public function category() { return $this->belongsTo(Category::class, 'category_id'); }
    public function brand() { return $this->belongsTo(Brand::class, 'brand_id'); }
    public function fabric() { return $this->belongsTo(FabricType::class, 'fabric_id'); }
    public function color() { return $this->belongsTo(Color::class, 'color_id'); }
    public function size() { return $this->belongsTo(Size::class, 'size_id'); }
    public function bottomType() { return $this->belongsTo(BottomType::class, 'bottom_type_id'); }
    public function fitType() { return $this->belongsTo(BodyTypeFit::class, 'fit_type_id'); }
    public function condition() { return $this->belongsTo(GarmentCondition::class, 'condition_id'); }

    // Compatibility Aliases (Ref)
    public function categoryRef() { return $this->category(); }
    public function brandRef() { return $this->brand(); }
    public function fabricRef() { return $this->fabric(); }
    public function colorRef() { return $this->color(); }
    public function sizeRef() { return $this->size(); }
    public function bottomTypeRef() { return $this->bottomType(); }
    public function fitTypeRef() { return $this->fitType(); }
    public function conditionRef() { return $this->condition(); }
} 