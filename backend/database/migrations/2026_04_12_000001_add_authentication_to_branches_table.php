<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        // Check if columns already exist
        if (!Schema::hasColumn('branches', 'username')) {
            // First, add columns without unique constraint
            Schema::table('branches', function (Blueprint $table) {
                $table->string('username', 100)->nullable()->after('branch_code');
                $table->string('password')->nullable()->after('username');
                $table->rememberToken()->after('password');
                $table->timestamp('last_login_at')->nullable()->after('remember_token');
            });
        }
        
        // Generate usernames for existing branches that don't have one
        $branches = DB::table('branches')->whereNull('username')->orWhere('username', '')->get();
        foreach ($branches as $branch) {
            $username = $this->generateUsername($branch);
            $password = Hash::make('branch123'); // Default password
            
            DB::table('branches')
                ->where('id', $branch->id)
                ->update([
                    'username' => $username,
                    'password' => $password,
                ]);
        }
        
        // Check if unique constraint exists
        $indexExists = DB::select("SHOW INDEX FROM branches WHERE Key_name = 'branches_username_unique'");
        
        if (empty($indexExists)) {
            // Now add unique constraint and index
            Schema::table('branches', function (Blueprint $table) {
                $table->string('username', 100)->nullable(false)->change();
                $table->unique('username');
            });
        }
        
        // Add index if it doesn't exist
        $regularIndexExists = DB::select("SHOW INDEX FROM branches WHERE Key_name = 'branches_username_index' AND Non_unique = 1");
        if (empty($regularIndexExists) && empty($indexExists)) {
            Schema::table('branches', function (Blueprint $table) {
                $table->index('username');
            });
        }
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropIndex(['username']);
            $table->dropColumn(['username', 'password', 'remember_token', 'last_login_at']);
        });
    }
    
    private function generateUsername($branch): string
    {
        // Try to use branch_code first
        if (!empty($branch->branch_code)) {
            $base = Str::slug($branch->branch_code, '_');
        } 
        // Otherwise use code
        elseif (!empty($branch->code)) {
            $base = Str::slug($branch->code, '_');
        }
        // Fallback to branch name
        else {
            $base = Str::slug($branch->name, '_');
        }
        
        // Ensure uniqueness
        $username = $base;
        $counter = 1;
        
        while (DB::table('branches')->where('username', $username)->where('id', '!=', $branch->id)->exists()) {
            $username = $base . '_' . $counter;
            $counter++;
        }
        
        return $username;
    }
};
