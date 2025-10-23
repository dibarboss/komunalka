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
        if (! Schema::hasColumn('meters', 'address_id')) {
            Schema::table('meters', function (Blueprint $table) {
                $table->foreignId('address_id')
                    ->nullable()
                    ->after('id')
                    ->constrained()
                    ->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('meters', 'user_id')) {
            DB::statement('DROP INDEX IF EXISTS meters_user_id_type_index');

            Schema::table('meters', function (Blueprint $table) {
                $table->dropConstrainedForeignId('user_id');
            });
        }

        $addresses = [];

        if (Schema::hasColumn('meters', 'user_id')) {
            DB::table('meters')->select('id', 'name', 'address_id', 'user_id')->chunkById(100, function ($meters) use (&$addresses): void {
                foreach ($meters as $meter) {
                    if (! $meter->user_id || $meter->address_id) {
                        continue;
                    }

                    if (! isset($addresses[$meter->user_id])) {
                        $addressId = DB::table('addresses')
                            ->where('owner_id', $meter->user_id)
                            ->orderBy('id')
                            ->value('id');

                        if (! $addressId) {
                            $addressId = DB::table('addresses')->insertGetId([
                                'owner_id' => $meter->user_id,
                                'name' => 'Основна адреса',
                                'address_line' => null,
                                'city' => null,
                                'state' => null,
                                'postal_code' => null,
                                'notes' => 'Автоматично створено під час оновлення схеми.',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);
                        }

                        DB::table('address_user')->updateOrInsert(
                            [
                                'address_id' => $addressId,
                                'user_id' => $meter->user_id,
                            ],
                            [
                                'role' => 'owner',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ],
                        );

                        $addresses[$meter->user_id] = $addressId;
                    }

                    DB::table('meters')->where('id', $meter->id)->update([
                        'address_id' => $addresses[$meter->user_id],
                    ]);
                }
            });
        }

        if (Schema::hasColumn('meters', 'user_id')) {
            Schema::table('meters', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('meters', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            if (Schema::hasColumn('meters', 'address_id')) {
                $table->dropConstrainedForeignId('address_id');
                $table->dropColumn('address_id');
            }
        });
    }
};
