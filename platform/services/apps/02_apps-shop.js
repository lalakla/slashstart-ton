



SlstApp.Shop = {};

SlstApp.Shop.init = function()
{
	SlstApp.init();

	SlstApp.Shop.lastH = 0;

	if (SlstApp._request.tgWebAppData && window.Telegram)
	{
		window.Telegram.WebApp.onEvent('viewportChanged', function(){

			if (window.Telegram.WebApp.viewportHeight != window.Telegram.WebApp.viewportStableHeight)
				$('.shop-footer-cart:not(.cart-is-empty)').hide();

			if (SlstApp.Shop.lastH != window.Telegram.WebApp.viewportStableHeight)
			{
				if (!window.Telegram.WebApp.isExpanded)
				{
					$('.shop-footer-cart:not(.cart-is-empty)').css({'bottom' : 'initial', 'top' : (window.Telegram.WebApp.viewportStableHeight - 50) + 'px'}).fadeIn();
				}
				else
				{
					$('.shop-footer-cart:not(.cart-is-empty)').css({'top' : 'initial', 'bottom' : '0px'}).fadeIn();
				}
			}

			SlstApp.Shop.lastH = window.Telegram.WebApp.viewportStableHeight;

		});
	}

	SlstApp.Cart.onsave = function(){

	    	var count = this.getCount();
			var price = this.getPrice();

			if (count)
			{
				$('.cart-isempty').hide();
				$('.cart-isnotempty').show().removeClass('cart-is-empty');
			}
			else
			{
				$('.cart-isempty').show();
				$('.cart-isnotempty').hide().addClass('cart-is-empty');
			}

			$('.product-cart-count-badge').hide();

			for ( i in this.elements )
			{
				$('.product-cart-count-badge-' + this.elements[i].id).text( this.elements[i].qty ).show();

				$('.product-variant-' + this.elements[i].id).trigger('change');
			}


			$('.cart-count-bdage').html(count);


			if ($('#cart-use-cashback-value').length)
			{
				var cb = $('#cart-use-cashback-value');
				
				var cashback = parseInt(cb.val());
				
				var balance = parseInt(cb.attr('data-balance'));
				
				var max = parseInt(cb.attr('data-max'));
				if (!max) max = 100;

				max = (price * max / 100).toFixed(0); 
				if (max > balance)
					max = balance;

				$('.cart-cashback-max-badge').html( max );

				if (cashback > max) 
					cb.val( max );
			}


			$('.cart-price-badge').html( SlstApp.formatPrice(price) );



			var f = $('.app-cart-form');
			if (f.attr('data-minorder'))
			{
				var m = parseInt( f.attr('data-minorder') );
				if (m > 0 && price < m)
				{
					$('.cart-order').hide();
					$('.cart-order-min-notice').show();
				}
				else
				{
					$('.cart-order').show();
					$('.cart-order-min-notice').hide();
				}
			}

			if (f.attr('data-cblevels') && $('.cblevels-alert').length)
			{
				var cbl = JSON.parse( f.attr('data-cblevels') );
				
				var bonus = [];
				if (cbl.length)
				{
					for (var i = 0; i < cbl.length; i++)
					{
						if (parseInt(cbl[i].price) > price)
						{
							bonus = cbl[i];
							break;
						}
					}
				}

				

				var l = $('.cblevels-alert');
				if (bonus.price)
				{	
					var t = l.attr('data-label');

					var s = parseInt(bonus.price) - price;

					var b = parseInt(bonus.bonus);

					if (bonus.is_pcnt)
					{
						b = (price * b / 100).toFixed(0);
					}

					t = t.replace('{s}', s).replace('{b}', b);

					l.html( t ).show();

				}
				else
				{
					l.hide();
				}

			}




	};


	SlstApp.Cart.init();
	SlstApp.Cart.onsave();

	SlstApp.onLoadPage.push(function(method, query, config){

		$('.product-variantes .form-check:first-child input').trigger('change');
		// $('.cart-count-changer').trigger('click');



		SlstApp.Cart.init();
		SlstApp.Cart.onsave();

		$('.app-main-content').attr('class', 'app-main-content ' + method);


		$('body').removeClass('shop-page-cart');
		if (method == 'cart' || method == 'checkout')
		{
			$('body').addClass('shop-page-cart');
		}



	});



	// SlstApp.query('index', {a : 'b', c : 'd'});
};


jQuery(document).ready(function($){



	$(document).on('keyup', '#cart-use-cashback-value', function(){
		SlstApp.Cart.onsave();
	});

	$(document).on('change', '#cart-use-cashback-trigger', function(){

		var t = $(this);

		if (t.prop('checked'))
			$('#cart-use-cashback-value-wrr').show();
		else
			$('#cart-use-cashback-value-wrr').hide();

	});

	


	$(document).on('click', '.gallery-preview', function(){

		var t = $(this);



		$('.product-photo-image').css('background-image', 'url(' + t.attr('data-src') + ')');

	});


	$(document).on('change', '.product-variant', function(){

		var t = $(this);



		if (t.prop("tagName") == 'INPUT')
		{
			var c = t.parents('.product-variantes');

			c.find('.product-variant').prop('checked', false);

			t.prop('checked', true);

			$( c.attr('data-target') ).attr('data-product-price', t.attr('data-price')).attr('data-product-id',  'v' + t.attr('data-id'));
			$( c.attr('data-target-price') ).text( SlstApp.formatPrice(t.attr('data-price')) );
		}

	});



	$(document).on('keyup', '.cart-pr-count', function(){
		var t = $(this);

		var c = t.parents('.cart-count-change');

		var v = c.find('.cart-count-product');

		var q = parseInt(t.val());

		if (!q || q < 1)
			q = 1;

		SlstApp.Cart.update(v.attr('data-id'), q, v.attr('data-price'));

		v.attr('data-count', q);

		t.val( q );

		$('.cart-product-total-' + v.attr('data-id')).text( SlstApp.formatPrice(q * v.attr('data-price')) );
	});


	$(document).on('click', '.cart-count-changer', function(){
		var t = $(this);

		var c = t.parents('.cart-count-change');

		var v = c.find('.cart-count-product');

		var q = parseInt(v.attr('data-count'));

		var a = t.attr('data-action');

		if (a == 'p')
		{
			q += 1;
		}
		else if (a == 'm')
		{
			if (q > 1)
				q -= 1;
			else
				q = 1;
		}

		SlstApp.Cart.update(v.attr('data-id'), q, v.attr('data-price'));

		v.attr('data-count', q);

		if (v.find('input'))
			v.find('input').val(q);
		else
			v.text( q );

		$('.cart-product-total-' + v.attr('data-id')).text( SlstApp.formatPrice(q * v.attr('data-price')) );

	});



	$(document).on('click', '.cart-product-delete', function(){
		let _ios = !!navigator.platform.match(/iPhone|iPod|iPad/);

		if (!_ios)
		{
			try {
				if (!confirm($(this).attr('data-confirm')))
					return false;
			} catch (e) {};
		}

		$(this).parents('.cart-product').remove();

		SlstApp.Cart.del( $(this).attr('data-id') );

	});


	$(document).on('click', '.app-shop-tocart', function(){
		if (SlstAppConfig.cartImCheckout)
		{
			SlstApp.query('checkout');
		}
		else
		{
			SlstApp.query('cart');
		}

	});

	$(document).on('click', '.cart-add', function(){


			var t = $(this);
			var cnt = 1;


			var i = t.attr('data-product-id');


			if (SlstAppConfig.cartDisable)
			{
				SlstApp.Cart.clear();
			}

	        if (t.attr('data-count'))
	        {
				cnt = parseInt($(t.attr('data-count')).attr('value'));
				if (!cnt || cnt < 0) cnt = 1;
				$(t.attr('data-count')).attr('value', cnt);
			}

			var id = SlstApp.checkId(i);
			var pp = parseFloat( t.attr('data-product-price') ? t.attr('data-product-price') : t.attr('productprice') );


			SlstApp.Cart.add(id, cnt, pp);
			var count = SlstApp.Cart.getCount();
			var price = SlstApp.Cart.getPrice();


			if (count)
			{
				if (t.attr('data-show')) $(t.attr('data-show')).show();
				if (t.attr('data-hide')) $(t.attr('data-hide')).hide();


				var l = SlstApp.serverUrl + '&method=add2cart&' + Math.random();
				$.post(l, {'id' : id, 'cnt': cnt, 'pp' : pp}, function(data) {}, 'json');



			}
			else
			{

			}


			if (SlstAppConfig.cartDisable)
			{
				if (SlstAppConfig.cartImCheckout)
				{
					SlstApp.query('checkout');
				}
				else
				{
					SlstApp.query('cart');
				}

			}

			return false;
		});




});


SlstApp.Shop.init();

window.SlstAppShopOnOrder = function()
{
	SlstApp.Cart.clear();	
}

