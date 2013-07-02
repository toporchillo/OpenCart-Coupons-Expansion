<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/customer.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons"><a onclick="$('#form').submit();" class="button"><?php echo $button_save; ?></a><a onclick="location = '<?php echo $cancel; ?>';" class="button"><?php echo $button_cancel; ?></a></div>
    </div>
    <div class="content">
      <div id="tabs" class="htabs"><a href="#tab-general"><?php echo $tab_general; ?></a>
        <?php if ($coupon_id) { ?>
        <a href="#tab-history"><?php echo $tab_coupon_history; ?></a>
        <?php } ?>
      </div>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">
        <div id="tab-general">
          <table class="form">
            <tr>
              <td><span class="required">*</span> <?php echo $entry_name; ?></td>
              <td><input name="name" value="<?php echo $name; ?>" />
                <?php if ($error_name) { ?>
                <span class="error"><?php echo $error_name; ?></span>
                <?php } ?></td>
            </tr>
            <tr>
              <td><?php if ($coupon_id) { ?><span class="required">*</span> <?php } ?><?php echo $entry_code; ?></td>
              <td><input type="text" name="code" value="<?php echo $code; ?>" />
                <?php if ($error_code) { ?>
                <span class="error"><?php echo $error_code; ?></span>
                <?php } ?></td>
            </tr>
		<?php if (!$coupon_id) { ?>
            <tr>
              <td><?php echo $entry_gen_count; ?></td>
              <td><input type="text" name="gen_count" value="<?php echo $gen_count; ?>" />
                <?php if ($error_gen_count) { ?>
                <span class="error"><?php echo $error_gen_count; ?></span>
				<?php } ?>
              </td>
            </tr>
            <tr>
              <td><?php echo $entry_gen_digits; ?></td>
              <td><input type="text" name="gen_digits" value="<?php echo $gen_digits; ?>" />
                <?php if ($error_gen_digits) { ?>
                <span class="error"><?php echo $error_gen_digits; ?></span>
				<?php } ?>
              </td>
            </tr>
		<?php } ?>
            <tr>
              <td><?php echo $entry_type; ?></td>
              <td><select name="type" id="discount_type">
                  <?php if ($type == 'P') { ?>
                  <option value="P" selected="selected"><?php echo $text_percent; ?></option>
                  <?php } else { ?>
                  <option value="P"><?php echo $text_percent; ?></option>
                  <?php } ?>
                  <?php if ($type == 'F') { ?>
                  <option value="F" selected="selected"><?php echo $text_amount; ?></option>
                  <?php } else { ?>
                  <option value="F"><?php echo $text_amount; ?></option>
                  <?php } ?>
                  <?php if ($type == 'C') { ?>
                  <option value="C" selected="selected"><?php echo $text_cumulative; ?></option>
                  <?php } else { ?>
                  <option value="C"><?php echo $text_cumulative; ?></option>
                  <?php } ?>
                  <?php if ($type == 'G') { ?>
                  <option value="G" selected="selected"><?php echo $text_gift; ?></option>
                  <?php } else { ?>
                  <option value="G"><?php echo $text_gift; ?></option>
                  <?php } ?>
                </select></td>
            </tr>
            <tr class="non_gift_discount">
              <td><?php echo $entry_discount; ?></td>
              <td><input type="text" name="discount" value="<?php echo $discount; ?>" /></td>
            </tr>
            <tr class="multi_discount" style="display: none;">
              <td><?php echo $entry_multi_discount; ?></td>
              <td id="multi_discount_td">
				<div id="multi_discount_clone" style="display: none;">
					<input type="text" name="discount_b[]" value="" /> -
					<input type="text" name="discount_v[]" value="" />
					<a class="remove-item"><img src="view/image/delete.png" /></a>
				</div>
                <?php if ($type == 'C' && is_array($discount_plan)) {
						$i = 0;
						foreach($discount_plan as $b=>$v) {
							$i++;
				?>
				<div>
					<input type="text" name="discount_b[]" value="<?php echo $b; ?>" <?php if ($i==1) { ?>readonly="readonly" <?php } ?>/> -
					<input type="text" name="discount_v[]" value="<?php echo $v; ?>" size="5" />
					<?php if ($i>0) { ?><a class="remove-item"><img src="view/image/delete.png" /></a><?php } ?>
				</div>
                <?php } ?>
                <?php } else { ?>
				<div>
					<input type="text" name="discount_b[]" value="0" readonly="readonly" /> -
					<input type="text" name="discount_v[]" value="0" size="5" />
				</div>
                <?php } ?>
				<a class="add-item"><img src="view/image/add.png"></a>
			  </td>
            </tr>
			<!-- ++++ Gift discount ++++ -->
            <tr class="gift_discount" style="display: none;">
              <td><?php echo $entry_gift_discount; ?></td>
              <td id="gift_discount_td">
				<div id="gift_discount_clone" style="display: none;">
					<span></span>
					<input type="hidden" name="discount_g[]" value="" />
					<a class="remove-item"><img src="view/image/delete.png" /></a>
					<input type="text" name="discount_p[]" style="float: right;" value="100" size="5" />
				</div>
				<div id="product_gifts" class="scrollbox" style="width: 400px; height: auto; overflow-y: auto; padding-bottom: 3px;">
                <?php if ($type == 'G' && is_array($discount_plan)) {
						$i = 0;
						foreach($discount_plan as $b=>$v) {
							$i++;
				?>
				<div>
					<span><?php echo $product_names[$b]; ?></span>
					<input type="hidden" name="discount_g[]" value="<?php echo $b; ?>" />
					<a class="remove-item"><img src="view/image/delete.png" /></a>
					<input type="text" name="discount_p[]" style="float: right;" value="<?php echo $v; ?>" size="5" />
				</div>
	            <?php 	} ?>
                <?php } ?>
				</div>
				<?php echo $entry_select_gift; ?><input type="text" name="product" value="" />
			  </td>
            </tr>
            <tr class="gift_discount" style="display: none;">
              <td><?php echo $entry_number_gifts; ?></td>
              <td><input type="text" name="number_gifts" value="<?php echo $number_gifts; ?>" size="5" /></td>
            </tr>
			<!-- ---- Gift discount ---- -->
            <tr>
              <td><?php echo $entry_total; ?></td>
              <td><input type="text" name="total" value="<?php echo $total; ?>" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_logged; ?></td>
              <td><?php if ($logged) { ?>
                <input type="radio" name="logged" value="1" checked="checked" />
                <?php echo $text_yes; ?>
                <input type="radio" name="logged" value="0" />
                <?php echo $text_no; ?>
                <?php } else { ?>
                <input type="radio" name="logged" value="1" />
                <?php echo $text_yes; ?>
                <input type="radio" name="logged" value="0" checked="checked" />
                <?php echo $text_no; ?>
                <?php } ?></td>
            </tr>
            <tr>
              <td><?php echo $entry_shipping; ?></td>
              <td><?php if ($shipping) { ?>
                <input type="radio" name="shipping" value="1" checked="checked" />
                <?php echo $text_yes; ?>
                <input type="radio" name="shipping" value="0" />
                <?php echo $text_no; ?>
                <?php } else { ?>
                <input type="radio" name="shipping" value="1" />
                <?php echo $text_yes; ?>
                <input type="radio" name="shipping" value="0" checked="checked" />
                <?php echo $text_no; ?>
                <?php } ?></td>
            </tr>
            <tr>
              <td><?php echo $entry_category; ?></td>
              <td><div class="scrollbox" style="width: 400px; height: 250px;">
                  <?php $class = 'odd'; ?>
                  <?php foreach ($categories as $category) { ?>
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div class="<?php echo $class; ?>">
                    <input type="checkbox" name="category[]" value="<?php echo $category['category_id']; ?>" />
                    <?php echo $category['name']; ?> </div>
                  <?php } ?>
                </div></td>
            </tr>
            <tr>
              <td><?php echo $entry_product; ?></td>
              <td id="product_adder_td"><input type="text" name="product" value="" /></td>
            </tr>
            <tr>
              <td>&nbsp;</td>
              <td><div id="coupon-product" class="scrollbox" style="width: 400px; height: 250px;">
                  <?php $class = 'odd'; ?>
                  <?php foreach ($coupon_product as $coupon_product) { ?>
                  <?php $class = ($class == 'even' ? 'odd' : 'even'); ?>
                  <div id="coupon-product<?php echo $coupon_product['product_id']; ?>" class="<?php echo $class; ?>"> <?php echo $coupon_product['name']; ?><img src="view/image/delete.png" />
                    <input type="hidden" name="coupon_product[]" value="<?php echo $coupon_product['product_id']; ?>" />
                  </div>
                  <?php } ?>
                </div></td>
            </tr>
            <tr>
              <td><?php echo $entry_date_start; ?></td>
              <td><input type="text" name="date_start" value="<?php echo $date_start; ?>" size="12" id="date-start" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_date_end; ?></td>
              <td><input type="text" name="date_end" value="<?php echo $date_end; ?>" size="12" id="date-end" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_uses_total; ?></td>
              <td><input type="text" name="uses_total" value="<?php echo $uses_total; ?>" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_uses_customer; ?></td>
              <td><input type="text" name="uses_customer" value="<?php echo $uses_customer; ?>" /></td>
            </tr>
            <tr>
              <td><?php echo $entry_status; ?></td>
              <td><select name="status">
                  <?php if ($status) { ?>
                  <option value="1" selected="selected"><?php echo $text_enabled; ?></option>
                  <option value="0"><?php echo $text_disabled; ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled; ?></option>
                  <option value="0" selected="selected"><?php echo $text_disabled; ?></option>
                  <?php } ?>
                </select></td>
            </tr>
          </table>
        </div>
        <?php if ($coupon_id) { ?>
        <div id="tab-history">
          <div id="history"></div>
        </div>
        <?php } ?>
      </form>
    </div>
  </div>
</div>
<script type="text/javascript"><!--
$(document).ready(function() {
	var toggleType = function toggleType() {
		$('.gift_discount').hide();
		$('.multi_discount').hide();
		$('.non_gift_discount').show();
		if ($('#discount_type').val() == 'C') {
			$('.multi_discount').show();
		}
		else if ($('#discount_type').val() == 'G') {
			$('.gift_discount').show();
			$('.non_gift_discount').hide();
		}
	}
	$('#discount_type').change(toggleType);
	toggleType();

	$('#multi_discount_td .add-item').click(function(e) {
		var clone = $('#multi_discount_clone').clone();
		clone.find('.remove-item').click(function(e) {
			$(this).parent('div').remove();
			return false;
		})
		clone.show();
		clone.removeAttr('id');
		$(this).before(clone);
		return false;
	})
	$('#gif_discount_td .add-item').click(function(e) {
		var clone = $('#multi_discount_clone').clone();
		clone.find('.remove-item').click(function(e) {
			$(this).parent('div').remove();
			return false;
		})
		clone.show();
		clone.removeAttr('id');
		$(this).before(clone);
		return false;
	})
	$('#multi_discount_td .remove-item, #gift_discount_td .remove-item').click(function(e) {
		$(this).parent('div').remove();
		return false;
	})
});

$('input[name=\'category[]\']').bind('change', function() {
	var filter_category_id = this;
	
	$.ajax({
		url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_category_id=' +  filter_category_id.value + '&limit=10000',
		dataType: 'json',
		success: function(json) {
			for (i = 0; i < json.length; i++) {
				if ($(filter_category_id).attr('checked') == 'checked') {
					$('#coupon-product' + json[i]['product_id']).remove();
					
					$('#coupon-product').append('<div id="coupon-product' + json[i]['product_id'] + '">' + json[i]['name'] + '<img src="view/image/delete.png" /><input type="hidden" name="coupon_product[]" value="' + json[i]['product_id'] + '" /></div>');
				} else {
					$('#coupon-product' + json[i]['product_id']).remove();
				}			
			}
			$('#coupon-product div:odd').attr('class', 'odd');
			$('#coupon-product div:even').attr('class', 'even');			
		}
	});
});

$('#gift_discount_td input[name=\'product\']').autocomplete({
	delay: 0,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {		
				response($.map(json, function(item) {
					return {
						label: item.name,
						value: item.product_id
					}
				}));
			}
		});
	}, 
	select: function(event, ui) {
		var clone = $('#gift_discount_clone').clone();
		clone.find('.remove-item').click(function(e) {
			$(this).parent('div').remove();
			return false;
		})
		clone.show();
		clone.removeAttr('id');
		clone.find('span').html(ui.item.label);
		clone.find('input[name=\'discount_g[]\']').val(ui.item.value);
		$('#product_gifts').append(clone);
		return false;
	},
	focus: function(event, ui) {
      	return false;
   	}
});

$('#product_adder_td input[name=\'product\']').autocomplete({
	delay: 0,
	source: function(request, response) {
		$.ajax({
			url: 'index.php?route=catalog/product/autocomplete&token=<?php echo $token; ?>&filter_name=' +  encodeURIComponent(request.term),
			dataType: 'json',
			success: function(json) {		
				response($.map(json, function(item) {
					return {
						label: item.name,
						value: item.product_id
					}
				}));
			}
		});
	}, 
	select: function(event, ui) {
		$('#coupon-product' + ui.item.value).remove();
		
		$('#coupon-product').append('<div id="coupon-product' + ui.item.value + '">' + ui.item.label + '<img src="view/image/delete.png" /><input type="hidden" name="coupon_product[]" value="' + ui.item.value + '" /></div>');

		$('#coupon-product div:odd').attr('class', 'odd');
		$('#coupon-product div:even').attr('class', 'even');
		
		$('input[name=\'product\']').val('');
		
		return false;
	},
	focus: function(event, ui) {
      	return false;
   	}
});

$('#coupon-product div img').live('click', function() {
	$(this).parent().remove();
	
	$('#coupon-product div:odd').attr('class', 'odd');
	$('#coupon-product div:even').attr('class', 'even');	
});
//--></script> 
<script type="text/javascript"><!--
$('#date-start').datepicker({dateFormat: 'yy-mm-dd'});
$('#date-end').datepicker({dateFormat: 'yy-mm-dd'});
//--></script>
<?php if ($coupon_id) { ?>
<script type="text/javascript"><!--
$('#history .pagination a').live('click', function() {
	$('#history').load(this.href);
	
	return false;
});			

$('#history').load('index.php?route=sale/coupon/history&token=<?php echo $token; ?>&coupon_id=<?php echo $coupon_id; ?>');
//--></script>
<?php } ?>
<script type="text/javascript"><!--
$('#tabs a').tabs(); 
//--></script> 
<?php echo $footer; ?>
