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
        Schema::create('ems_categories', function (Blueprint $table) {
            $table->id();
            $table->string('cat_name'); // ⬅️ เอา ->unique() ออก
            $table->enum('cat_delete_status', ['active', 'inactive'])->default('active');

            // ⬇️ ใส่ unique แบบคอมโพสิท
            $table->unique(['cat_name', 'cat_delete_status'], 'ems_categories_name_status_unique');
        });

        Schema::create('ems_event', function (Blueprint $table) {
            $table->id();
            $table->string('evn_title');
            $table->foreignId('evn_category_id')->constrained('ems_categories')->onDelete('cascade');
            $table->text('evn_description')->nullable();
            $table->date('evn_date');
            $table->time('evn_timestart');
            $table->time('evn_timeend');
            $table->integer('evn_duration'); // Duration in hour
            $table->string('evn_location');
            $table->enum('evn_file', ['have', 'not_have'])->default('not_have');
            $table->foreignId('evn_create_by')->constrained('ems_employees')->onDelete('cascade');
            $table->timestamp('evn_created_at')->useCurrent();
            $table->timestamp('evn_deleted_at')->nullable();
            $table->foreignId('evn_deleted_by')->nullable()->constrained('ems_employees')->nullOnDelete();
            $table->enum('evn_status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');
        });

        Schema::create('ems_connect', function (Blueprint $table) {
            $table->id();
            $table->foreignId('con_event_id')->constrained('ems_event')->onDelete('cascade');
            $table->foreignId('con_employee_id')->constrained('ems_employees')->onDelete('cascade');
            $table->enum('con_answer', ['accept', 'denied', 'invalid'])->default('invalid');
            $table->text('con_reason')->nullable();
            $table->enum('con_delete_status', ['active', 'inactive'])->default('active');
        });
        Schema::create('ems_event_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_event_id')
                  ->constrained('ems_event')
                  ->onDelete('cascade'); // ลบ event แล้วไฟล์หาย
            $table->string('file_name');          // ชื่อไฟล์จริงที่ผู้ใช้อัปโหลด
            $table->string('file_path');          // path ใน storage/public
            $table->string('file_type')->nullable(); // MIME type เช่น application/pdf
            $table->unsignedBigInteger('file_size'); // ขนาดไฟล์ (byte)
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ems_connect');
        Schema::dropIfExists('ems_event');
        Schema::dropIfExists('ems_categories');
    }
};
