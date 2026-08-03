<?php

use App\Modules\Employee\Models\Employee;
use App\Modules\PassportHandover\Models\PassportHandover;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('passport_handover_items', function (Blueprint $table) {
            $table->foreignId('collected_by_user_id')
                ->nullable()
                ->after('collected_by')
                ->constrained('users')
                ->nullOnDelete();
        });

        PassportHandover::query()
            ->whereNotNull('created_by')
            ->with('items')
            ->chunkById(100, function ($handovers) {
                foreach ($handovers as $handover) {
                    $employeeId = Employee::where('user_id', $handover->created_by)->value('id');

                    if (!$employeeId) {
                        continue;
                    }

                    if (!$handover->handed_over_by) {
                        $handover->updateQuietly(['handed_over_by' => $employeeId]);
                    }

                    $handover->items()
                        ->whereNull('handed_over_by')
                        ->update(['handed_over_by' => $employeeId]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('passport_handover_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collected_by_user_id');
        });
    }
};
