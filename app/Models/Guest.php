<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class Guest extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'full_name', 'email', 'phone_number', 'country', 'password', 'referral_id',
        'category', // 'learner' or 'partner'

        // Partner fields
        'expertise_category', 'role_specialization', 'contribution_type',
        'short_bio', 'skills_or_tools',
        'linkedin_link', 'youtube_link', 'medium_blog_link', 'github_link', 'sample_work_path',

        // Learner fields
        'primary_interest_area', 'learning_goal',
        'guarantor_full_name', 'guarantor_phone_number', 'relationship_to_learner',
        'how_did_you_hear', 'coupon_code',
        
        // Common
        'agreed_to_terms',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'contribution_type' => 'array',
        'agreed_to_terms' => 'boolean',
        'password' => 'hashed',
    ];
    
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($guest) {
            if (empty($guest->referral_id)) {
                $guest->referral_id = self::generateReferralId($guest->full_name);
            }
        });
    }

    /**
     * Generate a unique referral ID.
     *
     * @param string $name
     * @return string
     */
    private static function generateReferralId(string $name): string
    {
        $nameParts = explode(' ', $name);
        $firstName = $nameParts[0];
        $lastName = count($nameParts) > 1 ? end($nameParts) : ($firstName[0] ?? 'G');
        
        $prefix = strtoupper(substr($firstName, 0, 3) . substr($lastName, 0, 3));
        
        do {
            $randomNumber = random_int(100, 999);
            $referralId = $prefix . $randomNumber;
        } while (static::where('referral_id', $referralId)->exists());
        
        return $referralId;
    }
}