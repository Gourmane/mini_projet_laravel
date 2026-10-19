<?php

namespace Tests\Feature;

use App\Models\{Category, Product, User};
use App\Services\StockService;
use Database\Seeders\ProductImageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProductImagesTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create(['name' => 'Admin', 'email' => 'images@demo.test', 'password' => 'test-password', 'role' => 'admin']);
    }

    private function product(): Product
    {
        return Product::create([
            'name' => 'Test product', 'sku' => 'IT-0001', 'min_stock' => 3,
            'category_id' => Category::create(['name' => 'Test category'])->id,
        ]);
    }

    public function test_admin_can_create_update_read_and_clear_image_url_without_changing_stock(): void
    {
        $admin = $this->admin();
        $category = Category::create(['name' => 'Test category']);
        $url = 'https://images.example.org/product.jpg';
        $this->actingAs($admin);
        $response = $this->postJson('/api/products', [
            'name' => 'Test product', 'sku' => 'IMAGE-001', 'category_id' => $category->id,
            'min_stock' => 2, 'image_url' => $url,
        ])->assertCreated()->assertJsonPath('data.image_url', $url);
        $id = $response->json('data.id');
        app(StockService::class)->record($id, $admin, 'IN', 7);
        $history = DB::table('stock_movements')->get()->toArray();
        $newUrl = 'http://images.example.org/changed.png?width=400';
        $this->patchJson('/api/products/'.$id, ['image_url' => $newUrl])
            ->assertOk()->assertJsonPath('data.image_url', $newUrl)->assertJsonPath('data.quantity', 7);
        $this->getJson('/api/products/'.$id)->assertOk()->assertJsonPath('data.image_url', $newUrl);
        $this->getJson('/api/products?search=IMAGE-001')->assertOk()->assertJsonPath('data.0.image_url', $newUrl);
        $this->patchJson('/api/products/'.$id, ['image_url' => ''])->assertOk()->assertJsonPath('data.image_url', null);
        $this->assertEquals($history, DB::table('stock_movements')->get()->toArray());
        $this->assertDatabaseHas('products', ['id' => $id, 'quantity' => 7, 'image_url' => null]);
    }

    public static function invalidUrls(): array
    {
        return [['javascript:alert(1)'], ['data:image/png;base64,abc'], ['ftp://images.example.org/a.png'],
            ['file:///tmp/a.png'], ['/a.png'], ['not-a-url'], ['https://'], [str_repeat('a', 2049)]];
    }

    #[DataProvider('invalidUrls')]
    public function test_only_http_https_urls_are_accepted(string $url): void
    {
        $product = $this->product();
        $this->actingAs($this->admin())->patchJson('/api/products/'.$product->id, ['image_url' => $url])
            ->assertUnprocessable()->assertJsonValidationErrors('image_url');
        $this->assertNull($product->fresh()->image_url);
    }

    public function test_employee_can_view_images_but_cannot_change_them(): void
    {
        $product = $this->product();
        $url = 'https://images.example.org/original.jpg';
        $product->update(['image_url' => $url]);
        $employee = User::create(['name' => 'Employee', 'email' => 'viewer@demo.test', 'password' => 'test-password', 'role' => 'employee']);
        $this->actingAs($employee)->getJson('/api/products/'.$product->id)->assertOk()->assertJsonPath('data.image_url', $url);
        $this->getJson('/api/products')->assertOk()->assertJsonPath('data.0.image_url', $url);
        $this->patchJson('/api/products/'.$product->id, ['image_url' => 'https://images.example.org/changed.jpg'])->assertForbidden();
        $this->postJson('/api/products', ['image_url' => $url])->assertForbidden();
        $this->assertSame($url, $product->fresh()->image_url);
    }

    public function test_image_seed_is_idempotent_preserves_inventory_and_respects_admin_images(): void
    {
        $product = $this->product();
        app(StockService::class)->record($product->id, $this->admin(), 'IN', 8);
        $before = $product->getAttributes();
        $history = DB::table('stock_movements')->get()->toArray();
        $this->seed(ProductImageSeeder::class);
        $url = $product->fresh()->image_url;
        $this->assertNotEmpty($url);
        $this->seed(ProductImageSeeder::class);
        $this->assertSame($url, $product->fresh()->image_url);
        foreach ($before as $key => $value) {
            if ($key !== 'image_url') $this->assertSame($value, $product->fresh()->getAttributes()[$key]);
        }
        $this->assertEquals($history, DB::table('stock_movements')->get()->toArray());
        $this->assertDatabaseCount('products', 1);
        $product->update(['image_url' => 'https://images.example.org/admin.jpg']);
        $this->seed(ProductImageSeeder::class);
        $this->assertSame('https://images.example.org/admin.jpg', $product->fresh()->image_url);
    }
}
