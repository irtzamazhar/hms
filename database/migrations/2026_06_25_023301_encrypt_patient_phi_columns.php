<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** PHI columns now encrypted at rest (see Patient::ENCRYPTED_PHI). */
    private array $columns = [
        'allergies', 'medical_history',
        'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation',
    ];

    public function up(): void
    {
        // Encrypted ciphertext is far longer than the plaintext, so widen the
        // short VARCHAR contact columns to TEXT before encrypting.
        Schema::table('patients', function (Blueprint $table) {
            $table->text('emergency_contact_name')->nullable()->change();
            $table->text('emergency_contact_phone')->nullable()->change();
            $table->text('emergency_contact_relation')->nullable()->change();
        });

        // Encrypt existing plaintext values. Idempotent: a value that already
        // decrypts is skipped, so the migration is safe to re-run.
        DB::table('patients')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $update = [];
                foreach ($this->columns as $col) {
                    $value = $row->{$col} ?? null;
                    if ($value === null || $value === '') {
                        continue;
                    }
                    if ($this->isEncrypted($value)) {
                        continue; // already encrypted
                    }
                    $update[$col] = Crypt::encryptString((string) $value);
                }
                if ($update) {
                    DB::table('patients')->where('id', $row->id)->update($update);
                }
            }
        });
    }

    public function down(): void
    {
        // Decrypt back to plaintext so the column change is reversible.
        DB::table('patients')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $update = [];
                foreach ($this->columns as $col) {
                    $value = $row->{$col} ?? null;
                    if ($value === null || $value === '' || ! $this->isEncrypted($value)) {
                        continue;
                    }
                    $update[$col] = Crypt::decryptString($value);
                }
                if ($update) {
                    DB::table('patients')->where('id', $row->id)->update($update);
                }
            }
        });

        Schema::table('patients', function (Blueprint $table) {
            $table->string('emergency_contact_name')->nullable()->change();
            $table->string('emergency_contact_phone', 20)->nullable()->change();
            $table->string('emergency_contact_relation')->nullable()->change();
        });
    }

    private function isEncrypted(string $value): bool
    {
        try {
            Crypt::decryptString($value);

            return true;
        } catch (Throwable) {
            return false;
        }
    }
};
