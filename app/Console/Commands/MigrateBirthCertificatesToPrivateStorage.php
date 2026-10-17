<?php

namespace App\Console\Commands;

use App\Models\Student;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class MigrateBirthCertificatesToPrivateStorage extends Command
{
    protected $signature = 'students:migrate-private-birth-certificates {--apply : Move existing certificates from public to private storage}';

    protected $description = 'Move legacy public birth certificates to private storage';

    public function handle(): int
    {
        $students = Student::withoutGlobalScopes()
            ->whereNotNull('birth_certificate_path')
            ->get(['id', 'birth_certificate_path']);

        $migrated = 0;
        $skipped = 0;

        foreach ($students as $student) {
            $path = (string) $student->birth_certificate_path;

            if (!preg_match('#^schools/\d+/students/birth-certificates/[^/]+$#D', $path)) {
                $this->warn("Élève {$student->id} ignoré : chemin d’extrait invalide.");
                $skipped++;
                continue;
            }

            if (!Storage::disk('public')->exists($path)) {
                if (Storage::disk('private')->exists($path)) {
                    continue;
                }

                $this->warn("Élève {$student->id} ignoré : fichier source introuvable.");
                $skipped++;
                continue;
            }

            if (!$this->option('apply')) {
                $this->line("À migrer : élève {$student->id} ({$path})");
                $migrated++;
                continue;
            }

            if (Storage::disk('private')->exists($path)) {
                $this->warn("Élève {$student->id} ignoré : le fichier privé existe déjà.");
                $skipped++;
                continue;
            }

            $stream = Storage::disk('public')->readStream($path);
            if ($stream === false) {
                $this->error("Impossible de lire l’extrait de l’élève {$student->id}.");
                $skipped++;
                continue;
            }

            try {
                Storage::disk('private')->writeStream($path, $stream);
            } finally {
                if (is_resource($stream)) {
                    fclose($stream);
                }
            }

            if (!Storage::disk('public')->delete($path)) {
                Storage::disk('private')->delete($path);
                $this->error("Impossible de supprimer l’ancienne copie publique de l’élève {$student->id}.");
                $skipped++;
                continue;
            }

            $migrated++;
        }

        $this->info($this->option('apply')
            ? "{$migrated} extrait(s) déplacé(s), {$skipped} ignoré(s)."
            : "{$migrated} extrait(s) à déplacer, {$skipped} ignoré(s). Relancez avec --apply pour effectuer le déplacement.");

        return $skipped === 0 ? self::SUCCESS : self::FAILURE;
    }
}
