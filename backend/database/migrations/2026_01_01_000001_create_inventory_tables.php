<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'employee']);
            $table->rememberToken();
            $table->timestamps();
        });
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('sku')->unique();
            $table->string('name')->index();
            $table->string('brand')->nullable();
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('min_stock')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['IN', 'OUT']);
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('quantity_before');
            $table->unsignedInteger('quantity_after');
            $table->string('note', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['created_at', 'id']);
            $table->index(['product_id', 'created_at']);
        });
        // SQLite unsigned columns do not enforce nonnegative integers. These guards do.
        DB::unprepared("CREATE TRIGGER products_stock_insert BEFORE INSERT ON products WHEN NEW.quantity < 0 OR NEW.min_stock < 0 OR NEW.unit_price < 0 BEGIN SELECT RAISE(ABORT, 'Invalid stock values'); END");
        DB::unprepared("CREATE TRIGGER products_stock_update BEFORE UPDATE ON products WHEN NEW.quantity < 0 OR NEW.min_stock < 0 OR NEW.unit_price < 0 BEGIN SELECT RAISE(ABORT, 'Invalid stock values'); END");
        DB::unprepared("CREATE TRIGGER movements_validate BEFORE INSERT ON stock_movements WHEN NEW.quantity <= 0 OR NEW.quantity_before < 0 OR NEW.quantity_after < 0 OR NEW.quantity_after != NEW.quantity_before + CASE WHEN NEW.type = 'IN' THEN NEW.quantity ELSE -NEW.quantity END BEGIN SELECT RAISE(ABORT, 'Invalid movement'); END");
        DB::unprepared("CREATE TRIGGER movements_no_update BEFORE UPDATE ON stock_movements BEGIN SELECT RAISE(ABORT, 'Immutable history'); END");
        DB::unprepared("CREATE TRIGGER movements_no_delete BEFORE DELETE ON stock_movements BEGIN SELECT RAISE(ABORT, 'Immutable history'); END");
    }
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
    }
};

