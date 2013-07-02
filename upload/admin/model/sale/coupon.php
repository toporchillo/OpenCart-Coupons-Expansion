<?php
class ModelSaleCoupon extends Model {
	
	public function getProductNames($ids) {
		$ids = array_map('intval', $ids);
		$ret = array();
		$query = $this->db->query("SELECT product_id, name FROM " . DB_PREFIX . "product_description pd
			WHERE product_id IN ('" . implode("',' ", $ids) ."') AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		foreach ($query->rows as $row) {
			$ret[$row['product_id']] = $row['name'];
		}
		return $ret;
	}
	 
	public function recalculateDiscount($order_id) {
      	$cq = $this->db->query("SELECT coupon_id FROM " . DB_PREFIX . "coupon_history WHERE order_id='" . (int) $order_id ."'");
      	if (!$cq->num_rows) return false;
      	$coupon_id = $cq->row['coupon_id'];
      	$coupon_info = $this->getCoupon($coupon_id);
      	if ($coupon_info['type'] != 'C') return false;
      	$sq = $this->db->query("SELECT SUM(o.total) AS total FROM " . DB_PREFIX . "coupon_history ch 
			LEFT JOIN `" . DB_PREFIX . "order` o ON o.order_id = ch.order_id
			WHERE ch.coupon_id='" . (int) $coupon_id ."' AND o.order_status_id = '" . (int)$this->config->get('config_complete_status_id') ."'
			GROUP BY coupon_id");
      	if (!$sq->num_rows) $sq->row['total'] = 0;
		$discount_plan = $coupon_info['discount_plan'];
		if (!is_array($discount_plan)) return false;
		$discount = $coupon_info['discount'];
		foreach ($discount_plan as $tot=>$disct) {
			if ($sq->row['total'] >= $tot)
				$discount = $disct;
		}
		$this->db->query("UPDATE " . DB_PREFIX . "coupon SET discount='" . (float)$discount ."' WHERE coupon_id='" . (int)$coupon_id . "'");
		return $discount;
	}
	
	public function addCoupon($data) {
      	$this->db->query("INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', discount_plan ='" . $this->db->escape(serialize($data['discount_plan'])) . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . (int)$data['uses_customer'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

      	$coupon_id = $this->db->getLastId();
		
		if (isset($data['coupon_product'])) {
      		foreach ($data['coupon_product'] as $product_id) {
        		$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int)$coupon_id . "', product_id = '" . (int)$product_id . "'");
      		}			
		}
	}
	
	public function editCoupon($coupon_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', discount_plan ='" . $this->db->escape(serialize($data['discount_plan'])) . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . (int)$data['uses_customer'] . "', status = '" . (int)$data['status'] . "' WHERE coupon_id = '" . (int)$coupon_id . "'");
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");
		
		if (isset($data['coupon_product'])) {
      		foreach ($data['coupon_product'] as $product_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int)$coupon_id . "', product_id = '" . (int)$product_id . "'");
      		}
		}		
	}
	
	public function deleteCoupon($coupon_id) {
      	$this->db->query("DELETE FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");		
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");		
	}
	
	public function getCoupon($coupon_id) {
      	$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");
		if ($query->row['discount_plan']) $query->row['discount_plan'] = unserialize($query->row['discount_plan']);
		return $query->row;
	}

	public function getCouponByCode($code) {
      	$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE code = '" . $this->db->escape($code) . "'");
		
		return $query->row;
	}
		
	public function getCoupons($data = array()) {
		$sql = "SELECT c.coupon_id, c.name, c.code, c.discount, c.date_start, c.date_end, c.status,
			(SELECT SUM(o.total) FROM " . DB_PREFIX . "coupon_history ch
				LEFT JOIN `" . DB_PREFIX . "order` o ON o.order_id = ch.order_id
				WHERE ch.coupon_id = c.coupon_id AND o.order_status_id = '" . (int)$this->config->get('config_complete_status_id') ."' GROUP BY c.coupon_id) AS total
			FROM " . DB_PREFIX . "coupon c WHERE 1=1";
		if (isset($data['name']))
			$sql.= " AND c.name LIKE '%".$this->db->escape($data['name'])."%'";
		if (isset($data['code']))
			$sql.= " AND c.code LIKE '%".$this->db->escape($data['code'])."%'";
		
		$sort_data = array(
			'name',
			'code',
			'discount',
			'date_start',
			'date_end',
			'status',
			'total'
		);	
			
		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];	
		} else {
			$sql .= " ORDER BY name";	
		}
			
		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}
		
		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}			

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}	
			
			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}		
		
		$query = $this->db->query($sql);
		
		return $query->rows;
	}

	public function getCouponProducts($coupon_id) {
		$coupon_product_data = array();
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");
		
		foreach ($query->rows as $result) {
			$coupon_product_data[] = $result['product_id'];
		}
		
		return $coupon_product_data;
	}
	
	public function getTotalCoupons() {
      	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "coupon");
		
		return $query->row['total'];
	}	
	
	public function getCouponHistories($coupon_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}
		
		if ($limit < 1) {
			$limit = 10;
		}	
				
		$query = $this->db->query("SELECT ch.order_id, o.total, o.currency_code, o.currency_value, 
				(SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS status,
				CONCAT(o.firstname, ' ', o.lastname) AS customer, ch.amount, ch.date_added FROM " . DB_PREFIX . "coupon_history ch
			LEFT JOIN " . DB_PREFIX . "customer c ON (ch.customer_id = c.customer_id)
			LEFT JOIN `" . DB_PREFIX . "order` o ON (ch.order_id = o.order_id)
			WHERE ch.coupon_id = '" . (int)$coupon_id . "' ORDER BY ch.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}
	
	public function getTotalCouponHistories($coupon_id) {
	  	$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");

		return $query->row['total'];
	}			
}
?>
