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
        //
        Schema::create('workspaces', function (Blueprint $table) {
            // $table->uuid('id')->default(Str::uuid()); // UUID column with default value
            // $table->string('workspace_name'); // Workspace name
            // $table->json('status'); // Status as a JSON object
            // $table->timestamps(); // Created and updated timestamps
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('workspaces');
    }
};
