<?php
require_once __DIR__ . '/../models/Coupon.php';
require_once __DIR__ . '/../core/Response.php';

class CouponController {
    public function getCoupon($req, $res) {
        try {
            $userId = $req['user']['id'];
            $couponModel = new Coupon();
            $coupon = $couponModel->findActiveByUser($userId);

            return Response::json($res, $coupon ?: null);
        } catch (Exception $e) {
            return Response::json($res, [
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function validateCoupon($req, $res) {
        try {
            $userId = $req['user']['id'];
            $code = $req['code'];

            $couponModel = new Coupon();
            $coupon = $couponModel->findActiveByCodeAndUser($code, $userId);

            if (!$coupon) {
                return Response::json($res, ['message' => 'Coupon not found'], 404);
            }

            $now = date('Y-m-d');
            if ($coupon['expiration_date'] < $now) {
                return Response::json($res, ['message' => 'Coupon is expired'], 400);
            }

            return Response::json($res, [
                'message' => 'Coupon is valid',
                'code' => $coupon['code'],
                'discountPercentage' => $coupon['discount_percentage']
            ]);
        } catch (Exception $e) {
            return Response::json($res, [
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
