<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users');
            $table->foreignId('parent_id')->nullable()->constrained('departments');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['name', 'is_active']);
        });

        Schema::create('employee_department', function (Blueprint $table) {
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained()->cascadeOnDelete();
            $table->string('position');
            $table->boolean('is_primary')->default(false);
            $table->date('start_date')->default(DB::raw('(CURRENT_DATE)'));
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->primary(['employee_id', 'department_id', 'start_date']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_department');
        Schema::dropIfExists('departments');
    }
};
