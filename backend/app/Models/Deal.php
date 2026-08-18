<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Deal extends Model
{
    // Editorial Status Constants
    public const STATUS_DISCOVERED = 'DISCOVERED';
    public const STATUS_QUALIFIED = 'QUALIFIED';
    public const STATUS_DRAFT = 'DRAFT';
    public const STATUS_IN_REVIEW = 'IN_REVIEW';
    public const STATUS_PUBLISHED = 'PUBLISHED';
    public const STATUS_EXPIRED = 'EXPIRED';
    public const STATUS_ARCHIVED = 'ARCHIVED';
    public const STATUS_REJECTED = 'REJECTED';

    protected $fillable = [
        'category_id', 'merchant_id', 'brand_id', 'title', 'original_price', 
        'discounted_price', 'discount_percentage', 'amount_saved', 'price_drop', 'effective_price',
        'needs_brand_review', 'coupon_code', 'promo_code', 'url', 'short_url', 'image_path', 'status', 'brand',
        'features', 'verdict', 'trust_metrics', 'ai_caption', 'ai_score', 'slug', 'hash_id',
        'editorial_status', 'editorial_summary', 'editorial_verdict', 'pros', 'cons', 
        'best_for', 'not_for', 'editor_id', 'reviewed_at', 'is_editor_pick', 'typical_price'
    ];

    protected $dispatchesEvents = [
        'created' => \App\Events\DealCreated::class,
        'updated' => \App\Events\DealUpdated::class,
        'deleted' => \App\Events\DealDeleted::class,
    ];

    protected static function booted()
    {
        static::saved(function ($deal) {
            \Illuminate\Support\Facades\Cache::forget('recommendations_trending_5');
            \Illuminate\Support\Facades\Cache::forget('recommendations_trending_10');
            \Illuminate\Support\Facades\Cache::forget('recommendations_trending_15');
            \Illuminate\Support\Facades\Cache::forget('deals.assistant');
        });

        static::creating(function ($deal) {
            if (empty($deal->slug)) {
                $baseSlug = Str::slug($deal->title);
                $slug = $baseSlug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $baseSlug . '-' . $count++;
                }
                $deal->slug = $slug;
            }
            if (empty($deal->hash_id)) {
                $hash = Str::random(6);
                while (static::where('hash_id', $hash)->exists()) {
                    $hash = Str::random(6);
                }
                $deal->hash_id = $hash;
            }
            
            // Critical Scraper Safeguard
            if (!auth()->check() && $deal->editorial_status === self::STATUS_PUBLISHED) {
                $deal->editorial_status = self::STATUS_DISCOVERED; // Default to DISCOVERED if unauthenticated system tries to publish
            }
        });

        static::updating(function ($deal) {
            // Critical Scraper Safeguard
            if (!auth()->check() && $deal->isDirty('editorial_status') && $deal->editorial_status === self::STATUS_PUBLISHED) {
                $deal->editorial_status = $deal->getOriginal('editorial_status'); // Revert
            }
        });
    }

    protected $casts = [
        'features' => 'array',
        'trust_metrics' => 'array',
        'confidence_reasons' => 'array',
        'pros' => 'array',
        'cons' => 'array',
        'best_for' => 'array',
        'not_for' => 'array',
        'reviewed_at' => 'datetime',
        'is_editor_pick' => 'boolean'
    ];

    public function merchant()
    {
        return $this->belongsTo(Merchant::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * Alias for brand() relationship to avoid collision with 'brand' string column.
     * Use $deal->brandRelation->name in views to safely access the Brand model.
     */
    public function brandRelation()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function priceHistories()
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    public function collections()
    {
        return $this->belongsToMany(Collection::class);
    }

    /**
     * Generate a fallback slug for deals missing one in DB.
     * IMPORTANT: must only 'return', never assign $this->slug or saveQuietly()
     * to avoid infinite recursion.
     */
    public function getSlugAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        $baseSlug = \Illuminate\Support\Str::slug($this->attributes['title'] ?? '');
        return ($baseSlug ?: 'deal') . '-' . ($this->attributes['id'] ?? 0);
    }

    /**
     * Generate a fallback hash_id for deals missing one in DB.
     * Returns the numeric ID as string so links don't break.
     */
    public function getHashIdAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }
        return (string) ($this->attributes['id'] ?? 0);
    }

    public function resolveRouteBinding($value, $field = null)
    {
        if ($field === 'slug') {
            // Try exact slug match first
            $deal = $this->where('slug', $value)->first();
            if ($deal) {
                return $deal;
            }
            // Fallback: extract trailing ID from slug (e.g. "puma-sneakers-413" -> id 413)
            if (preg_match('/-(\d+)$/', $value, $matches)) {
                return $this->find($matches[1]);
            }
            return null;
        }
        
        if ($field === 'hash_id') {
            $deal = $this->where('hash_id', $value)->first();
            if ($deal) {
                return $deal;
            }
            // Numeric fallback: treat as ID
            if (is_numeric($value)) {
                return $this->find($value);
            }
            return null;
        }

        return parent::resolveRouteBinding($value, $field);
    }

    /**
     * Get the fully qualified absolute URL for the deal image.
     *
     * Production images are stored via Storage::disk('public') in storage/app/public/deals/
     * and served via the public/storage symlink at /storage/deals/filename.jpeg.
     * This requires `php artisan storage:link` which is run automatically by unzip.php on deploy.
     */
    public function getImageUrlAttribute(): string
    {
        $path = $this->attributes['image_path'] ?? '';
        if (empty($path)) {
            return asset('images/logo.png');
        }

        // Already a full HTTP/HTTPS URL — upgrade to https and return as-is
        if (filter_var($path, FILTER_VALIDATE_URL) || Str::startsWith($path, ['http://', 'https://'])) {
            return preg_replace('/^http:/i', 'https:', $path);
        }

        $cleanPath = ltrim($path, '/');

        // If path already starts with "storage/", don't double-prefix it
        if (Str::startsWith($cleanPath, 'storage/')) {
            // Check if file exists at public_path (via symlink)
            if (file_exists(public_path($cleanPath))) {
                return asset($cleanPath);
            }
            // Try the actual storage location
            $storageSub = Str::after($cleanPath, 'storage/');
            if (file_exists(storage_path('app/public/' . $storageSub))) {
                return asset($cleanPath);
            }
            // File doesn't exist — use the route-based fallback
            return asset($cleanPath);
        }

        // Non-storage paths (e.g. "deals/uuid.jpeg" in public/)
        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }

        // Fallback: try storage path
        if (file_exists(storage_path('app/public/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }

        // Last resort — return the asset URL anyway (may 404 but won't break)
        return asset('storage/' . $cleanPath);
    }

    /**
     * Helper to get the fully constructed affiliate URL.
     */
    public function getAffiliateUrlAttribute()
    {
        if (!empty($this->short_url)) {
            return $this->short_url;
        }

        $url = $this->url ?? '';
        if (empty($url)) {
            return '#';
        }
        
        // Check if it's an Amazon URL
        if (\Illuminate\Support\Str::contains($url, ['amazon.in', 'amazon.com'])) {
            $urlParts = parse_url($url);
            if (is_array($urlParts) && isset($urlParts['host'])) {
                $scheme = $urlParts['scheme'] ?? 'https';
                $host = $urlParts['host'];
                $path = $urlParts['path'] ?? '';
                
                $asin = null;
                if (preg_match('/\/dp\/([A-Z0-9]{10})/i', $path, $matches)) {
                    $asin = $matches[1];
                } elseif (preg_match('/\/gp\/product\/([A-Z0-9]{10})/i', $path, $matches)) {
                    $asin = $matches[1];
                }
                
                $queryParams = [];
                if (isset($urlParts['query'])) {
                    parse_str($urlParts['query'], $queryParams);
                }
                
                $queryParams['linkCode'] = 'll2';
                $queryParams['tag'] = 'kridaymart-21';
                
                if (!isset($queryParams['linkId'])) {
                    $queryParams['linkId'] = '9c069790f13a8b75cb7b4a3989e1698d';
                }
                
                $queryParams['ref_'] = 'as_li_ss_tl';
                unset($queryParams['pd_rd_w'], $queryParams['pd_rd_wg'], $queryParams['pd_rd_r'], $queryParams['pf_rd_p'], $queryParams['pf_rd_r']);
                
                $queryStr = http_build_query($queryParams);
                if ($asin) {
                    $path = '/dp/' . $asin;
                }
                
                return $scheme . '://' . $host . $path . ($queryStr ? '?' . $queryStr : '');
            }
        }

        $merchant = $this->merchant;
        if (!$merchant || empty($merchant->affiliate_param_key) || empty($merchant->store_id)) {
            return $url;
        }

        $separator = \Illuminate\Support\Str::contains($url, '?') ? '&' : '?';
        return $url . $separator . $merchant->affiliate_param_key . '=' . $merchant->store_id;
    }

    /**
     * Database scope mirroring the isPublishable() logic.
     */
    public function scopePublishable($query)
    {
        return $query->where('editorial_status', self::STATUS_PUBLISHED)
                     ->whereNotNull('editorial_summary')
                     ->whereNotNull('editorial_verdict')
                     ->whereNotNull('pros')
                     ->whereNotNull('cons')
                     ->whereNotNull('editor_id')
                     ->whereNotNull('reviewed_at');
    }

    /**
     * Publication Quality Firewall logic gate.
     * Determines if a deal has sufficient original value to exist publicly.
     */
    public function isPublishable(): bool
    {
        if ($this->editorial_status !== self::STATUS_PUBLISHED) return false;
        if (empty($this->editorial_summary) || empty($this->editorial_verdict)) return false;
        if (empty($this->pros) || empty($this->cons)) return false;
        if (empty($this->editor_id) || empty($this->reviewed_at)) return false;

        return true;
    }

    /**
     * Determines if the deal should be indexed by search engines.
     */
    public function isIndexable(): bool
    {
        if (!$this->isPublishable()) return false;
        
        if ($this->status === self::STATUS_EXPIRED) {
            // Expired deals are only indexable if they have substantial historical value
            // Proxy for high value: Editor's pick or lengthy editorial summary
            return $this->is_editor_pick || strlen((string)$this->editorial_summary) > 200;
        }

        return true;
    }

    /**
     * Determines if the deal is eligible for AdSense.
     */
    public function isAdsEligible(): bool
    {
        return $this->isIndexable() && $this->status === 'active';
    }

    /**
     * Editor's Pick logic gate.
     * Must be publishable AND explicitly flagged.
     */
    public function isEditorPick(): bool
    {
        return $this->isPublishable() && $this->is_editor_pick;
    }
}
