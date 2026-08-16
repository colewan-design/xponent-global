<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ordered after `create_job_openings_table` on purpose.
 *
 * Both were generated in the same second, so Laravel falls back to sorting them
 * by filename — and `job_applications` sorts before `job_openings`, which put
 * this foreign key ahead of the table it references. SQLite accepts a forward
 * reference like that, so local development never noticed; MySQL rejects it at
 * CREATE time with errno 150. The timestamp is bumped rather than the key being
 * added in a later ALTER, so the table is still created complete in one step.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_opening_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('cover_letter')->nullable();
            $table->string('resume');
            $table->string('status')->default('new'); // new, reviewed, rejected, hired
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_applications');
    }
};
