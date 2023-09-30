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
        Schema::create('code_queues', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('queue_code', 100);
            $table->timestamps();
        });

        Schema::create('role_users', function (Blueprint $table) {
            $table->id();
            $table->string('nama_role');
            $table->unsignedBigInteger('code_id');
            $table->foreign('code_id')->references('id')->on('code_queues');
            $table->timestamps();
        });
        
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_users_id');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('role_users_id')->references('id')->on('role_users');
            $table->timestamps();
        });
        
        Schema::create('queues', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 100);
            $table->enum('status', ['waiting', 'process','complete'])->default('waiting');
            $table->unsignedBigInteger('assignments_id')->nullable();
            $table->foreign('assignments_id')->references('id')->on('assignments')->nullable();
            $table->timestamps();
        });

       


        
    }

  
    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
