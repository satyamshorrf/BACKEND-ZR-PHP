<?php
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/CartItem.php';
require_once __DIR__ . '/../core/Response.php';

class CartController {
    public function getCartProducts($req, $res) {
        try {
            $userId = $req['user']['id'];
            $cartItemModel = new CartItem();
            $items = $cartItemModel->getByUser($userId);

            $productModel = new Product();
            $cartProducts = [];

            foreach ($items as $item) {
                $product = $productModel->findById($item['product_id']);
                if ($product) {
                    $product['quantity'] = $item['quantity'];
                    $cartProducts[] = $product;
                }
            }

            return Response::json($res, $cartProducts);
        } catch (Exception $e) {
            return Response::json($res, ['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    public function addToCart($req, $res) {
        try {
            $userId = $req['user']['id'];
            $productId = $req['productId'];

            $cartItemModel = new CartItem();
            $existing = $cartItemModel->find($userId, $productId);

            if ($existing) {
                $cartItemModel->updateQuantity($userId, $productId, $existing['quantity'] + 1);
            } else {
                $cartItemModel->add($userId, $productId);
            }

            return Response::json($res, ['message' => 'Cart updated']);
        } catch (Exception $e) {
            return Response::json($res, ['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    public function removeAllFromCart($req, $res) {
        try {
            $userId = $req['user']['id'];
            $productId = $req['productId'] ?? null;

            $cartItemModel = new CartItem();
            if ($productId) {
                $cartItemModel->remove($userId, $productId);
            } else {
                $cartItemModel->clear($userId);
            }

            return Response::json($res, ['message' => 'Cart cleared']);
        } catch (Exception $e) {
            return Response::json($res, ['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    public function updateQuantity($req, $res) {
        try {
            $userId = $req['user']['id'];
            $productId = $req['id'];
            $quantity = $req['quantity'];

            $cartItemModel = new CartItem();
            if ($quantity === 0) {
                $cartItemModel->remove($userId, $productId);
            } else {
                $cartItemModel->updateQuantity($userId, $productId, $quantity);
            }

            return Response::json($res, ['message' => 'Quantity updated']);
        } catch (Exception $e) {
            return Response::json($res, ['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }
}
