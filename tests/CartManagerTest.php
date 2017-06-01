<?php

use AbstractEverything\Cart\CartManager;
use Illuminate\Config\Repository as Config;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;

class CartManagerTest extends Orchestra\Testbench\TestCase
{
    public function test_it_can_add_a_cart_item()
    {
        $this->setUpEmptyCart();
        $config = $this->getConfig();

        $cartManager = new CartManager(resolve(SessionManager::class), $config);
        $cartManager->add('item1', 'Test item 1', 123, 1);

        $this->assertEquals('Test item 1', $cartManager->getCartSession()['item1']['name']);
    }

    public function test_it_can_add_many_cart_items()
    {
        $this->setUpEmptyCart();
        $config = $this->getConfig();

        $cartManager = new CartManager(resolve(SessionManager::class), $config);
        $cartManager->addMany([
            [
                'id' => 'item1',
                'name' => 'Test item 1',
                'price' => 123,
                'quantity' => 1,
                'options' => [],
            ],
            [
                'id' => 'item2',
                'name' => 'Test item 2',
                'price' => 234,
                'quantity' => 2,
                'options' => [],
            ],
        ]);

        $this->assertEquals(2, count(session()->get($config->get('cart.session_name'))));
    }

    public function test_it_increments_quantity_of_existing_item()
    {
        $this->setUpDummyCart();
        $config = $this->getConfig();

        $cartManager = new CartManager(resolve(SessionManager::class), $config);
        $cartManager->add('item3', 'Test item 3', 400, 3);

        $this->assertEquals(6, $cartManager->getCartSession()['item3']['quantity']);
    }

    public function test_it_can_remove_a_cart_item()
    {
        $this->setUpDummyCart();
        $config = $this->getConfig();

        $cartManager = new CartManager(resolve(SessionManager::class), $config);

        $this->assertArrayHasKey('item2', $cartManager->getCartSession());
        $cartManager->remove('item2');
        $this->assertArrayNotHasKey('item2', $cartManager->getCartSession());
    }

    public function test_it_can_find_item()
    {
        $this->setUpDummyCart();
        $config = $this->getConfig();

        $cartManager = new CartManager(resolve(SessionManager::class), $config);
        $item = $cartManager->find('item3');

        $this->assertEquals(
            'test item three',
            $item['name']
        );
    }

    public function test_it_returns_null_if_not_found()
    {
        $this->setUpDummyCart();
        $config = $this->getConfig();

        $cartManager = new CartManager(resolve(SessionManager::class), $config);
        $this->assertEquals(null, $cartManager->find(7));
    }

    public function test_it_calculates_subtotal()
    {
        $this->setUpDummyCart();
        $config = $this->getConfig();

        $cartManager = new CartManager(resolve(SessionManager::class), $config);
        
        $this->assertEquals(1250, $cartManager->subtotal());
    }

    public function test_it_calculates_tax()
    {
        $this->setUpDummyCart();
        $config = $this->getConfig();

        $cartManager = new CartManager(resolve(SessionManager::class), $config);

        $this->assertEquals(250, $cartManager->tax());
    }

    public function test_it_calculates_subtotal_with_tax()
    {
        $this->setUpDummyCart();
        $config = $this->getConfig();

        $cartManager = new CartManager(resolve(SessionManager::class), $config);
        
        $this->assertEquals(1500, $cartManager->subtotalWithTax());
    }

    public function test_it_counts_items()
    {
        $this->setUpDummyCart();
        $config = $this->getConfig();

        $cartManager = new CartManager(resolve(SessionManager::class), $config);

        $this->assertEquals(6, $cartManager->count());
    }

    public function test_if_no_items_count_returns_zero()
    {
        $this->setUpEmptyCart();
        $config = $this->getConfig();

        $cartManager = new CartManager(resolve(SessionManager::class), $config);

        $this->assertSame(0, $cartManager->count());
    }

    // ----------------------------------------------------------------------

    protected function getConfig()
    {
        $config = resolve(Config::class);
        $config->set('cart.session_name', '_cart');
        $config->set('cart.tax_rate', 20);

        return $config;
    }

    protected function setUpEmptyCart()
    {
        session(['_cart' => []]);
    }

    protected function setUpDummyCart()
    {
        session()->put('_cart', [
            'item1' => [
                'name' => 'test item one',
                'price' => 100,
                'quantity' => 1,
                'options' => [],
            ],
            'item2' => [
                'name' => 'test item two',
                'price' => 200,
                'quantity' => 2,
                'options' => ['color' => 'red', 'size' => 'large'],
            ],
            'item3' => [
                'name' => 'test item three',
                'price' => 250,
                'quantity' => 3,
                'options' => ['color' => 'blue'],
            ]
        ]);
    }
}