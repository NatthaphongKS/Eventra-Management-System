<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ems_position', function (Blueprint $table) {
            $table->id();
            $table->string('ept_name')->unique();
            $table->enum('ept_delete_status', ['active', 'inactive'])->default('active');
        });
        Schema::create('ems_department', function (Blueprint $table) {
            $table->id();
            $table->string('edm_name')->unique();
            $table->enum('edm_delete_status', ['active', 'inactive'])->default('active');
        });
        Schema::create('ems_team', function (Blueprint $table) {
            $table->id();
            $table->string('etm_name')->unique();
            $table->enum('etm_delete_status', ['active', 'inactive'])->default('active');
        });
        Schema::create('ems_employees', function (Blueprint $table) {
            $table->id();
            $table->string('emp_id')->unique();
            $table->enum('emp_prefix',['นาย', 'นาง', 'นางสาว'])->default('นาย');
            $table->string('emp_firstname');
            $table->string('emp_lastname');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->unique();
            $table->foreignId('emp_position_id')->constrained('ems_position')->onDelete('cascade');
            $table->foreignId('emp_department_id')->constrained('ems_department')->onDelete('cascade');
            $table->foreignId('emp_team_id')->constrained('ems_team')->onDelete('cascade');
            $table->string('emp_password');
            $table->enum('emp_status', ['enabled', 'disabled'])->default('disabled');
            $table->enum('emp_delete_status', ['active', 'inactive'])->default('active');
            $table->string('emp_delete_by')->nullable();
            $table->rememberToken()->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ems_employees');
        Schema::dropIfExists('ems_position');
        Schema::dropIfExists('ems_department');
        Schema::dropIfExists('ems_team');
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
