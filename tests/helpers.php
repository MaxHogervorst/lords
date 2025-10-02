<?php

if (!function_exists('factory')) {
    /**
     * Create a model factory builder for a given class and amount.
     * Modern Laravel factory helper for backward compatibility.
     *
     * @param  string  $class
     * @param  int|null  $amount
     * @return \Illuminate\Database\Eloquent\Factories\Factory
     */
    function factory($class, $amount = null)
    {
        $factory = $class::factory();

        if (isset($amount) && is_numeric($amount)) {
            return $factory->count($amount);
        }

        return $factory;
    }
}
