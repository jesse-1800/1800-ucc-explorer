<?php namespace App\Models;

/**
 * Class CostModel
 * Handles calculations for equipment costs, margin prices, and lease factors.
 */
class CostModel {

    /**
     * This function returns either the 'monthly' or 'one-time' cost
     * based on the $cart_item->product->charge_type
     * @param $proposal
     * @param $cart_item
     * @param $charge_type
     * @return int
     */
    public static function single_equipment_total_cost($proposal, $cart_item, $force_lease_term = null) {
        $single_subtotal = 0;

        # Get the copier's margin price
        $single_subtotal += self::get_margin_price(
            ($cart_item->product->price * $cart_item->product->qty),
            $cart_item->product->price_margin
        );

        # Add accessories price
        foreach (Products::attachments($cart_item) as $attachment) {
            $single_subtotal += self::get_margin_price(
                ($attachment->accessory_price * $attachment->qty),
                $attachment->price_margin
            );
        }

        # If it's monthly, return the monthly payment
        if ($cart_item->product->charge_type == 'monthly') {
            # Use the custom lease term (for lease term options)
            if ($force_lease_term) {
                return $single_subtotal * self::lease_factor($proposal, $force_lease_term);
            }
            return $single_subtotal * self::lease_factor($proposal);
        }

        # If it's fixed-monthly, return the fixed monthly payment
        else if ($cart_item->product->charge_type == 'fixed-monthly') {
            return $single_subtotal;
        }

        # If it's one-time, return the one-time payment
        else {
            return $single_subtotal;
        }
    }

    /**
     * Returns % margin of a base price
     * e.g. 20% margin price of $100 is just 100 / 0.8 = 125
     */
    public static function get_margin_price(float $base_price, float $price_margin): float {
        if ($price_margin == 0) {
            return $base_price;
        }

        $margin_decimal = $price_margin / 100;

        // Prevent divide by zero or >100% margin
        if ($margin_decimal >= 1) {
            return INF;
        }

        $selling_price = $base_price / (1 - $margin_decimal);
        return round($selling_price, 2);
    }

    /**
     * Provides the lease factor only e.g. 0.088
     * @param $proposal
     * @param null|int $custom_lease_term
     */
    public static function lease_factor($proposal, $custom_lease_term = null)
    {
        $provider = $proposal->lease_factor_provider;
        $lease_term = $proposal->lease_term_offered;

        if ($custom_lease_term) {
            $lease_term = $custom_lease_term;
        }

        $lease_type = $proposal->lease_type;
        $lease_factor = 0;

        foreach ($provider->lease_factors as $factor) {
            if ($factor->term == $lease_term) {
                $lease_factor = $factor->$lease_type;
            }
        }

        return $lease_factor;
    }

    /**
     * Computes for Global Print Cost.
     * @param $proposal
     **/
    public static function global_print_cost($proposal, $no_formatting = false) {
        if ($proposal->prints_included_free==1) {
            return number_format(0,2);
        }

        $global = $proposal->global_print_cost;
        $total_black = self::get_margin_price(
            $global->black_prints_included * $global->black_prints_cost,
            $global->black_prints_margin
        );
        $total_color = self::get_margin_price(
            $global->color_prints_included * $global->color_prints_cost,
            $global->color_prints_margin
        );

        # If it's monthly, compute against lease factor
        $total_amount = $total_black + $total_color;
        if ($proposal->global_print_cost->charge_type == 'monthly') {
            $total_amount = $total_amount * self::lease_factor($proposal);
        }

        # Returns float as it is.
        if ($no_formatting) {
            return $total_amount;
        }

        # Return ready to display number format
        return number_format($total_amount, 2);
    }

    /**
     * Computes for PER-COPIER Print Cost for ALL PRINTERS.
     * @param $proposal
     **/
    public static function per_copier_print_cost($proposal, $no_formatting = false, $monthly_only = false) {
        if ($proposal->prints_included_free==1) {
            return number_format(0,2);
        }

        $per_copier_total = 0;

        # Iterate through cart_items to total print cost
        foreach ($proposal->cart_items as $cart_item) {
            $total_black = self::get_margin_price(
                $cart_item->print_cost->black_prints_included * $cart_item->print_cost->black_prints_cost,
                $cart_item->print_cost->black_prints_margin
            );
            $total_color = self::get_margin_price(
                $cart_item->print_cost->color_prints_included * $cart_item->print_cost->color_prints_cost,
                $cart_item->print_cost->color_prints_margin
            );

            if ($monthly_only) {
                if ($cart_item->print_cost->charge_type == 'one-time') {
                    continue;
                }
            }

            if ($cart_item->print_cost->charge_type == 'monthly') {
                $per_copier_total = ($total_black + $total_color) * self::lease_factor($proposal);
            }
            else if ($cart_item->print_cost->charge_type == 'fixed-monthly') {
                $per_copier_total += ($total_black + $total_color);
            }
            else {
                $per_copier_total += ($total_black + $total_color);
            }
        }

        # Returns float as it is.
        if ($no_formatting) {
            return $per_copier_total;
        }

        # Return ready to display number format
        return number_format($per_copier_total, 2);
    }

    /**
     * Provides calculation for Cost Addons
     *
     * @param $proposal
     * @param $addon
     */
    public static function single_addon_cost($proposal, $addon) {
        $lease_factor = self::lease_factor($proposal);
        $addon_total = self::get_margin_price(
            ($addon->price * $addon->qty), $addon->price_margin
        );
        if ($addon->charge_type == 'monthly') {
            return $addon_total * $lease_factor;
        } else {
            return $addon_total;
        }
    }

    /**
     * Computes for Grand Total
     */
    public static function grand_total_monthly($proposal, $lease_term)
    {
        $grand_total_monthly = 0;
        $lease_factor = self::lease_factor($proposal, $lease_term);

        # Iterate through items
        foreach ($proposal->cart_items as $cart_item) {
            if ($cart_item->product->charge_type != 'one-time') {
                $grand_total_monthly += self::single_equipment_total_cost($proposal,$cart_item,$lease_term);
            }
        }

        # Maintenance (Prints Included)
        if (CostModel::is_global_prints($proposal)) {
            if ($proposal->global_print_cost->charge_type != 'one-time') {
                $grand_total_monthly += self::global_print_cost($proposal, true);
            }
        } else {
            if ($proposal->cart_items[0]->print_cost->charge_type != 'one-time') {
                $grand_total_monthly += self::per_copier_print_cost($proposal, true);
            }
        }

        # Addons
        foreach ($proposal->cost_addons as $addon) {
            if ($addon->charge_type != 'one-time') {
                $grand_total_monthly += self::single_addon_cost($proposal, $addon);
            }
        }

        return number_format($grand_total_monthly, 2);
    }

    /**
     * Computes for Grand Total One-Time payments
     */
    public static function grand_total_one_time($proposal)
    {
        $grand_total_one_time = 0;

        # Product Items
        foreach ($proposal->cart_items as $cart_item) {
            if ($cart_item->product->charge_type == 'one-time') {
                $grand_total_one_time += self::single_equipment_total_cost($proposal,$cart_item);
            }
        }

        # Maintenance and Service
        if (CostModel::is_global_prints($proposal)) {
            # For Global Prints
            if ($proposal->global_print_cost->charge_type == 'one-time') {
                $grand_total_one_time += self::global_print_cost($proposal, true);
            }
        } else {
            # For Per-Copier Prints
            foreach ($proposal->cart_items as $cart_item) {
                if ($cart_item->print_cost->charge_type == 'one-time') {
                    $grand_total_one_time += self::per_copier_print_cost($proposal, true);
                }
            }
        }

        # Addons first
        foreach ($proposal->cost_addons as $addon) {
            if ($addon->charge_type == 'one-time') {
                $grand_total_one_time += self::get_margin_price(
                    ($addon->price * $addon->qty),
                    $addon->price_margin
                );
            }
        }

        return $grand_total_one_time;
    }

    /**
     * Returns all prints included both Global/Per-copier
     * if PER-COPIER, it returns total prints from all copiers
     */
    public static function total_prints_included($proposal) {
        $total_black = 0;
        $total_color = 0;

        if ($proposal->is_global_print_cost == 1) {
            $global = $proposal->global_print_cost;
            $total_black = $global->black_prints_included;
            $total_color = $global->color_prints_included;
        } else {
            foreach ($proposal->cart_items as $cart_item) {
                $per_copier = $cart_item->print_cost;
                $total_black += $per_copier->black_prints_included;
                $total_color += $per_copier->color_prints_included;
            }
        }

        return [
            'black' => $total_black,
            'color' => $total_color
        ];
    }

    /**
     * Returns true if proposal is 'lease'
     */
    public static function is_leasing($proposal)
    {
        return $proposal->acquisition_type == 'lease';
    }

    /**
     * Returns true if proposal is 'purchase'
     */
    public static function is_purchasing($proposal)
    {
        return $proposal->acquisition_type == 'purchase';
    }

    /**
     * Returns true if is global prints
     */
    public static function is_global_prints($proposal)
    {
        return $proposal->is_global_print_cost == 1;
    }

    /**
     * Charge type alias only.
     */
    public static function charge_type_alias($charge_type)
    {
        if ($charge_type == 'monthly') {
            return 'Recurring Payment';
        } else if ($charge_type == 'fixed-monthly') {
            return 'Recurring Payment';
        } else {
            return 'One-Time Payment';
        }
    }

    /**
     * IT Service Line Item Term Multiplier
     */
    public static function it_svc_line_item_term_multiplier($term) {
        $it_svc_terms = [
            [ "value" => "monthly",  "title" => "Monthly",  "multiplier" => 1 ],
            [ "value" => "annually", "title" => "Annually", "multiplier" => 12 ],
            [ "value" => "one-time", "title" => "One-Time", "multiplier" => 1 ],
        ];

        foreach ($it_svc_terms as $it_svc_term) {
            if ($it_svc_term['value'] == $term) {
                return $it_svc_term['multiplier'];
            }
        }
    }

    /**
     * IT Tiered Package Subtotal
     */
    public static function it_svc_tier_subtotal($cart_item)
    {
        $tier_total = 0;
        if (!empty($cart_item->tier)) {
            foreach ($cart_item->items as $line_item) {
                $tier_total += self::it_svc_line_item_subtotal($line_item);
            }
        }
        return $tier_total;
    }

    /**
     * IT Service Line Item Subtotal
     */
    public static function it_svc_line_item_subtotal($line_item)
    {
        $multiplier  = self::it_svc_line_item_term_multiplier($line_item->charge_type);
        $with_margin = self::get_margin_price(
            $line_item->unit_price * $line_item->quantity,
            $line_item->price_margin
        );

        return $with_margin * $multiplier;
    }


    /**
     * One-time Grand Total for IT Service Proposal
     */
    public static function it_svc_one_time_grand_total($proposal)
    {
        $onetime_grand_total = 0;
        foreach ($proposal->it_service_items as $cart_item) {
            foreach ($cart_item->items as $line_item) {
                if ($line_item->charge_type == 'one-time') {
                    $onetime_grand_total += self::it_svc_line_item_subtotal($line_item);
                }
            }
        }
        return $onetime_grand_total;
    }

    /**
     * RECURRING Grand Total for IT Service Proposal
     */
    public static function it_svc_recurring_grand_total($proposal)
    {
        $recurring_grand_total = 0;
        foreach ($proposal->it_service_items as $cart_item) {
            foreach ($cart_item->items as $line_item) {
                if ($line_item->charge_type !== 'one-time') {
                    $recurring_grand_total += self::it_svc_line_item_subtotal($line_item);
                }
            }
        }
        return $recurring_grand_total;
    }

    /**
     * Simply returns lease type alias
     * @param $proposal
     * @return string
     **/
    public static function lease_type_alias($proposal)
    {
        if ($proposal->lease_type == 'fmv') {
            return "FMV (Fair Market Value)";
        } elseif ($proposal->lease_type == 'buyout') {
            return "$1 Buyout";
        } elseif ($proposal->lease_type == 'efa') {
            return 'EFA';
        } else {
            "Unknown";
        }
    }

    /**
     * Simply returns global_overage_rates
     * @param $proposal
     * @return string
     **/
    public static function global_overages_rates($proposal)
    {
        $black_overage_cost = $proposal->global_print_cost->black_overage_cost;
        $color_overage_cost = $proposal->global_print_cost->color_overage_cost;

        if ($proposal->is_global_print_cost==0) {
            $black_overage_cost = 0;
            $color_overage_cost = 0;
        }

        return [
            'black' => $black_overage_cost,
            'color' => $color_overage_cost,
        ];
    }
}