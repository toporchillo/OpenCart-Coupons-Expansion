<?php
// Heading  
$_['heading_title']       = 'Coupon';

// Text
$_['text_success']        = 'Success: You have modified coupons!';
$_['text_percent']        = 'Percentage';
$_['text_amount']         = 'Fixed Amount';
$_['text_cumulative']     = 'Cumulative Percentage';
$_['text_gift']     	  = 'Gifts and discounts on buy';


$_['button_export'] = 'Export as CSV';
// Column
$_['column_name']         = 'Coupon Name';
$_['column_code']         = 'Code';
$_['column_discount']     = 'Discount';
$_['column_date_start']   = 'Date Start';
$_['column_date_end']     = 'Date End';
$_['column_status']       = 'Status';
$_['column_order_id']     = 'Order ID';
$_['column_order_total']  = 'Order Total';
$_['column_customer']     = 'Customer';
$_['column_amount']       = 'Amount';
$_['column_date_added']   = 'Date Added';
$_['column_total']   	  = 'Complete Orders Total';
$_['column_action']       = 'Action';

// Entry
$_['entry_name']          = 'Coupon Name:';
$_['entry_code']          = 'Code:<br /><span class="help">The code the customer enters to get the discount</span>';
$_['entry_type']          = 'Type:<br /><span class="help">Percentage or Fixed Amount</span>';
$_['entry_discount']      = 'Discount:';
$_['entry_multi_discount'] = 'Cumulative discount: <br /><span class="help">Orders Total - Discount Percentage</span>';
$_['entry_gift_discount'] = 'Gifts: <br /><span class="help">Discount in percents. 100% means gift.</span>';
$_['entry_select_gift'] = 'Select product:';
$_['entry_number_gifts'] = 'Gifts quantity, or maximum quantity of discounted products:';
$_['entry_logged']        = 'Customer Login:<br /><span class="help">Customer must be logged in to use the coupon.</span>';
$_['entry_shipping']      = 'Free Shipping:';
$_['entry_total']         = 'Total Amount:<br /><span class="help">The total amount that must reached before the coupon is valid.</span>';
$_['entry_category']      = 'Category:<br /><span class="help">Choose all products under selected category.</span>';
$_['entry_product']       = 'Products:<br /><span class="help">Choose specific products the coupon will apply to. Select no products to apply coupon to entire cart.</span>';
$_['entry_date_start']    = 'Date Start:';
$_['entry_date_end']      = 'Date End:';
$_['entry_uses_total']    = 'Uses Per Coupon:<br /><span class="help">The maximum number of times the coupon can be used by any customer. Leave blank for unlimited</span>';
$_['entry_uses_customer'] = 'Uses Per Customer:<br /><span class="help">The maximum number of times the coupon can be used by a single customer. Leave blank for unlimited</span>';
$_['entry_status']        = 'Status:';

$_['entry_gen_count']     = 'Number coupons to create:';
$_['entry_gen_digits']    = 'Number of digits in random-generated part of coupon\'s code:';

// Error
$_['error_permission']    = 'Warning: You do not have permission to modify coupons!';
$_['error_exists']        = 'Warning: Coupon code is already in use!';
$_['error_name']          = 'Coupon Name must be between 3 and 128 characters!';
$_['error_code']          = 'Code must be more then 3 characters!';
$_['error_gen_count']           = 'Create at least 1 coupon!';
$_['error_gen_digits']          = 'Too few digits to create this number of coupons!';
?>
