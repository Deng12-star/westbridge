<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\HasApiTokensWhenAvailable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Staff. Customers are a separate model from Phase 5 — a person who buys a
 * laptop should never end up in the same table as someone who can issue an
 * invoice.
 */
class User extends Authenticatable
{
    use HasApiTokensWhenAvailable, HasFactory, HasRoles, Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'job_title',
        'is_active',
        'last_login_at',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /** Staff who can open the admin panel: active, with at least one role. */
    public function canUseAdmin(): bool
    {
        return (bool) ($this->is_active ?? true) && $this->roles()->exists();
    }

    /**
     * Ends every API token this person holds. Called whenever their access
     * changes - deactivated, deleted, new role or new password.
     */
    public function revokeApiTokens(): void
    {
        if (method_exists($this, 'tokens')) {
            $this->tokens()->delete();
        }
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('Super Admin');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function leadNotes(): HasMany
    {
        return $this->hasMany(LeadNote::class);
    }

    /**
     * Where a WhatsApp notification would be delivered once a provider is
     * configured. See App\Notifications\Channels\WhatsAppChannel.
     */
    public function routeNotificationForWhatsApp(): ?string
    {
        return $this->phone;
    }
}
