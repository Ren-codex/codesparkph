<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->text('summary');
            $table->string('url');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // Seed the projects the landing page shipped with so the section keeps
        // rendering the same content after it switches to the database.
        $now = now();

        DB::table('projects')->insert([
            [
                'name' => 'Honda Cars Philippines',
                'type' => 'Business Website & Booking System',
                'summary' => 'A full dealership site with model browsing, online service booking, a financing calculator, and a dealer finder.',
                'url' => 'https://www.hondaphil.com/',
                'position' => 1,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Bouyant Trading',
                'type' => 'Business Website & Landing Page',
                'summary' => 'A landing page built to introduce the brand, present what the business offers, and turn visitors into inquiries.',
                'url' => 'https://bouyant-trading.com/landing',
                'position' => 2,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'Pestco Zamboanga',
                'type' => 'Business Digital Solution',
                'summary' => 'A pest control management system that keeps service operations and client records in one place.',
                'url' => 'https://pestcozam.com',
                'position' => 3,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name' => 'PinkPaw',
                'type' => 'Custom Web Application',
                'summary' => 'An online store with customer accounts, secure sign-in, and ordering built in.',
                'url' => 'https://pinkpaw.store/login',
                'position' => 4,
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
