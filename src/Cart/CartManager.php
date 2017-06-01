<?php

namespace AbstractEverything\Cart;

use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use Illuminate\Config\Repository as Config;

class CartManager
{
    /**
     * @var Illuminate\Session\SessionManager
     */
    protected $session;

    /**
     * @var Illuminate\Config\Repository
     */
    protected $config;

    /**
     * Constructor
     * 
     * @param Product        $product
     * @param SessionManager $session
     */
    public function __construct(SessionManager $session, Config $config)
    {
        $this->session = $session;
        $this->config = $config;
    }

    /**
     * Add a product to the cart, if the product id already exists then increment its quantity
     * 
     * @param integer  $id
     * @param string  $name
     * @param integer  $price
     * @param integer $quantity
     * @param array   $options
     * @return array
     */
    public function add($id, $name = '', $price = 0, $quantity = 1, array $options = [])
    {
        $cartItem = $this->getCartSessionCollection()->first(function($value, $key) use ($id) {
            return $key == $id;
        });

        if ($cartItem != null)
        {
            $this->session->put(
                $this->config->get('cart.session_name') . ".{$id}.quantity",
                $cartItem['quantity'] + $quantity
            );

            return $cartItem;
        }

        return $this->create($id, [
            'name' => $name,
            'price' => $price,
            'quantity' => $quantity,
            'options' => $options,
        ]);
    }

    /**
     * Add an array of items to the session
     * 
     * @param array $items
     */
    public function addMany(array $items = [])
    {
        foreach ($items as $item)
        {
            $this->add(
                $item['id'],
                $item['name'],
                $item['price'],
                $item['quantity'],
                $item['options']
            );
        }
    }

    /**
     * Add a new item to the session
     * @param  integer $id
     * @param  array $newItem
     * @return array
     */
    protected function create($id, $newItem)
    {
        $this->session->put(
            $this->config->get('cart.session_name') . '.' . $id,
            $newItem
        );

        return [$id => $newItem];
    }

    /**
     * Find a cart item by its id
     * 
     * @param  integer $id
     * @return array
     */
    public function find($id)
    {
        return $this->getCartSessionCollection()->get($id);
    }

    /**
     * Show all cart session items
     * 
     * @return array
     */
    public function all()
    {
        return $this->getCartSession();
    }

    /**
     * Remove a product from the cart by its id
     * 
     * @param  integer $id
     * @return boolean
     */
    public function remove($id)
    {
        $this->session->forget($this->config->get('cart.session_name') . '.' . $id);
    }

    /**
     * Remove multiple items from the cart by their id
     * 
     * @param  array  $ids
     * @return null
     */
    public function removeMany(array $ids = [])
    {
        foreach ($ids as $id)
        {
            $this->remove($id);
        }
    }

    /**
     * Clear all the cart session data
     * 
     * @return boolean
     */
    public function clear()
    {
        return $this->session->forget($this->config->get('cart.session_name'));
    }

    /**
     * Calculate the subtotal of all the items in the cart
     * 
     * @return integer
     */
    public function subtotal()
    {
        return $this->getCartSessionCollection()->map(function($item, $key) {
            return $item['price'] * $item['quantity'];
        })->reduce(function($carry, $item) {
            return $carry + $item;
        });
    }

    /**
     * Calculate the total tax of all items in the cart
     * 
     * @return integer
     */
    public function tax()
    {
        return $this->getCartSessionCollection()->map(function($item, $key) {
            return (($item['price'] * $item['quantity']) / 100) * $this->config->get('cart.tax_rate');
        })->reduce(function($carry, $item) {
            return $carry + $item;
        });
    }

    /**
     * Add the subtotal to the tax
     * 
     * @return integer
     */
    public function subtotalWithTax()
    {
        return $this->subtotal() + $this->tax();
    }

    /**
     * Count the number of items in the cart
     * 
     * @return integer
     */
    public function count()
    {
        return $this->getCartSessionCollection()->map(function($item, $key) {
            return $item['quantity'];
        })->reduce(function($carry, $item) {
            return $carry + $item;
        });
    }

    /**
     * Get the current cart session
     * 
     * @return array
     */
    public function getCartSession()
    {
        return $this->session->get($this->config->get('cart.session_name'), []);
    }

    /**
     * Get the current cart session as a collection
     * 
     * @return Illuminate\Support\Collection
     */
    public function getCartSessionCollection()
    {
        return $this->getCollection($this->getCartSession());
    }

    /**
     * Create a collection of items
     * 
     * @param  array $items
     * @return Illuminate\Support\Collection
     */
    protected function getCollection(array $items = [])
    {
        return new Collection($items);
    }
}