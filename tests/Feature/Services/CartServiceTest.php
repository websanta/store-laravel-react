<?php

namespace Tests\Unit\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Product $product1;
    private Product $product2;
    private CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->product1 = Product::factory()->create([
            'price' => 100.00,
        ]);

        $this->product2 = Product::factory()->create([
            'price' => 50.00,
        ]);

        $this->cartService = new CartService();
    }

    // Guest tests
    public function test_guest_can_add_item_to_cookie_cart()
    {
        Auth::shouldReceive('check')->andReturn(false);
        Auth::shouldReceive('id')->andReturnNull();

        Cookie::shouldReceive('get')
            ->with('cartItems', '[]')
            ->andReturn(json_encode([]));

        Cookie::shouldReceive('queue')
            ->with('cartItems', \Mockery::on(function ($json) {
                $items = json_decode($json, true);
                return count($items) === 1 && array_values($items)[0]['quantity'] === 2;
            }), \Mockery::any())
            ->once();

        $this->cartService->addItemToCart($this->product1, 2);

        $this->assertEquals(0, CartItem::count());
    }

    public function test_guest_can_get_total_price_from_cookies()
    {
        $_COOKIE['cartItems'] = json_encode([
            '1_[]' => [
                'id' => Str::uuid(),
                'product_id' => $this->product1->id,
                'quantity' => 2,
                'price' => 100.00,
                'option_ids' => []
            ],
        ]);

        Auth::shouldReceive('check')->andReturn(false);
        Auth::shouldReceive('id')->andReturnNull();

        $total = $this->cartService->getTotalPrice();

        $this->assertEquals(200.0, $total);
    }

    // Authenticated user tests
    public function test_authenticated_user_can_add_item_to_database_cart()
    {
        Auth::login($this->user);

        $this->cartService->addItemToCart($this->product1, 2, []);

        $this->assertEquals(1, CartItem::count());

        $cartItem = CartItem::first();
        $this->assertEquals($this->user->id, $cartItem->user_id);
        $this->assertEquals($this->product1->id, $cartItem->product_id);
        $this->assertEquals(2, $cartItem->quantity);
    }

    public function test_authenticated_user_can_update_item_quantity()
    {
        Auth::login($this->user);

        $this->cartService->addItemToCart($this->product1, 2, []);
        $this->cartService->updateItemQuantity($this->product1->id, 5, []);

        $cartItem = CartItem::first();
        $this->assertEquals(5, $cartItem->quantity);
    }

    public function test_authenticated_user_can_remove_item()
    {
        Auth::login($this->user);

        $this->cartService->addItemToCart($this->product1, 2, []);
        $this->assertEquals(1, CartItem::count());

        $this->cartService->removeItemFromCart($this->product1->id, []);
        $this->assertEquals(0, CartItem::count());
    }

    public function test_authenticated_user_can_get_total_price()
    {
        Auth::login($this->user);

        $this->cartService->addItemToCart($this->product1, 2, []);
        $this->cartService->addItemToCart($this->product2, 3, []);

        $this->assertEquals(350.0, $this->cartService->getTotalPrice());
    }

    public function test_authenticated_user_can_get_total_quantity()
    {
        Auth::login($this->user);

        $this->cartService->addItemToCart($this->product1, 2, []);
        $this->cartService->addItemToCart($this->product2, 3, []);

        $this->assertEquals(5, $this->cartService->getTotalQuantity());
    }

    public function test_authenticated_user_can_clear_cart()
    {
        Auth::login($this->user);

        $this->cartService->addItemToCart($this->product1, 2, []);
        $this->cartService->addItemToCart($this->product2, 3, []);

        $this->assertEquals(2, CartItem::count());

        $this->cartService->clearCart();

        $this->assertEquals(0, CartItem::count());
        $this->assertEquals(0, $this->cartService->getTotalPrice());
    }

    public function test_quantity_increases_when_adding_same_product_twice()
    {
        Auth::login($this->user);

        $this->cartService->addItemToCart($this->product1, 2, []);
        $this->cartService->addItemToCart($this->product1, 3, []);

        $cartItem = CartItem::first();
        $this->assertEquals(5, $cartItem->quantity);
    }

    public function test_cookie_items_migrate_to_database_on_login()
    {
        $cookieCartItems = [
            '1_[]' => [
                'id' => Str::uuid(),
                'product_id' => $this->product1->id,
                'quantity' => 2,
                'price' => 100.00,
                'option_ids' => []
            ],
        ];

        $_COOKIE['cartItems'] = json_encode($cookieCartItems);

        Cookie::shouldReceive('queue')
            ->with('cartItems', '', -1)
            ->once();

        $this->cartService->moveCartItemsToDatabase($this->user->id);

        $this->assertEquals(1, CartItem::count());
        $cartItem = CartItem::first();
        $this->assertEquals($this->user->id, $cartItem->user_id);
        $this->assertEquals($this->product1->id, $cartItem->product_id);
        $this->assertEquals(2, $cartItem->quantity);
    }

    public function test_cookie_items_merge_with_existing_database_items()
    {
        Auth::login($this->user);

        CartItem::factory()->create([
            'user_id' => $this->user->id,
            'product_id' => $this->product1->id,
            'quantity' => 1,
            'price' => 100.00,
        ]);

        $cookieCartItems = [
            '1_[]' => [
                'id' => Str::uuid(),
                'product_id' => $this->product1->id,
                'quantity' => 2,
                'price' => 100.00,
                'option_ids' => []
            ],
        ];

        $_COOKIE['cartItems'] = json_encode($cookieCartItems);

        Cookie::shouldReceive('queue')
            ->with('cartItems', '', -1)
            ->once();

        $this->cartService->moveCartItemsToDatabase($this->user->id);

        $this->assertEquals(1, CartItem::count());
        $cartItem = CartItem::first();
        $this->assertEquals(3, $cartItem->quantity);
    }

    public function test_multiple_different_products_migrate_correctly()
    {
        $cookieCartItems = [
            '1_[]' => [
                'id' => Str::uuid(),
                'product_id' => $this->product1->id,
                'quantity' => 2,
                'price' => 100.00,
                'option_ids' => []
            ],
            '2_[]' => [
                'id' => Str::uuid(),
                'product_id' => $this->product2->id,
                'quantity' => 3,
                'price' => 50.00,
                'option_ids' => []
            ],
        ];

        $_COOKIE['cartItems'] = json_encode($cookieCartItems);

        Cookie::shouldReceive('queue')
            ->with('cartItems', '', -1)
            ->once();

        $this->cartService->moveCartItemsToDatabase($this->user->id);

        $this->assertEquals(2, CartItem::count());

        $cartItem1 = CartItem::where('product_id', $this->product1->id)->first();
        $cartItem2 = CartItem::where('product_id', $this->product2->id)->first();

        $this->assertEquals(2, $cartItem1->quantity);
        $this->assertEquals(3, $cartItem2->quantity);
    }

    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
