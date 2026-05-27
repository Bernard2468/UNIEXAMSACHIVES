<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tag positions as belonging to one of the institution's leadership pools
     * (HOD / Dean / Director) so forms can route to the right pool dynamically
     * instead of to a single "dean-hod" office.
     */
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            if (!Schema::hasColumn('positions', 'category')) {
                $table->string('category', 20)->nullable()->index()->after('name');
            }
        });

        // Best-effort auto-tagging of existing positions based on their name.
        // This is idempotent — re-running the migration after it has been
        // rolled back will simply re-apply the tags.
        $this->autoTag('hod',      ['head of department', 'hod']);
        $this->autoTag('dean',     ['dean']);
        $this->autoTag('director', ['director']);
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            if (Schema::hasColumn('positions', 'category')) {
                $table->dropIndex(['category']);
                $table->dropColumn('category');
            }
        });
    }

    /**
     * Apply $category to every position whose lower-cased name contains
     * any of the given $needles AND does not already carry a category.
     */
    private function autoTag(string $category, array $needles): void
    {
        if (!Schema::hasColumn('positions', 'category')) {
            return;
        }

        try {
            $rows = DB::table('positions')
                ->whereNull('category')
                ->get(['id', 'name']);

            foreach ($rows as $row) {
                $lower = strtolower((string) $row->name);
                foreach ($needles as $needle) {
                    if (str_contains($lower, $needle)) {
                        DB::table('positions')->where('id', $row->id)->update(['category' => $category]);
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            // Don't block the migration if the data layer is unusual; the admin
            // can tag positions manually in the Positions page.
        }
    }
};
