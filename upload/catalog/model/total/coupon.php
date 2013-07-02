<?php
class ModelTotalCoupon extends Model {
	var $discounted = array();

	private function getCumulativeDiscount($coupon_info, $amount) {
		$discount = $coupon_info['discount'];
		$discount_plan = $coupon_info['discount_plan'];
		if (!is_array($discount_plan)) return $discount;

		//++++ Detect complete orders total ++++
	  	$sq = $this->db->query("SELECT SUM(o.total) AS total FROM " . DB_PREFIX . "coupon_history ch 
			LEFT JOIN `" . DB_PREFIX . "order` o ON o.order_id = ch.order_id
			WHERE ch.coupon_id='" . (int) $coupon_info['coupon_id'] ."' AND o.order_status_id = '" . (int)$this->config->get('config_complete_status_id') ."'
			GROUP BY coupon_id");
		$orders_total = (!$sq->num_rows ? 0 : $sq->row['total']);
		//---- Detect complete orders total ----
		
		foreach ($discount_plan as $tot=>$disct) {
			if ($orders_total + $amount >= $tot)
				$discount = $disct;
		}
		return $discount;
	}

	private function getGiftedDiscount($coupon_info, $cart_products, $amount) {
		$gifts_num = 0;
		$sub_total = 0;
		$discount_plan = $coupon_info['discount_plan'];
		$cart_product_ids = array();
		if (!$coupon_info['product']) {
			$gifts_num = intval($coupon_info['discount']);
		}
		foreach ($cart_products as $product) {
			$cart_product_ids[] = $product['product_id'];
			if (in_array($product['product_id'], $coupon_info['product'])) {
				$gifts_num += $product['quantity'] * intval($coupon_info['discount']);
			}
		}
		$discount = 0;
		if (isset($this->request->post['coupon']) && !isset($this->session->data['temp_coupon'])) {
			$this->session->data['temp_coupon'] = $this->request->post['coupon'];
			$cart_added = false;
			foreach ($discount_plan as $product_id=>$perc) {
				if (!in_array($product_id, $cart_product_ids)) {
					if ($perc >= 100)
						$this->cart->add($product_id, $gifts_num);
					else
						$this->cart->add($product_id, 1);
					$cart_added = true;
				}
			}
			$cart_products = $cart_added ? $this->cart->getProducts() : $cart_products;
		}
		foreach ($cart_products as $product) {
			if (isset($discount_plan[$product['product_id']])) {
				$discount+= $product['price'] * min($product['quantity'], $gifts_num) * $discount_plan[$product['product_id']] / 100;
			}
		}
		return $discount;
	}
	
	/**
	 * Returns coupon disconted products with prices 
	 * @return array(product_id=>price)
	 */
	private function getDiscountedProducts($cart_products, $coupon_info) {
		$ret = array();
		if ($coupon_info['type'] == 'F') {
			return $ret;
		}
		$gifts_num = 0;
		foreach ($cart_products as $product) {
			if (!$coupon_info['product'] || in_array($product['product_id'], $coupon_info['product'])) {
				if ($coupon_info['type'] == 'P' || $coupon_info['type'] == 'C') {
					$ret[$product['product_id']] = $product['price'] * (100 - $coupon_info['discount']) / 100;
				}
				elseif ($coupon_info['type'] == 'G') {
					if (!$coupon_info['product']) {
						$gifts_num = intval($coupon_info['gifts_number']);
						break;
					}
					$gifts_num += $product['quantity'] * intval($coupon_info['gifts_number']);
				}
			}
		}
		if ($coupon_info['type'] == 'G') {
			$discount_plan = $coupon_info['discount_plan'];
			foreach ($cart_products as $product) {
				if (isset($discount_plan[$product['product_id']])) {
					$ret[$product['product_id']] = ($product['price'] * min($product['quantity'], $gifts_num) * (100 - $discount_plan[$product['product_id']]) / 100
						+ $product['price'] * ($product['quantity'] - min($product['quantity'], $gifts_num))) / $product['quantity'];
				}
			}
		}
		return $ret;
	}

	public function getTotal(&$total_data, &$total, &$taxes) {
		if (isset($this->session->data['coupon'])) {
			$this->load->language('total/coupon');
			
			$this->load->model('checkout/coupon');
			 
			$coupon_info = $this->model_checkout_coupon->getCoupon($this->session->data['coupon']);
			
			if ($coupon_info) {
				$discount_total = 0;

				$cart_products = $this->cart->getProducts();
				if (!$coupon_info['product']) {
					$sub_total = $this->cart->getSubTotal();
				} else {
					$sub_total = 0;
					foreach ($cart_products as $product) {
						if (in_array($product['product_id'], $coupon_info['product'])) {
							$sub_total += $product['total'];
						}
					}
				}
				
				if ($coupon_info['type'] == 'F') {
					$coupon_info['discount'] = min($coupon_info['discount'], $sub_total);
				}
				else if ($coupon_info['type'] == 'C') {
					$coupon_info['discount'] = $this->getCumulativeDiscount($coupon_info, $sub_total);
				}
				else if ($coupon_info['type'] == 'G') {
					$coupon_info['gifts_number'] = $coupon_info['discount'];
					$coupon_info['discount'] = $this->getGiftedDiscount($coupon_info, $cart_products, $sub_total);
				}
				
				foreach ($this->cart->getProducts() as $product) {
					$discount = 0;
					
					if (!$coupon_info['product']) {
						$status = true;
					} else {
						if (in_array($product['product_id'], $coupon_info['product'])) {
							$status = true;
						} else {
							$status = false;
						}
					}
					
					if ($status) {
						if ($coupon_info['type'] == 'F') {
							$discount = $coupon_info['discount'] * ($product['total'] / $sub_total);
						} elseif ($coupon_info['type'] == 'P' || $coupon_info['type'] == 'C') {
							$discount = $product['total'] / 100 * $coupon_info['discount'];
						} elseif ($coupon_info['type'] == 'G') {
							$discount = $coupon_info['discount'];
						}
				
						if ($product['tax_class_id']) {
							$tax_rates = $this->tax->getRates($product['total'] - ($product['total'] - $discount), $product['tax_class_id']);
							
							foreach ($tax_rates as $tax_rate) {
								if ($tax_rate['type'] == 'P' || $tax_rate['type'] == 'C') {
									$taxes[$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
								}
							}
						}
					}
					
					$discount_total += $discount;
				}
				
				if ($coupon_info['shipping'] && isset($this->session->data['shipping_method'])) {
					if (!empty($this->session->data['shipping_method']['tax_class_id'])) {
						$tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);
						
						foreach ($tax_rates as $tax_rate) {
							if ($tax_rate['type'] == 'P' || $tax_rate['type'] == 'C') {
								$taxes[$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
							}
						}
					}
					
					$discount_total += $this->session->data['shipping_method']['cost'];				
				}				

				$total -= $discount_total;
				$js_str = '';
				if ($coupon_info['type'] == 'C') {
					$js_str_text = sprintf($this->language->get('coupon_current_discount'), $coupon_info['discount']);
					foreach ($coupon_info['discount_plan'] as $tot=>$disct) {
						if ($disct > $coupon_info['discount']) {
							$more = $this->currency->format($tot - $total);
							$js_str_text.= sprintf($this->language->get('coupon_next_discount'), $more, $disct);
							break;
						}
					}
					$js_str = "<script>
						$(document).ready(function() {
							$('#coupon-info').remove();
							$('input[name=\'coupon\']').after('<div id=\"coupon-info\" style=\"color:red;\">$js_str_text</div>');
						})
					</script>";
					
				}
      			
				$total_data[] = array(
					'code'       => 'coupon',
        			'title'      => sprintf($this->language->get('text_coupon'), $this->session->data['coupon']),
	    			'text'       => $this->currency->format(-$discount_total).$js_str,
        			'value'      => -$discount_total,
					'sort_order' => $this->config->get('coupon_sort_order'),
					'discounted_products' => $this->getDiscountedProducts($cart_products, $coupon_info)
      			);

			}
			else {
				unset($this->session->data['temp_coupon']);
			} 
		}
	}
	
	public function confirm($order_info, $order_total) {
		$code = '';
		
		$start = strpos($order_total['title'], '(') + 1;
		$end = strrpos($order_total['title'], ')');
		
		if ($start && $end) {  
			$code = substr($order_total['title'], $start, $end - $start);
		}	
		
		$this->load->model('checkout/coupon');
		
		$coupon_info = $this->model_checkout_coupon->getCoupon($code);
			
		if ($coupon_info) {
			$this->model_checkout_coupon->redeem($coupon_info['coupon_id'], $order_info['order_id'], $order_info['customer_id'], $order_total['value']);	
		}						
	}
}
?>
