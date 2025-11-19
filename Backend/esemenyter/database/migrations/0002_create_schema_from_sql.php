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
        Schema::create('user', function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('region', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('inner_region', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('region_id')->nullable();
            $table->string('title')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('settlement', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('inner_region_id')->nullable();
            $table->string('title')->nullable();
            $table->string('number')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('establishment', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title')->nullable();
            $table->integer('grades_id')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('settlement_id')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('class', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id');
            $table->string('name')->nullable();
            $table->integer('grade')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->unsignedInteger('establishment_id');
        });

        Schema::create('class_students', function (Blueprint $table) {
            $table->unsignedInteger('class_id');
            $table->unsignedInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->primary(['class_id', 'user_id']);
        });

        Schema::create('personnel', function (Blueprint $table) {
            $table->enum('role', ['admin', 'teacher'])->nullable();
            $table->unsignedInteger('establishment_id');
            $table->unsignedInteger('user_id');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('students', function (Blueprint $table) {
            $table->string('alias')->nullable();
            $table->unsignedInteger('establishment_id');
            $table->unsignedInteger('user_id');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('event', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('type', ['local', 'global'])->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->text('content')->nullable();
            $table->unsignedInteger('user_id');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->enum('status', ['open', 'closed'])->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('event_shown_to', function (Blueprint $table) {
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('class_id');
            $table->primary(['event_id', 'class_id', 'user_id']);
        });

        Schema::create('event_msg', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('user_id');
            $table->text('content')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('event_feedback', function (Blueprint $table) {
            $table->unsignedInteger('event_id');
            $table->enum('answer', ['y', 'n'])->nullable();
            $table->unsignedInteger('user_id');
            $table->timestamp('updated_at')->nullable();
            $table->primary(['event_id', 'user_id']);
        });

        Schema::create('event_favourite', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->unsignedInteger('user_id');
        });

        Schema::create('poll', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('event_id');
            $table->string('title')->nullable();
            $table->unsignedInteger('user_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('poll_option', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('poll_id');
            $table->string('title')->nullable();
        });

        Schema::create('poll_answer', function (Blueprint $table) {
            $table->unsignedInteger('poll_id')->nullable();
            $table->unsignedInteger('user_id')->nullable();
            $table->unsignedInteger('poll_option_id')->nullable();
            $table->primary(['poll_id', 'user_id']);
        });

        // Foreign keys
        Schema::table('inner_region', function (Blueprint $table) {
            $table->foreign('region_id')->references('id')->on('region');
        });

        Schema::table('settlement', function (Blueprint $table) {
            $table->foreign('inner_region_id')->references('id')->on('inner_region');
        });

        Schema::table('class_students', function (Blueprint $table) {
            $table->foreign('class_id')->references('id')->on('class');
            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::table('event_shown_to', function (Blueprint $table) {
            $table->foreign('class_id')->references('id')->on('class');
            $table->foreign('event_id')->references('id')->on('event');
            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::table('personnel', function (Blueprint $table) {
            $table->foreign('establishment_id')->references('id')->on('establishment');
            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::table('students', function (Blueprint $table) {
            $table->foreign('establishment_id')->references('id')->on('establishment');
            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::table('class', function (Blueprint $table) {
            $table->foreign('establishment_id')->references('id')->on('establishment');
            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::table('establishment', function (Blueprint $table) {
            $table->foreign('settlement_id')->references('id')->on('settlement');
        });

        Schema::table('event_favourite', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('event');
            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::table('event_msg', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('event');
            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::table('event_feedback', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('event');
            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::table('event', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::table('poll', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('event');
            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::table('poll_option', function (Blueprint $table) {
            $table->foreign('poll_id')->references('id')->on('poll');
        });

        Schema::table('poll_answer', function (Blueprint $table) {
            $table->foreign('poll_id')->references('id')->on('poll');
            $table->foreign('poll_option_id')->references('id')->on('poll_option');
            $table->foreign('user_id')->references('id')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('poll_answer');
        Schema::dropIfExists('poll_option');
        Schema::dropIfExists('poll');
        Schema::dropIfExists('event_favourite');
        Schema::dropIfExists('event_feedback');
        Schema::dropIfExists('event_msg');
        Schema::dropIfExists('event_shown_to');
        Schema::dropIfExists('event');
        Schema::dropIfExists('students');
        Schema::dropIfExists('personnel');
        Schema::dropIfExists('class_students');
        Schema::dropIfExists('class');
        Schema::dropIfExists('establishment');
        Schema::dropIfExists('settlement');
        Schema::dropIfExists('inner_region');
        Schema::dropIfExists('region');
        Schema::dropIfExists('user');
        Schema::enableForeignKeyConstraints();
    }
};
