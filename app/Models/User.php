<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

use App\Models\School;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
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
        'email',
        'password',
    ];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolApplication()
    {
        return $this->hasMany(SchoolApplication::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isSchoolAdmin(): bool
    {
        return in_array($this->role, ['school_admin', 'promoteur'], true);
    }

    public function canManageSchoolSettings(): bool
    {
        return $this->isSchoolAdmin() || in_array($this->role, ['directeur', 'censeur'], true);
    }

    public const MODULES = [
        'dashboard', 'school-years', 'students', 'teachers', 'classes', 'subjects',
        'teaching-assignments', 'timetable', 'finances', 'notes', 'parents',
        'documents', 'reports', 'updates',
    ];

    private const ROLE_DENIED = [
        'directeur' => ['finances', 'reports'],
        'censeur' => ['school-years', 'finances', 'reports'],
        'surveillant' => ['school-years', 'finances', 'teaching-assignments', 'notes', 'documents'],
    ];

    public static function invitableRoles(): array
    {
        return [
            'promoteur' => 'Promoteur',
            'maternelle' => 'Maternelle',
            'premier_cycle' => '1er cycle',
            'second_cycle' => '2è cycle',
            'directeur' => 'Directeur',
            'censeur' => 'Censeur',
            'surveillant' => 'Surveillant',
            'comptable' => 'Comptable',
        ];
    }

    public static function lyceeStaffRoles(): array
    {
        return [
            'directeur' => 'Directeur',
            'censeur' => 'Censeur',
            'comptable' => 'Comptable',
            'surveillant' => 'Surveillant',
        ];
    }

    public function canManageSchoolTeam(): bool
    {
        return $this->role === 'promoteur'
            && $this->school !== null
            && School::includesLycee((array) $this->school->cycles);
    }

    public static function schoolCreationRoles(): array
    {
        return [
            'promoteur' => 'Promoteur',
            'directeur' => 'Directeur',
            'censeur' => 'Censeur',
            'surveillant' => 'Surveillant',
            'comptable' => 'Comptable',
        ];
    }

    public function roleLabel(): string
    {
        return match (true) {
            $this->role === 'school_admin' => 'Administrateur',
            $this->role === 'super_admin' => 'Super administrateur',
            isset(self::invitableRoles()[$this->role]) => self::invitableRoles()[$this->role],
            default => 'Personnel',
        };
    }

    public function canAccess(string $module): bool
    {
        $school = $this->school;
        if (!$school || !School::includesLycee((array) $school->cycles)) {
            return true;
        }

        if ($this->role === 'comptable') {
            return in_array($module, ['dashboard', 'students', 'teachers', 'classes', 'finances', 'reports'], true);
        }

        return !in_array($module, self::ROLE_DENIED[$this->role] ?? [], true);
    }

    public function homeRoute(): string
    {
        if (
            $this->role === 'comptable'
            && $this->school
            && School::includesLycee((array) $this->school->cycles)
        ) {
            return 'dashboard';
        }

        return match ($this->role) {
            'super_admin' => 'owner.dashboard',
            'comptable' => 'finances.index',
            default => 'dashboard',
        };
    }

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
        ];
    }
}
