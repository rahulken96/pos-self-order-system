<?php

use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\Table;
use App\Models\User;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ─── Helpers ────────────────────────────────────────────────────────────────

function makeTable(string $status = 'available'): Table
{
    return Table::create(['number' => rand(100, 999), 'status' => $status]);
}

function makeCategory(): Category
{
    return Category::create(['name' => 'Test Cat ' . uniqid()]);
}

function makeMenuItem(Category $cat, float $price = 15000): MenuItem
{
    return MenuItem::create([
        'category_id'  => $cat->id,
        'name'         => 'Menu ' . uniqid(),
        'price'        => $price,
        'is_available' => true,
    ]);
}

function makeDraftOrder(Table $table): Order
{
    return Order::create([
        'table_id'       => $table->id,
        'customer_name'  => 'Tester',
        'customer_phone' => '08111111111',
        'status'         => 'draft',
        'total_price'    => 0,
    ]);
}

// ─── Phase 4 — Customer Flow ─────────────────────────────────────────────────

test('customer sees identity form when table has no active order', function () {
    $table = makeTable();

    $this->get(route('customer.order', $table->id))
         ->assertInertia(fn ($p) => $p->component('Customer/Start'));
});

test('customer sees menu when table has active draft order', function () {
    $table = makeTable('occupied');
    makeDraftOrder($table);

    $this->get(route('customer.order', $table->id))
         ->assertInertia(fn ($p) => $p->component('Customer/Menu'));
});

test('customer can start order and table becomes occupied', function () {
    $table = makeTable();

    $this->post(route('customer.start', $table->id), [
        'customer_name'  => 'Budi',
        'customer_phone' => '08123456789',
    ])->assertRedirect(route('customer.order', $table->id));

    expect(Order::where('table_id', $table->id)->first()->status)->toBe('draft');
    expect($table->fresh()->status)->toBe('occupied');
});

test('customer can add item to order', function () {
    $table  = makeTable('occupied');
    $order  = makeDraftOrder($table);
    $cat    = makeCategory();
    $item   = makeMenuItem($cat, 20000);

    $this->postJson(route('customer.addItem', $order->id), [
        'menu_item_id' => $item->id,
        'quantity'     => 2,
    ])->assertOk();

    expect((float) $order->fresh()->total_price)->toBe(40000.0);
});

test('customer can submit order (draft -> pending)', function () {
    $table = makeTable('occupied');
    $order = makeDraftOrder($table);

    $this->postJson(route('customer.submitOrder', $order->id))
         ->assertOk()
         ->assertJsonPath('message', 'Pesanan dikirim ke dapur!');

    expect($order->fresh()->status)->toBe('pending');
});

test('customer can request bill', function () {
    $table = makeTable('occupied');
    $order = makeDraftOrder($table);
    $order->update(['status' => 'ready']);

    $this->patchJson(route('customer.requestBill', $order->id))
         ->assertOk()
         ->assertJsonPath('message', 'Permintaan bill terkirim ke kasir!');

    expect($order->fresh()->bill_requested)->toBeTrue();
});

// ─── Phase 3 — Role Middleware ───────────────────────────────────────────────

test('guest is redirected to login when accessing kasir route', function () {
    $this->get('/kasir')->assertRedirect(route('login'));
});

test('dapur user cannot access admin routes (403)', function () {
    $user = User::factory()->create(['role' => 'dapur']);

    $this->actingAs($user)
         ->get('/admin/menu')
         ->assertStatus(403);
});

test('kasir user can access kasir dashboard', function () {
    $user = User::factory()->create(['role' => 'kasir']);

    $this->actingAs($user)
         ->get('/kasir')
         ->assertInertia(fn ($p) => $p->component('Kasir/Index'));
});

test('dapur user can access dapur dashboard', function () {
    $user = User::factory()->create(['role' => 'dapur']);

    $this->actingAs($user)
         ->get('/dapur')
         ->assertInertia(fn ($p) => $p->component('Dapur/Index'));
});

// ─── Phase 6 — Kasir Payment ─────────────────────────────────────────────────

test('kasir can process manual cash payment', function () {
    $kasir = User::factory()->create(['role' => 'kasir']);
    $table = makeTable('occupied');
    $order = makeDraftOrder($table);
    $order->update(['status' => 'ready', 'total_price' => 50000]);

    $this->actingAs($kasir)
         ->postJson(route('kasir.payment', $order->id), ['amount' => 60000])
         ->assertOk()
         ->assertJsonPath('change', 10000);

    expect($order->fresh()->status)->toBe('completed');
    expect($table->fresh()->status)->toBe('available');
});

// ─── Phase 7 & 8 — Admin CRUD & Reports ──────────────────────────────────────

test('admin can manage menu items', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $cat = makeCategory();

    // Create
    $this->actingAs($admin)
         ->post(route('menu.store'), [
             'category_id'  => $cat->id,
             'name'         => 'Nasi Tempong',
             'price'        => 25000,
             'description'  => 'Pedas mantap',
             'is_available' => true,
         ])->assertRedirect(route('menu.index'));

    $menu = MenuItem::where('name', 'Nasi Tempong')->first();
    expect($menu)->not->toBeNull();

    // Update
    $this->actingAs($admin)
         ->put(route('menu.update', $menu->id), [
             'category_id'  => $cat->id,
             'name'         => 'Nasi Tempong Juara',
             'price'        => 27000,
             'description'  => 'Sangat pedas mantap',
             'is_available' => false,
         ])->assertRedirect(route('menu.index'));

    expect($menu->fresh()->name)->toBe('Nasi Tempong Juara');
    expect((float) $menu->fresh()->price)->toBe(27000.0);

    // Delete
    $this->actingAs($admin)
         ->delete(route('menu.destroy', $menu->id))
         ->assertRedirect(route('menu.index'));

    expect(MenuItem::find($menu->id))->toBeNull();
});

test('admin can manage tables', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Create
    $this->actingAs($admin)
         ->post(route('tables.store'), [
             'number' => 99
         ])->assertRedirect(route('tables.index'));

    $table = Table::where('number', 99)->first();
    expect($table)->not->toBeNull();
    expect($table->qr_code)->not->toBeNull();

    // Update
    $this->actingAs($admin)
         ->put(route('tables.update', $table->id), [
             'number' => 98,
             'status' => 'occupied'
         ])->assertRedirect(route('tables.index'));

    expect($table->fresh()->number)->toBe(98);
    expect($table->fresh()->status)->toBe('occupied');

    // Delete
    $this->actingAs($admin)
         ->delete(route('tables.destroy', $table->id))
         ->assertRedirect(route('tables.index'));

    expect(Table::find($table->id))->toBeNull();
});

test('admin can manage users', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // Create
    $this->actingAs($admin)
         ->post(route('users.store'), [
             'name'     => 'Kasir Baru',
             'email'    => 'kasirbaru@pos.com',
             'role'     => 'kasir',
             'password' => 'password123',
         ])->assertRedirect(route('users.index'));

    $kasir = User::where('email', 'kasirbaru@pos.com')->first();
    expect($kasir)->not->toBeNull();

    // Update
    $this->actingAs($admin)
         ->put(route('users.update', $kasir->id), [
             'name'  => 'Kasir Baru Editan',
             'email' => 'kasirbaru@pos.com',
             'role'  => 'dapur',
         ])->assertRedirect(route('users.index'));

    expect($kasir->fresh()->name)->toBe('Kasir Baru Editan');
    expect($kasir->fresh()->role)->toBe('dapur');

    // Delete
    $this->actingAs($admin)
         ->delete(route('users.destroy', $kasir->id))
         ->assertRedirect(route('users.index'));

    expect(User::find($kasir->id))->toBeNull();
});

test('admin can view reports and export', function () {
    $admin = User::factory()->create(['role' => 'admin']);

    // View dashboard reports
    $this->actingAs($admin)
         ->get(route('admin.reports'))
         ->assertOk();

    // Export Excel
    $this->actingAs($admin)
         ->get(route('admin.reports.excel'))
         ->assertOk();

    // Export PDF
    $this->actingAs($admin)
         ->get(route('admin.reports.pdf'))
         ->assertOk();
});

