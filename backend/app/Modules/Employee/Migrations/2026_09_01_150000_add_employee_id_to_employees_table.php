<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_id', 50)->nullable()->unique()->after('user_id');
        });

        $employees = DB::table('employees')->orderBy('id')->get(['id']);

        foreach ($employees as $index => $employee) {
            DB::table('employees')->where('id', $employee->id)->update([
                'employee_id' => 'ST'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropUnique(['employee_id']);
            $table->dropColumn('employee_id');
        });
    }
};
