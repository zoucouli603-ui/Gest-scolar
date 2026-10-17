<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    public const CYCLES = ['Maternelle', '1er Cycle', '2è Cycle', 'Lycée'];
    public const SUBSCRIPTION_PLANS = [
        'free' => 'Gratuit',
        'starter' => 'Starter',
        'essentiel' => 'Essentiel',
        'professionnel' => 'Professionnel',
        'personnalise' => 'Personnalisé',
    ];
    public const SUBSCRIPTION_STATUSES = [
        'trial' => 'Essai',
        'active' => 'Actif',
        'past_due' => 'En attente de paiement',
        'cancelled' => 'Résilié',
    ];

    public const CARD_DEFAULTS = [
        'bg_color' => '#2563eb',
        'text_color' => '#ffffff',
        'line1' => 'République du Mali',
        'line2' => 'Un Peuple · Un But · Une Foi',
        'line3' => 'Ministère de l\'Éducation Nationale',
        'title' => 'Carte de l\'élève',
    ];

    protected $fillable = [
        'name',
        'director_name',
        'logo_path',
        'address',
        'phone',
        'email',
        'country',
        'province',
        'currency',
        'institution_type',
        'establishment_type',
        'cycles',
        'website',
        'city',
        'commune',
        'district',
        'subdivision',
        'academy',
        'cap',
        'card_settings',
        'inspection',
        'maintenance_mode',
        'appearance',
        'owner_user_id',
        'last_activity_at',
        'access_status',
        'suspended_at',
        'suspension_reason',
        'subscription_plan',
        'subscription_status',
        'subscription_starts_at',
        'subscription_ends_at',
        'subscription_note',
    ];

    protected function casts(): array
    {
        return [
            'last_activity_at' => 'datetime',
            'maintenance_mode' => 'boolean',
            'appearance' => 'array',
            'cycles' => 'array',
            'card_settings' => 'array',
            'suspended_at' => 'datetime',
            'subscription_starts_at' => 'date',
            'subscription_ends_at' => 'date',
        ];
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function schoolYears()
    {
        return $this->hasMany(SchoolYear::class);
    }

    public function currentSchoolYear()
    {
        return $this->hasOne(SchoolYear::class)->where('is_current', true);
    }

    public function invitations()
    {
        return $this->hasMany(SchoolInvitation::class);
    }

    public function photos()
    {
        return $this->hasMany(SchoolPhoto::class);
    }

    public function ownerActions()
    {
        return $this->hasMany(OwnerAdminAction::class);
    }

    public static function normalizeCycle(string $cycle): string
    {
        return match ($cycle) {
            'Primaire' => '1er Cycle',
            'Secondaire' => '2è Cycle',
            default => $cycle,
        };
    }

    public static function normalizeCycles(array $cycles): array
    {
        return array_values(array_unique(array_map(fn ($c) => self::normalizeCycle($c), $cycles)));
    }

    public static function includesLycee(array $cycles): bool
    {
        return in_array('Lycée', self::normalizeCycles($cycles), true);
    }

    public static function includesFundamental(array $cycles): bool
    {
        return count(array_intersect(self::normalizeCycles($cycles), ['1er Cycle', '2è Cycle'])) > 0;
    }

    /** Règles de validation des champs Académie / CAP selon les cycles choisis. */
    public static function academyRules(array $input): array
    {
        $cycles = (array) ($input['cycles'] ?? []);

        return [
            'academy' => [
                self::includesLycee($cycles) || self::includesFundamental($cycles) ? 'required' : 'nullable',
                'string',
                'max:150',
            ],
            'cap' => [
                self::includesFundamental($cycles) ? 'required' : 'nullable',
                'string',
                'max:150',
            ],
        ];
    }

    /** Le Lycée est exclusif : incompatible avec Maternelle, 1er et 2è Cycle. */
    public static function exclusiveLyceeRule(): \Closure
    {
        return function (string $attribute, mixed $value, \Closure $fail): void {
            $cycles = self::normalizeCycles((array) $value);
            if (in_array('Lycée', $cycles, true) && count($cycles) > 1) {
                $fail('La sélection « Lycée » est exclusive : elle ne peut pas être combinée avec Maternelle, 1er Cycle ou 2è Cycle.');
            }
        };
    }

    public function supportsCycle(string $cycle): bool
    {
        $normalized = self::normalizeCycle($cycle);
        $cycles = array_map(fn ($c) => self::normalizeCycle($c), $this->cycles ?? self::CYCLES);

        return in_array($normalized, $cycles, true);
    }
}
