# Cart management for Laravel

Manage cart items using sessions.

## Install

Run `composer require abstracteverything/cart` or add:

```
{
    "require": {
        "abstracteverything/cart": "dev-master"
    }
}
```

to your composer.json file and run `composer update`.

Add `AbstractEverything\Cart\CartServiceProvider` to your providers array.

Export the config file using `php artisan vendor:publish`.

## Usage

```
// Add cart items:

$cart->add(123abc, 'Grapes', 12.50, 2, [
    'variety' => 'green',
    'type' => 'seedless',
]);

// Add more than one item to the cart

$cart->addMany([
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

// Find cart item by its id:

$cart->find(123abc);

// Get all the items in the cart:

$cart->all();

// Calculate total price of all items:

$cart->subtotal();

// Calculate the total tax of all items:

$cart->tax();

// Calculate total price with tax of all items:

$cart->subtotalWithTax();

// Remove an item by its id:

$cart->remove(123);

// Count the number of items in the cart:

$cart->count();

// Remove all items from the cart:

$cart->clear();
```