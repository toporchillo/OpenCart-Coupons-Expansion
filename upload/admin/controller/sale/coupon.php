<?php
class ControllerSaleCoupon extends Controller {
	private $error = array();
     
  	public function index() {
		$this->load->language('sale/coupon');
    	
		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/coupon');
		
		$this->getList();
  	}
  
	private function getDiscountPlan($post) {
		if ($post['type'] == 'C') {
			$p_b = $post['discount_b'];
			$p_v = $post['discount_v'];
		}
		else if ($post['type'] == 'G') {
			$p_b = $post['discount_g'];
			$p_v = $post['discount_p'];
		}
		if (($post['type'] == 'C' || $post['type'] == 'G') && is_array($p_b) && count($p_b) > 1) {
			$plan = array();
			foreach($p_b as $i=>$b) {
				if ($i > 0) $plan[floatval($b)] = floatval($p_v[$i]);
			}
			ksort($plan);
			return $plan;
		}
		else {
			return '';
		}
	}

	private function generateCode($prefix, $digits) {
		for ($att=0; $att<5; $att++) {
			$code = '';
			for ($i=0; $i<$digits; $i++) {
				$code.= rand(0,9);
			}
			$coupon_info = $this->model_sale_coupon->getCouponByCode($prefix.$code);
			if (!$coupon_info) return $prefix.$code;
		}
		return '[:|||||||||:]';
	}
	
  	public function insert() {
    	$this->load->language('sale/coupon');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/coupon');
		
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->request->post['discount_plan'] = $this->getDiscountPlan($this->request->post);
			$gen_digits = intval($this->request->post['gen_digits']);
			$gen_count = intval($this->request->post['gen_count']);
			$coupon_data = $this->request->post;
			if ($coupon_data['type'] == 'G') {
				$coupon_data['discount'] = $coupon_data['number_gifts'];
				unset($coupon_data['number_gifts']);
			}
			for ($i=0; $i<$gen_count; $i++) {
				if ($gen_digits > 0) {
					$coupon_data['code'] = $this->generateCode($this->request->post['code'], $gen_digits);
				}
				$this->model_sale_coupon->addCoupon($coupon_data);
			}
			
			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			$this->redirect($this->url->link('sale/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL'));
    	}
    
    	$this->getForm();
  	}

  	public function update() {
    	$this->load->language('sale/coupon');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/coupon');
				
    	if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->request->post['discount_plan'] = $this->getDiscountPlan($this->request->post);
			$coupon_data = $this->request->post;
			if ($coupon_data['type'] == 'G') {
				$coupon_data['discount'] = $coupon_data['number_gifts'];
				unset($coupon_data['number_gifts']);
			}
			$this->model_sale_coupon->editCoupon($this->request->get['coupon_id'], $coupon_data);
      		
			$this->session->data['success'] = $this->language->get('text_success');
	  
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			$this->redirect($this->url->link('sale/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL'));
		}
    
    	$this->getForm();
  	}

  	public function delete() {
    	$this->load->language('sale/coupon');

    	$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('sale/coupon');
		
    	if (isset($this->request->post['selected']) && $this->validateDelete()) { 
			foreach ($this->request->post['selected'] as $coupon_id) {
				$this->model_sale_coupon->deleteCoupon($coupon_id);
			}
      		
			$this->session->data['success'] = $this->language->get('text_success');
	  
			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}
			
			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
						
			$this->redirect($this->url->link('sale/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL'));
    	}
	
    	$this->getList();
  	}

	public function export() {
		$this->load->model('sale/coupon');
		
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		$data = array(
			'sort'  => $sort,
			'order' => $order
		);
		
		if (isset($this->request->get['filter_name']) && $this->request->get['filter_name'] != '') {
			$data['name'] = $this->request->get['filter_name'];
		}
		if (isset($this->request->get['filter_code']) && $this->request->get['filter_code'] != '') {
			$data['code'] = $this->request->get['filter_code'];
		}
		
		$results = $this->model_sale_coupon->getCoupons($data);
		
		header("Content-Encoding: UTF-8");
		header("Content-type: csv/plain; charset=UTF-8");
		header("Content-Disposition: attachment; filename=coupons.csv");
		echo "\xEF\xBB\xBF"; // UTF-8 BOM

    	foreach ($results as $result) {
			echo $result['name'].';'.$result['code'].';'.$result['discount'].';'
				.date($this->language->get('date_format_short'), strtotime($result['date_start'])).';'
				.date($this->language->get('date_format_short'), strtotime($result['date_end'])).';'
				.($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')).';'
				.$this->currency->format($result['total'])."\n";
		}
	}
	
  	private function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}
		
		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}
		
		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}
				
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}
			
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
							
		$this->data['insert'] = $this->url->link('sale/coupon/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		$this->data['delete'] = $this->url->link('sale/coupon/delete', 'token=' . $this->session->data['token'] . $url, 'SSL');
		
		$this->data['coupons'] = array();

		$data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_admin_limit'),
			'limit' => $this->config->get('config_admin_limit')
		);
		if (isset($this->request->get['filter_name']) && $this->request->get['filter_name'] != '') {
			$data['name'] = $this->request->get['filter_name'];
		}
		if (isset($this->request->get['filter_code']) && $this->request->get['filter_code'] != '') {
			$data['code'] = $this->request->get['filter_code'];
		}
		
		$coupon_total = $this->model_sale_coupon->getTotalCoupons();
	
		$results = $this->model_sale_coupon->getCoupons($data);
 
    	foreach ($results as $result) {
			$action = array();
						
			$action[] = array(
				'text' => $this->language->get('text_edit'),
				'href' => $this->url->link('sale/coupon/update', 'token=' . $this->session->data['token'] . '&coupon_id=' . $result['coupon_id'] . $url, 'SSL')
			);
						
			$this->data['coupons'][] = array(
				'coupon_id'  => $result['coupon_id'],
				'name'       => $result['name'],
				'code'       => $result['code'],
				'discount'   => $result['discount'],
				'date_start' => date($this->language->get('date_format_short'), strtotime($result['date_start'])),
				'date_end'   => date($this->language->get('date_format_short'), strtotime($result['date_end'])),
				'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'total'     => $this->currency->format($result['total']),
				'selected'   => isset($this->request->post['selected']) && in_array($result['coupon_id'], $this->request->post['selected']),
				'action'     => $action
			);
		}
									
		$this->data['heading_title'] = $this->language->get('heading_title');

		$this->data['text_no_results'] = $this->language->get('text_no_results');

		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_code'] = $this->language->get('column_code');
		$this->data['column_discount'] = $this->language->get('column_discount');
		$this->data['column_date_start'] = $this->language->get('column_date_start');
		$this->data['column_date_end'] = $this->language->get('column_date_end');
		$this->data['column_status'] = $this->language->get('column_status');
		$this->data['column_total'] = $this->language->get('column_total');
		$this->data['column_action'] = $this->language->get('column_action');		
		
		$this->data['button_insert'] = $this->language->get('button_insert');
		$this->data['button_delete'] = $this->language->get('button_delete');
		$this->data['button_filter'] = $this->language->get('button_filter');
		$this->data['button_export'] = $this->language->get('button_export');
 
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
		
		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
		
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$this->data['token'] = $this->session->data['token'];
		if (isset($this->request->get['filter_name'])) {
			$this->data['filter_name'] = $this->request->get['filter_name'];
			$url.= '&filter_name=' . $this->request->get['filter_name'];
		}
		else {
			$this->data['filter_name'] = '';
		}
		if (isset($this->request->get['filter_code'])) {
			$this->data['filter_code'] = $this->request->get['filter_code'];
			$url.= '&filter_code=' . $this->request->get['filter_code'];
		}
		else {
			$this->data['filter_code'] = '';
		}
		
		$this->data['sort_name'] = HTTPS_SERVER . 'index.php?route=sale/coupon&token=' . $this->session->data['token'] . '&sort=name' . $url;
		$this->data['sort_code'] = HTTPS_SERVER . 'index.php?route=sale/coupon&token=' . $this->session->data['token'] . '&sort=code' . $url;
		$this->data['sort_discount'] = HTTPS_SERVER . 'index.php?route=sale/coupon&token=' . $this->session->data['token'] . '&sort=discount' . $url;
		$this->data['sort_date_start'] = HTTPS_SERVER . 'index.php?route=sale/coupon&token=' . $this->session->data['token'] . '&sort=date_start' . $url;
		$this->data['sort_date_end'] = HTTPS_SERVER . 'index.php?route=sale/coupon&token=' . $this->session->data['token'] . '&sort=date_end' . $url;
		$this->data['sort_status'] = HTTPS_SERVER . 'index.php?route=sale/coupon&token=' . $this->session->data['token'] . '&sort=status' . $url;
		$this->data['sort_total'] = HTTPS_SERVER . 'index.php?route=sale/coupon&token=' . $this->session->data['token'] . '&sort=total' . $url;
				
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}
												
		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $coupon_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_admin_limit');
		$pagination->text = $this->language->get('text_pagination');
		$pagination->url = HTTPS_SERVER . 'index.php?route=sale/coupon&token=' . $this->session->data['token'] . $url . '&page={page}';
			
		$this->data['pagination'] = $pagination->render();

		$this->data['sort'] = $sort;
		$this->data['order'] = $order;
		
		$this->template = 'sale/coupon_list.tpl';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->response->setOutput($this->render());
  	}

  	private function getForm() {
    	$this->data['heading_title'] = $this->language->get('heading_title');

    	$this->data['text_enabled'] = $this->language->get('text_enabled');
    	$this->data['text_disabled'] = $this->language->get('text_disabled');
    	$this->data['text_yes'] = $this->language->get('text_yes');
    	$this->data['text_no'] = $this->language->get('text_no');
    	$this->data['text_percent'] = $this->language->get('text_percent');
    	$this->data['text_amount'] = $this->language->get('text_amount');
    	$this->data['text_cumulative'] = $this->language->get('text_cumulative');
    	$this->data['text_gift'] = $this->language->get('text_gift');
				
		$this->data['entry_name'] = $this->language->get('entry_name');
    	$this->data['entry_description'] = $this->language->get('entry_description');
    	$this->data['entry_code'] = $this->language->get('entry_code');
		$this->data['entry_discount'] = $this->language->get('entry_discount');
		$this->data['entry_multi_discount'] = $this->language->get('entry_multi_discount');
		$this->data['entry_gift_discount'] = $this->language->get('entry_gift_discount');
		$this->data['entry_select_gift'] = $this->language->get('entry_select_gift');
		$this->data['entry_number_gifts'] = $this->language->get('entry_number_gifts');
		$this->data['entry_logged'] = $this->language->get('entry_logged');
		$this->data['entry_shipping'] = $this->language->get('entry_shipping');
		$this->data['entry_type'] = $this->language->get('entry_type');
		$this->data['entry_total'] = $this->language->get('entry_total');
		$this->data['entry_category'] = $this->language->get('entry_category');
		$this->data['entry_product'] = $this->language->get('entry_product');
    	$this->data['entry_date_start'] = $this->language->get('entry_date_start');
    	$this->data['entry_date_end'] = $this->language->get('entry_date_end');
    	$this->data['entry_uses_total'] = $this->language->get('entry_uses_total');
		$this->data['entry_uses_customer'] = $this->language->get('entry_uses_customer');
		$this->data['entry_status'] = $this->language->get('entry_status');
		
		$this->data['entry_gen_count'] = $this->language->get('entry_gen_count');
		$this->data['entry_gen_digits'] = $this->language->get('entry_gen_digits');
		

    	$this->data['button_save'] = $this->language->get('button_save');
    	$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_coupon_history'] = $this->language->get('tab_coupon_history');

		$this->data['token'] = $this->session->data['token'];
	
		if (isset($this->request->get['coupon_id'])) {
			$this->data['coupon_id'] = $this->request->get['coupon_id'];
		} else {
			$this->data['coupon_id'] = 0;
		}
				
 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}
	 	
		if (isset($this->error['name'])) {
			$this->data['error_name'] = $this->error['name'];
		} else {
			$this->data['error_name'] = '';
		}
		
		if (isset($this->error['code'])) {
			$this->data['error_code'] = $this->error['code'];
		} else {
			$this->data['error_code'] = '';
		}		
		
		if (isset($this->error['gen_count'])) {
			$this->data['error_gen_count'] = $this->error['gen_count'];
		} else {
			$this->data['error_gen_count'] = '';
		}		
		if (isset($this->error['gen_digits'])) {
			$this->data['error_gen_digits'] = $this->error['gen_digits'];
		} else {
			$this->data['error_gen_digits'] = '';
		}		
		
		if (isset($this->error['date_start'])) {
			$this->data['error_date_start'] = $this->error['date_start'];
		} else {
			$this->data['error_date_start'] = '';
		}	
		
		if (isset($this->error['date_end'])) {
			$this->data['error_date_end'] = $this->error['date_end'];
		} else {
			$this->data['error_date_end'] = '';
		}	

		$url = '';
			
		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('sale/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL'),
      		'separator' => ' :: '
   		);
									
		if (!isset($this->request->get['coupon_id'])) {
			$this->data['action'] = $this->url->link('sale/coupon/insert', 'token=' . $this->session->data['token'] . $url, 'SSL');
		} else {
			$this->data['action'] = $this->url->link('sale/coupon/update', 'token=' . $this->session->data['token'] . '&coupon_id=' . $this->request->get['coupon_id'] . $url, 'SSL');
		}
		
		$this->data['cancel'] = $this->url->link('sale/coupon', 'token=' . $this->session->data['token'] . $url, 'SSL');
  		
		if (isset($this->request->get['coupon_id']) && (!$this->request->server['REQUEST_METHOD'] != 'POST')) {
      		$coupon_info = $this->model_sale_coupon->getCoupon($this->request->get['coupon_id']);
    	}
    	else {
			if (isset($this->request->post['gen_count'])) {
				$this->data['gen_count'] = $this->request->post['gen_count'];
			} else {
				$this->data['gen_count'] = 1;
			}
			if (isset($this->request->post['gen_digits'])) {
				$this->data['gen_digits'] = $this->request->post['gen_digits'];
			} else {
				$this->data['gen_digits'] = 0;
			}
		}
		
    	if (isset($this->request->post['name'])) {
      		$this->data['name'] = $this->request->post['name'];
    	} elseif (!empty($coupon_info)) {
			$this->data['name'] = $coupon_info['name'];
		} else {
      		$this->data['name'] = '';
    	}
		
    	if (isset($this->request->post['code'])) {
      		$this->data['code'] = $this->request->post['code'];
    	} elseif (!empty($coupon_info)) {
			$this->data['code'] = $coupon_info['code'];
		} else {
      		$this->data['code'] = '';
    	}
		
    	if (isset($this->request->post['type'])) {
      		$this->data['type'] = $this->request->post['type'];
    	} elseif (!empty($coupon_info)) {
			$this->data['type'] = $coupon_info['type'];
		} else {
      		$this->data['type'] = '';
    	}
		
    	if (isset($this->request->post['discount'])) {
      		$this->data['discount'] = $this->request->post['discount'];
    	} elseif (!empty($coupon_info)) {
			$this->data['discount'] = $coupon_info['discount'];
		} else {
      		$this->data['discount'] = 0;
    	}

    	if (isset($this->request->post['number_gifts'])) {
      		$this->data['number_gifts'] = $this->request->post['number_gifts'];
    	} elseif (!empty($coupon_info)) {
			$this->data['number_gifts'] = intval($coupon_info['discount']);
		} else {
      		$this->data['number_gifts'] = 1;
    	}

    	if (isset($this->request->post['discount_b']) && isset($this->request->post['discount_v'])) {
      		$this->data['discount_plan'] = $this->getDiscountPlan($this->request->post);
    	} elseif (!empty($coupon_info)) {
			$this->data['discount_plan'] = $coupon_info['discount_plan'];
		} else {
      		$this->data['discount_plan'] = '';
    	}

		$this->load->model('catalog/product');

		$this->data['product_names'] = array();
		if ($this->data['type'] == 'G' && is_array($this->data['discount_plan'])) {
			$this->data['product_names'] =  $this->model_sale_coupon->getProductNames(array_keys($this->data['discount_plan']));
		}

    	if (isset($this->request->post['logged'])) {
      		$this->data['logged'] = $this->request->post['logged'];
    	} elseif (!empty($coupon_info)) {
			$this->data['logged'] = $coupon_info['logged'];
		} else {
      		$this->data['logged'] = '';
    	}

    	if (isset($this->request->post['shipping'])) {
      		$this->data['shipping'] = $this->request->post['shipping'];
    	} elseif (!empty($coupon_info)) {
			$this->data['shipping'] = $coupon_info['shipping'];
		} else {
      		$this->data['shipping'] = '';
    	}

    	if (isset($this->request->post['total'])) {
      		$this->data['total'] = $this->request->post['total'];
    	} elseif (!empty($coupon_info)) {
			$this->data['total'] = $coupon_info['total'];
		} else {
      		$this->data['total'] = '';
    	}
		
		if (isset($this->request->post['coupon_product'])) {
			$products = $this->request->post['coupon_product'];
		} elseif (isset($this->request->get['coupon_id'])) {		
			$products = $this->model_sale_coupon->getCouponProducts($this->request->get['coupon_id']);
		} else {
			$products = array();
		}
		
		$this->data['coupon_product'] = array();
		$product_infos = $this->model_sale_coupon->getProductNames($products);
		foreach ($products as $product_id) {
			if (isset($product_infos[$product_id])) {
				$this->data['coupon_product'][] = array(
					'product_id' => $product_id,
					'name'       => $product_infos[$product_id]
				);
			}
		}

		$this->load->model('catalog/category');
				
		$this->data['categories'] = $this->model_catalog_category->getCategories(0);
					
		if (isset($this->request->post['date_start'])) {
       		$this->data['date_start'] = $this->request->post['date_start'];
		} elseif (!empty($coupon_info)) {
			$this->data['date_start'] = date('Y-m-d', strtotime($coupon_info['date_start']));
		} else {
			$this->data['date_start'] = date('Y-m-d', time());
		}

		if (isset($this->request->post['date_end'])) {
       		$this->data['date_end'] = $this->request->post['date_end'];
		} elseif (!empty($coupon_info)) {
			$this->data['date_end'] = date('Y-m-d', strtotime($coupon_info['date_end']));
		} else {
			$this->data['date_end'] = date('Y-m-d', strtotime('+1year'));
		}

    	if (isset($this->request->post['uses_total'])) {
      		$this->data['uses_total'] = $this->request->post['uses_total'];
		} elseif (!empty($coupon_info)) {
			$this->data['uses_total'] = $coupon_info['uses_total'];
    	} else {
      		$this->data['uses_total'] = 1;
    	}
  
    	if (isset($this->request->post['uses_customer'])) {
      		$this->data['uses_customer'] = $this->request->post['uses_customer'];
    	} elseif (!empty($coupon_info)) {
			$this->data['uses_customer'] = $coupon_info['uses_customer'];
		} else {
      		$this->data['uses_customer'] = 1;
    	}
 
    	if (isset($this->request->post['status'])) { 
      		$this->data['status'] = $this->request->post['status'];
    	} elseif (!empty($coupon_info)) {
			$this->data['status'] = $coupon_info['status'];
		} else {
      		$this->data['status'] = 1;
    	}
		
		$this->template = 'sale/coupon_form.tpl';
		$this->children = array(
			'common/header',	
			'common/footer'	
		);
		
		$this->response->setOutput($this->render());		
  	}
	
  	private function validateForm() {
    	if (!$this->user->hasPermission('modify', 'sale/coupon')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}
      	
		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 128)) {
        	$this->error['name'] = $this->language->get('error_name');
      	}
			
		if (isset($this->request->get['coupon_id'])) {
			if ((utf8_strlen($this->request->post['code']) < 3)) {
				$this->error['code'] = $this->language->get('error_code');
			}
		}
		else {
			if (!intval($this->request->post['gen_count'])) {
				$this->error['gen_count'] = $this->language->get('error_gen_count');
			}
			else if (intval($this->request->post['gen_digits']) < 2*ceil(log10(intval($this->request->post['gen_count'])))) {
				$this->error['gen_digits'] = $this->language->get('error_gen_digits');
			}
		}
    	
		$coupon_info = $this->model_sale_coupon->getCouponByCode($this->request->post['code']);
		
		if ($coupon_info) {
			if (!isset($this->request->get['coupon_id'])) {
				$this->error['warning'] = $this->language->get('error_exists');
			} elseif ($coupon_info['coupon_id'] != $this->request->get['coupon_id'])  {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		}
	
    	if (!$this->error) {
      		return true;
    	} else {
      		return false;
    	}
  	}

  	private function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'sale/coupon')) {
      		$this->error['warning'] = $this->language->get('error_permission');  
    	}
	  	
		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}	
	
	public function history() {
    	$this->language->load('sale/coupon');
		
		$this->load->model('sale/coupon');
				
		$this->data['text_no_results'] = $this->language->get('text_no_results');
		
		$this->data['column_order_id'] = $this->language->get('column_order_id');
		$this->data['column_order_status'] = $this->language->get('column_status');
		$this->data['column_order_total'] = $this->language->get('column_order_total');
		$this->data['column_customer'] = $this->language->get('column_customer');
		$this->data['column_amount'] = $this->language->get('column_amount');
		$this->data['column_date_added'] = $this->language->get('column_date_added');

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}  
		
		$this->data['histories'] = array();
			
		$results = $this->model_sale_coupon->getCouponHistories($this->request->get['coupon_id'], ($page - 1) * 10, 10);
      		
		foreach ($results as $result) {
        	$this->data['histories'][] = array(
				'order_id'   => $result['order_id'],
				'order_url'   => $this->url->link('sale/order/info', 'token=' . $this->session->data['token'] . '&order_id=' . $result['order_id'], 'SSL'),
				'order_total' => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'order_status' => $result['status'],
				'customer'   => $result['customer'],
				'amount'     => $result['amount'],
        		'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
        	);
      	}			
		
		$history_total = $this->model_sale_coupon->getTotalCouponHistories($this->request->get['coupon_id']);
			
		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10; 
		$pagination->url = $this->url->link('sale/coupon/history', 'token=' . $this->session->data['token'] . '&coupon_id=' . $this->request->get['coupon_id'] . '&page={page}', 'SSL');
			
		$this->data['pagination'] = $pagination->render();
		
		$this->template = 'sale/coupon_history.tpl';		
		
		$this->response->setOutput($this->render());
  	}		
}
?>
