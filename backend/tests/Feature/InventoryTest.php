<?php
namespace Tests\Feature;
use App\Models\{User, Product, Category, StockMovement};
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;
class InventoryTest extends TestCase
{
    use RefreshDatabase;
    private User $admin;
    private User $employee;
    private Category $category;
    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create(['name'=>'Admin Demo','email'=>'admin@demo.test','password'=>'Admin123!','role'=>'admin']);
        $this->employee = User::create(['name'=>'Employee Demo','email'=>'employee@demo.test','password'=>'Employee123!','role'=>'employee']);
        $this->category = Category::create(['name'=>'SSD & Stockage']);
    }
    private function product(array $extra=[]): Product { return Product::create(array_merge(['name'=>'SSD Test','sku'=>'TEST-001','category_id'=>$this->category->id,'min_stock'=>3,'unit_price'=>400],$extra)); }
    private function payload(): array { return ['name'=>'RAM Test','sku'=>'NEW-001','category_id'=>$this->category->id,'min_stock'=>2,'unit_price'=>200]; }
    private function move(Product $product,string $type,int|float $qty,array $extra=[]){return $this->postJson('/api/movements',array_merge(['product_id'=>$product->id,'type'=>$type,'quantity'=>$qty],$extra));}
    public function test_administrator_can_authenticate_and_session_is_preserved(): void
    {
        $this->postJson('/login',['email'=>'admin@demo.test','password'=>'Admin123!'])->assertOk()->assertJsonPath('data.role','admin');
        $this->getJson('/api/user')->assertOk()->assertJsonPath('data.email','admin@demo.test');
        $this->assertAuthenticatedAs($this->admin);
    }
    public function test_api_guest_without_json_accept_header_is_still_401(): void { $this->get('/api/user')->assertUnauthorized()->assertJsonPath('message','Unauthenticated.'); }
    public function test_partial_product_update_preserves_other_fields(): void { $p=$this->product();$this->actingAs($this->admin)->patchJson('/api/products/'.$p->id,['name'=>'Updated SSD'])->assertOk()->assertJsonPath('data.sku','TEST-001')->assertJsonPath('data.name','Updated SSD'); }
    public function test_invalid_login_is_rejected(): void { $this->postJson('/login',['email'=>'admin@demo.test','password'=>'wrong'])->assertUnprocessable()->assertJsonValidationErrors('email');$this->assertGuest(); }
    public function test_guest_is_rejected(): void {foreach(['/api/products','/api/dashboard','/api/categories','/api/movements','/api/user'] as $url)$this->getJson($url)->assertUnauthorized();}
    public function test_employee_cannot_change_catalog(): void
    {
        $p=$this->product();$this->actingAs($this->employee);
        $this->postJson('/api/products',$this->payload())->assertForbidden();
        $this->patchJson('/api/products/'.$p->id,$this->payload())->assertForbidden();
        $this->patchJson('/api/products/'.$p->id.'/status',['is_active'=>false])->assertForbidden();
        $this->postJson('/api/categories',['name'=>'Network'])->assertForbidden();
        $this->patchJson('/api/categories/'.$this->category->id,['name'=>'Changed'])->assertForbidden();
    }
    public function test_duplicate_sku_is_rejected(): void { $p=$this->product();$this->actingAs($this->admin)->postJson('/api/products',array_merge($this->payload(),['sku'=>$p->sku]))->assertUnprocessable()->assertJsonValidationErrors('sku'); }
    public function test_new_product_starts_at_zero(): void {$this->actingAs($this->admin)->postJson('/api/products',$this->payload())->assertCreated()->assertJsonPath('data.quantity',0);$this->assertDatabaseCount('stock_movements',0);}
    public function test_in_movement_increases_quantity_with_trusted_identity_and_snapshots(): void
    {
        $p=$this->product();$this->actingAs($this->admin);$this->move($p,'IN',10,['note'=>'Test entrée'])->assertCreated()->assertJsonPath('data.user_id',$this->admin->id)->assertJsonPath('data.quantity_before',0)->assertJsonPath('data.quantity_after',10);
        $this->assertSame(10,$p->fresh()->quantity);$this->assertDatabaseHas('stock_movements',['quantity'=>10,'note'=>'Test entrée']);
    }
    public function test_out_movement_decreases_quantity(): void
    {
        $p=$this->product();app(StockService::class)->record($p->id,$this->admin,'IN',10);$this->actingAs($this->employee);
        $this->move($p,'OUT',3)->assertCreated()->assertJsonPath('data.quantity_before',10)->assertJsonPath('data.quantity_after',7)->assertJsonPath('data.user_id',$this->employee->id);$this->assertSame(7,$p->fresh()->quantity);
    }
    public function test_insufficient_stock_does_not_modify_quantity_or_history(): void
    {
        $p=$this->product();app(StockService::class)->record($p->id,$this->admin,'IN',7);$this->actingAs($this->admin);$this->move($p,'OUT',20)->assertUnprocessable()->assertJsonValidationErrors('quantity')->assertJsonPath('errors.quantity.0','Stock insuffisant : 7 unité(s) disponible(s).');$this->assertSame(7,$p->fresh()->quantity);$this->assertDatabaseCount('stock_movements',1);
    }
    public static function invalidQuantities(): array { return [[0],[-1],[1.5]]; }
    #[DataProvider('invalidQuantities')]
    public function test_invalid_movement_quantities_are_rejected(int|float $quantity): void
    { $p=$this->product();$this->actingAs($this->admin);$this->move($p,'IN',$quantity)->assertUnprocessable()->assertJsonValidationErrors('quantity');$this->assertSame(0,$p->fresh()->quantity);$this->assertDatabaseCount('stock_movements',0); }
    public function test_inactive_product_rejects_movements(): void { $p=$this->product(['is_active'=>false]);$this->actingAs($this->admin);$this->move($p,'IN',1)->assertUnprocessable();$this->move($p,'OUT',1)->assertUnprocessable();$this->assertDatabaseCount('stock_movements',0); }
    public function test_client_cannot_supply_identity_or_snapshots(): void { $p=$this->product();$this->actingAs($this->admin);$this->move($p,'IN',2,['user_id'=>$this->employee->id,'quantity_before'=>5,'quantity_after'=>7])->assertUnprocessable()->assertJsonValidationErrors(['user_id','quantity_before','quantity_after']);$this->assertSame(0,$p->fresh()->quantity); }
    public function test_quantity_cannot_be_set_through_product_create_or_update(): void
    { $p=$this->product();$this->actingAs($this->admin);$this->patchJson('/api/products/'.$p->id,array_merge($this->payload(),['quantity'=>999]))->assertUnprocessable()->assertJsonValidationErrors('quantity');$this->postJson('/api/products',array_merge($this->payload(),['quantity'=>20]))->assertUnprocessable();$this->assertSame(0,$p->fresh()->quantity); }
    public function test_dashboard_statistics_match_sqlite(): void
    {
        $a=$this->product();$b=$this->product(['sku'=>'LOW','min_stock'=>4]);$c=$this->product(['sku'=>'OUT']);$service=app(StockService::class);$service->record($a->id,$this->admin,'IN',10);$service->record($b->id,$this->admin,'IN',2);
        $this->actingAs($this->admin)->getJson('/api/dashboard')->assertOk()->assertJsonPath('data.total_products',3)->assertJsonPath('data.total_units',12)->assertJsonPath('data.low_stock',1)->assertJsonPath('data.out_of_stock',1)->assertJsonPath('data.healthy_stock',1)->assertJsonPath('data.movement_count',2);
        $this->assertSame('sqlite',DB::connection()->getDriverName());
    }
    public function test_seed_has_exact_counts_is_idempotent_and_balances_history(): void
    {
        // Start without test fixtures for the exact demonstration dataset.
        DB::table('categories')->delete();DB::table('users')->delete();$this->seed();$this->seed();
        $this->assertDatabaseCount('products',50);$this->assertDatabaseCount('categories',8);$this->assertDatabaseCount('users',2);$this->assertDatabaseCount('stock_movements',75);
        $this->assertSame(50, Product::whereNotNull('image_url')->count());
        $this->assertSame([6,6,8,8,7,5,5,5],Category::withCount('products')->orderBy('id')->get()->pluck('products_count')->all());
        foreach(Product::all() as $p){$balance=0;foreach($p->movements()->orderBy('id')->get() as $m){$this->assertSame($balance,$m->quantity_before);$balance+=$m->type==='IN'?$m->quantity:-$m->quantity;$this->assertSame($balance,$m->quantity_after);}$this->assertSame($balance,$p->quantity);}
    }
    public function test_sqlite_rolls_back_quantity_if_movement_insert_fails(): void
    {
        $p=$this->product();$missing=new User(['name'=>'Missing']);$missing->id=999999;
        try {app(StockService::class)->record($p->id,$missing,'IN',10);$this->fail('Expected foreign key violation');}catch(\Illuminate\Database\QueryException $e){$this->assertSame(0,$p->fresh()->quantity);$this->assertDatabaseCount('stock_movements',0);}
    }
    public function test_movement_history_has_no_edit_or_delete_routes_and_fk_restricts_deletion(): void
    {
        $p=$this->product();$m=app(StockService::class)->record($p->id,$this->admin,'IN',5);$this->actingAs($this->admin);
        $this->patchJson('/api/movements/'.$m->id,['quantity'=>50])->assertNotFound();$this->deleteJson('/api/movements/'.$m->id)->assertNotFound();
        try{DB::table('products')->where('id',$p->id)->delete();$this->fail('Expected FK restriction');}catch(\Illuminate\Database\QueryException $e){$this->assertDatabaseHas('products',['id'=>$p->id]);}
        try{DB::table('users')->where('id',$this->admin->id)->delete();$this->fail('Expected FK restriction');}catch(\Illuminate\Database\QueryException $e){$this->assertDatabaseHas('users',['id'=>$this->admin->id]);}
    }
    public function test_history_is_immutable_at_database_level(): void
    {
        $p=$this->product();$m=app(StockService::class)->record($p->id,$this->admin,'IN',5);
        foreach(['update','delete'] as $action){try{$q=DB::table('stock_movements')->where('id',$m->id);$action==='delete'?$q->delete():$q->update(['quantity'=>6]);$this->fail('Expected immutable history');}catch(\Illuminate\Database\QueryException $e){$this->assertDatabaseHas('stock_movements',['id'=>$m->id,'quantity'=>5]);}}
    }
    public function test_search_filters_and_pagination(): void
    {
        $this->seed();$categoryId=Category::where('name','SSD & Stockage')->value('id');
        $response=$this->actingAs($this->admin)->getJson('/api/products?search=Samsung&category_id='.$categoryId.'&per_page=1');
        $response->assertOk()->assertJsonPath('per_page',1)->assertJsonPath('total',2)->assertJsonCount(1,'data')->assertJsonPath('data.0.category_id',$categoryId);
        $this->getJson('/api/products?search=IT-0014')->assertOk()->assertJsonPath('total',1)->assertJsonPath('data.0.sku','IT-0014');
        $response=$this->getJson('/api/products?stock_status=out');$response->assertOk()->assertJsonPath('total',5);foreach($response->json('data') as $p)$this->assertSame(0,$p['quantity']);
        $this->getJson('/api/movements?type=OUT&per_page=3')->assertOk()->assertJsonCount(3,'data');
    }
    public function test_negative_price_and_duplicate_category_are_rejected(): void
    {
        $this->actingAs($this->admin)->postJson('/api/products',array_merge($this->payload(),['unit_price'=>-1]))->assertUnprocessable();
        $this->postJson('/api/categories',['name'=>$this->category->name])->assertUnprocessable();
    }
    public function test_missing_resource_returns_404(): void {$this->actingAs($this->admin)->getJson('/api/products/9999')->assertNotFound();}
    public function test_deactivation_preserves_quantity_and_movements(): void
    {
        $p=$this->product();app(StockService::class)->record($p->id,$this->admin,'IN',10);$this->actingAs($this->admin)->patchJson('/api/products/'.$p->id.'/status',['is_active'=>false])->assertOk();$this->assertSame(10,$p->fresh()->quantity);$this->assertDatabaseCount('stock_movements',1);
    }
    public function test_logout_invalidates_session(): void {$this->actingAs($this->admin)->postJson('/logout')->assertOk();$this->assertGuest();}

    public function test_admin_can_create_and_rename_a_category_without_losing_products(): void
    {
        $this->actingAs($this->admin);
        $response = $this->postJson('/api/categories', ['name' => 'Accessoires']);
        $response->assertCreated()->assertJsonPath('data.products_count', 0);
        $categoryId = $response->json('data.id');

        $this->product(['category_id' => $categoryId]);
        $this->patchJson('/api/categories/'.$categoryId, ['name' => 'Accessoires IT'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Accessoires IT')
            ->assertJsonPath('data.products_count', 1);
    }

    public function test_stock_capacity_rejection_keeps_quantity_and_history_unchanged(): void
    {
        $product = $this->product();
        $stock = app(StockService::class);

        for ($index = 0; $index < 20; $index++) {
            $stock->record($product->id, $this->admin, 'IN', 100000000);
        }

        $this->actingAs($this->admin);
        $this->move($product, 'IN', 1)->assertUnprocessable()->assertJsonValidationErrors('quantity');
        $this->assertSame(2000000000, $product->fresh()->quantity);
        $this->assertDatabaseCount('stock_movements', 20);
    }

    public function test_sqlite_rejects_a_direct_negative_quantity_update(): void
    {
        $product = $this->product();

        try {
            DB::table('products')->where('id', $product->id)->update(['quantity' => -1]);
            $this->fail('Expected the SQLite stock trigger to reject negative stock.');
        } catch (\Illuminate\Database\QueryException $exception) {
            $this->assertSame(0, $product->fresh()->quantity);
            $this->assertDatabaseCount('stock_movements', 0);
        }
    }
}
