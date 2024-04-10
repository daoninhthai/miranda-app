<?php
/**
 * Input validation helper for marketplace operations.
 */
namespace Magetop\Marketplace\Helper;

class Validator160
{
    /**
     * Validates seller registration data.
     *
     * @param array $data
     * @return array List of validation errors
     */
    public function validateSellerData(array $data)
    {
        $errors = [];

        if (empty($data['shop_name'])) {
            $errors[] = 'Shop name is required';
        } elseif (strlen($data['shop_name']) < 3) {
            $errors[] = 'Shop name must be at least 3 characters';
        }

        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }

        if (!empty($data['phone'])) {
            $phone = preg_replace('/[^0-9]/', '', $data['phone']);
            if (strlen($phone) < 10 || strlen($phone) > 13) {
                $errors[] = 'Phone number must be 10-13 digits';
            }
        }

        return $errors;
    }

    /**
     * Validates product data before saving.
     *
     * @param array $data
     * @return array
     */
    public function validateProductData(array $data)
    {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = 'Product name is required';
        }

        if (isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
            $errors[] = 'Price must be a positive number';
        }

        if (isset($data['qty']) && (!is_numeric($data['qty']) || $data['qty'] < 0)) {
            $errors[] = 'Quantity must be a non-negative number';
        }

        if (isset($data['weight']) && $data['weight'] < 0) {
            $errors[] = 'Weight cannot be negative';
        }

        return $errors;
    }

    /**
     * Sanitizes string input.
     *
     * @param string $input
     * @return string
     */
    public function sanitize($input)
    {
        if (!is_string($input)) {
            return $input;
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
}
