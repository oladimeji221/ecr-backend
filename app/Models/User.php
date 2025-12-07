<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'first_name',
        'middle_name',
        'surname',
        'email',
        'phone_number',
        'department',
        'position',
        'role',
        'password',
        'profile_photo_path',
        'bio',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
        'is_admin',
    ];

    /**
     * Create an is_admin attribute.
     *
     * @return bool
     */
    public function getIsAdminAttribute()
    {
        return $this->role === 'admin';
    }

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
        ];
    }

    protected static function booted()
    {
        static::creating(function ($user) {
            // Combine first, middle, and last name into the name field
            $nameParts = array_filter([
                $user->first_name,
                $user->middle_name,
                $user->surname
            ]);
            $user->name = implode(' ', $nameParts);
        });

        static::updating(function ($user) {
            if ($user->isDirty(['first_name', 'middle_name', 'surname'])) {
                $nameParts = array_filter([
                    $user->first_name,
                    $user->middle_name,
                    $user->surname
                ]);
                $user->name = implode(' ', $nameParts);
            }
        });
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Override the profile photo URL accessor to handle Cloudinary URLs
     * 
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function profilePhotoUrl(): Attribute
    {
        return Attribute::get(function (): string {
            if (!$this->profile_photo_path) {
                return $this->defaultProfilePhotoUrl();
            }

            // If it's already a Cloudinary URL (starts with http:// or https://), return it directly
            if (str_starts_with($this->profile_photo_path, 'http://') || str_starts_with($this->profile_photo_path, 'https://')) {
                return $this->profile_photo_path;
            }

            // Otherwise, it's a local storage path, use the default Storage URL
            return Storage::disk($this->profilePhotoDisk())->url($this->profile_photo_path);
        });
    }

    /**
     * Get the default profile photo URL if no profile photo has been uploaded.
     *
     * @return string
     */
    protected function defaultProfilePhotoUrl()
    {
        $name = trim(collect(explode(' ', $this->name))->map(function ($segment) {
            return $segment;
        })->join(' '));

        return 'https://ui-avatars.com/api/?name='.urlencode($name).'&color=7F9CF5&background=EBF4FF';
    }
}
