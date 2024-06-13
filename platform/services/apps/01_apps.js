
var SlstApp = SlstApp || {};

SlstApp.AppConfig = SlstApp.AppConfig || (SlstAppConfig ?? {});

SlstApp.AppConfig.Currency = !SlstApp.AppConfig || !SlstApp.AppConfig.Currency ? {} : SlstApp.AppConfig.Currency;


SlstApp.init = function()
{
	SlstApp._get = SlstApp.getGetParams();
	SlstApp._hash = SlstApp.getHashParams();
	SlstApp._request = SlstApp.getRequestParams();
	SlstApp._tgRequest = {};

	if (SlstApp._request.tgWebAppData && SlstApp._request.tgWebAppData.tgWebAppData)
	{
		SlstApp._tgRequest = SlstApp.getGetParams('?' + SlstApp._request.tgWebAppData.tgWebAppData);

		window.Telegram.WebApp.ready();


	}


	var l = top.location.href;
	l = l.indexOf('#') > 0 ? l.split('#')[0] : l;
	// l = l.indexOf('?') > 0 ? l.split('?')[0] : l;

	SlstApp.serverUrl = l;





}




SlstApp.p = function (obj)
{
	alert( JSON.stringify(obj, null, 2) );
}


SlstApp.getRequestParams = function(q)
{
	var q = q ?? window.location.href;

	var a = SlstApp.getGetParams();
	var b = SlstApp.getHashParams();

	for (var i in b)
		a[i] = b;

	return a;
}

SlstApp.getGetParams = function(q)
{
	var q = q ?? window.location.href;

	var r = {};

	if (q.indexOf('?') < 0)
		return r;

	var p = q.split("?")[1].split("&");

	for (i in p)
	{
		var kv = p[i].split("=");

		r[kv[0]] = decodeURIComponent(kv[1]);
	}

	return r;
}

SlstApp.getHashParams = function(q)
{
	var q = q ?? window.location.href;

	var r = {};

	if (q.indexOf('#') < 0)
		return r;

	var p = q.split("#")[1].split("&");

	for (i in p)
	{
		var kv = p[i].split("=");

		r[kv[0]] = decodeURIComponent(kv[1]);
	}

	return r;

}


SlstApp.isMobile = function()
{
	let check = false;
	(function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
	return check;
}



SlstApp.getUtms = function(q)
{
	var q = q ?? window.location.href;
	var r = {};

	var p = getGetParams(q);

	for (i in p)
	{
		if (i.indexOf('utm_') !== 0)
			continue;

		r[i] = p[i];
	}

	return r;
}


SlstApp.isEmail = function(check)
{
   var pattern =/^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
   return pattern.test(check);
}


SlstApp.timeOffset = function()
{
    var offset = new Date().getTimezoneOffset(), o = Math.abs(offset);
    return (offset < 0 ? "+" : "-") + ("00" + Math.floor(o / 60)).slice(-2) + ":" + ("00" + (o % 60)).slice(-2);
}



SlstApp.pagesCache = {};
SlstApp.onLoadPage = [];

SlstApp.afterQuery = function(method, query, config)
{
	$('.navbar-toggler:not(.collapsed)').trigger('click');

	if (SlstApp.onLoadPage)
	{
		for (var i in SlstApp.onLoadPage)
			SlstApp.onLoadPage[i](method, query, config);
	}

	$(document).scrollTop(0);

	$('.loader').hide();

}


SlstApp.query = function (method, query, config)
{

	// Show loader

	$('.loader').show();


	if (config && config.cacheKey)
	{
		if (SlstApp.pagesCache[config.cacheKey])
		{
			$(SlstApp.pagesCache[config.cacheKey].update.selector).html(SlstApp.pagesCache[config.cacheKey].update.content);

			SlstApp.afterQuery(method, query, config);

			return true;
		}
	}

	var l = SlstApp.serverUrl + '&method=' + method + '&' + Math.random();


	$.post(l, query, function(data) {

	    	if (data.status != 'error')
	    	{

	    	}

	    	if (data.update)
	    	{
	    		$(data.update.selector).html(data.update.content);

	    		if (data.update.class)
	    			$(data.update.selector).attr('class', data.update.class);

	    		if (config && config.cacheKey)
	    		{
	    			SlstApp.pagesCache[config.cacheKey] = data;
	    		}
	    	}

	    	if (data.refresh)
	    	{
	    		if (document.location.href.indexOf('#') > 0)
	    		{

	    		}
	    		else
		    	{
		    		top.location.href = document.location.href;
		    	}
	    	}

	    	if (config && config.callback)
	    	{
	    		var f = window[config.callback];
	    		f( data );
	    	}

	    	if (data.redirect)
	    	{
	    		top.location.href = data.redirect;
	    	}



	   	},
	   	'json'
	).always(function() {

	    // Hide loader


	    SlstApp.afterQuery(method, query, config);


	});

};



SlstApp.checkId = function(id)
{
    	var regexp = /^[0-9vmc]+$/i;

    	if (regexp.test(id))
    		return id;

    	return 0;
};

/**
	 * Create a cookie with the given name and value and other optional parameters.
	 * @name $.cookie
	 * @cat Plugins/Cookie
	 * @author Klaus Hartl/klaus.hartl@stilbuero.de
	 * Dual licensed under the MIT and GPL licenses:
	 */

SlstApp.Cookie = function(name, value, options) {
	if (typeof value != 'undefined') { // name and value given, set cookie
	    options = options || {};
	    if (value === null) {
	        value = '';
	        options.expires = -1;
	    }
	    var expires = '';
	    if (options.expires && (typeof options.expires == 'number' || options.expires.toUTCString)) {
	        var date;
	        if (typeof options.expires == 'number') {
	            date = new Date();
	            date.setTime(date.getTime() + (options.expires * 24 * 60 * 60 * 1000));
	        } else {
	            date = options.expires;
	        }
	        expires = '; expires=' + date.toUTCString(); // use expires attribute, max-age is not supported by IE
	    }
	    // CAUTION: Needed to parenthesize options.path and options.domain
	    // in the following expressions, otherwise they evaluate to undefined
	    // in the packed version for some reason...
	    var path = options.path ? '; path=' + (options.path) : '';
	    var domain = options.domain ? '; domain=' + (options.domain) : '';
	    var secure = options.secure ? '; secure' : '';
	    document.cookie = [name, '=', encodeURIComponent(value), expires, path, domain, secure].join('');
	} else { // only name given, get cookie
	    var cookieValue = null;
	    if (document.cookie && document.cookie != '') {
	        var cookies = document.cookie.split(';');
	        for (var i = 0; i < cookies.length; i++) {
	            var cookie = cookies[i].replace(/^\s*|\s*$/g, '');
	            // Does this cookie string begin with the name we want?
	            if (cookie.substring(0, name.length + 1) == (name + '=')) {
	                cookieValue = decodeURIComponent(cookie.substring(name.length + 1));
	                break;
	            }
	        }
	    }
	    return cookieValue;
	}
};


SlstApp.nf = function  (number, decimals, dec_point, thousands_sep) {

    	function rtrim (str, charlist) {
		    // Removes trailing whitespace
		    //
		    // version: 1103.1210
		    // discuss at: http://phpjs.org/functions/rtrim
		    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		    charlist = !charlist ? ' \\s\u00A0' : (charlist + '').replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '\\$1');
		    var re = new RegExp('[' + charlist + ']+$', 'g');    return (str + '').replace(re, '');
		};

	    // Formats a number with grouped thousands
	    //
	    // version: 1103.1210
	    // discuss at: http://phpjs.org/functions/number_format
	    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
	    // Strip all characters but numerical ones.
	    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
	    var n = !isFinite(+number) ? 0 : +number,
	        prec = !isFinite(+decimals) ? 2 : Math.abs(decimals),
	        sep = (typeof thousands_sep === 'undefined') ? '' : thousands_sep,
	        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
	        s = '',
	        toFixedFix = function (n, prec) {
	            var k = Math.pow(10, prec);            return '' + Math.round(n * k) / k;
	        };


	    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
	    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
	    if (s[0].length > 3) {        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
	    }
	    if ((s[1] || '').length < prec) {
	        s[1] = s[1] || '';
	        s[1] += new Array(prec - s[1].length + 1).join('0');    }

	    val = s.join(dec);

	    if ((s[1] || '').length < prec)
	    {
		    val = rtrim(val, '0');
		    val = rtrim(val, '.');
	    }

	    return val;
	};


SlstApp.formatPrice = function(val)
{

	val = SlstApp.nf(val, SlstApp.AppConfig.Currency.decimals ?? 0, SlstApp.AppConfig.Currency.dsep ?? '.', SlstApp.AppConfig.Currency.tsep ?? ',');

	return val;

}



SlstApp.Cart = {

		cookieName : '_slst_cart',

		count : 0,
		price : 0,


		elements : [], // id, price, count

		save : function() {

			this.count = 0;
			this.price = 0;

			if (!this.elements) return;

			cookie = new Array();

			for ( i in this.elements )
			{
				if (this.elements[i].qty)
				{
					this.count += this.elements[i].qty;
					this.price += this.elements[i].price * this.elements[i].qty;

					cookie[i] = this.elements[i].id + ':' + this.elements[i].qty + ':' + this.elements[i].price;
				}
			};

			SlstApp.Cookie( SlstApp.Cart.cookieName, cookie.join('|'), { expires: 7, path: '/'} );

			if (typeof(this.onsave) == 'function')
				this.onsave();

		},




		init : function(elements)
		{
			if (SlstAppConfig.cartCookieName)
				SlstApp.Cart.cookieName = SlstAppConfig.cartCookieName;

			if (!elements)
				elements = SlstApp.Cookie( SlstApp.Cart.cookieName );




			if (!elements) return;

			l = 'Incorrect elements [{id: 1, price:2, qty: 3}, {id: 2, price:3, qty: 4}] format: ';

			if ( typeof(elements) == 'object' )
			{
				for ( i in elements )
				{
					with (elements[i])
					{
						if (id)
						{
							this.elements[i] = elements[i];

							if ( typeof(id) == 'undefined' ) throw l + 'id is empty for ' + i + ' element';

	                        this.elements[i].id = SlstApp.checkId(id);

	                        if (!this.elements[i].id) throw l + 'id is not a number';

							this.elements[i].price = typeof(price) == 'undefined' ? 0 : SlstApp.nf(price);
							this.elements[i].qty = typeof(qty) == 'undefined' ? 1 : parseInt(qty);
						}
					}
				}
			}
			else if ( typeof(elements) == 'string' )
			{
				elements = elements.split('|');
				var n = 0;
				for (i in elements)
				{
					if ( typeof(elements[i]) == 'string' )
					{
						a = elements[i].split(':');

						a[0] = SlstApp.checkId(a[0]);

						if (!a[0]) throw 'Incorrect elements "id1:qty1:price1|id2:qty2..." format: id is not a number';

						this.elements[n] = {};

						this.elements[n].id = a[0];
						this.elements[n].qty = typeof(a[1]) == 'undefined' ? 0 : parseInt(a[1]);
						this.elements[n].price = typeof(a[2]) == 'undefined' ? 1 : SlstApp.nf(a[2]);
						n++;
					}
				}
			}



	        this.save();

		},



	    mkItem : function (id, qty, price)
		{
			el = {id : 0, qty: 1, price : 0};


			if ( typeof(id) == 'object' ) // id = arguments = {id : 1, qty : 1, price : 0} || 1, 1, 0
			{
				if ( typeof(id[0]) == 'object' )
				{
					el = id[0];
					el.qty = el.qty || 1;
					el.price = SlstApp.nf(el.price) || 0;
				}
				else if ( SlstApp.checkId(id[0]) )
				{
					el.id = id[0];
					el.qty = typeof(id[1]) == 'number' ? id[1] : 1;
					el.price = typeof(id[2]) != 'undefined' ? SlstApp.nf(id[2]) : 0;
				}

			}
			else
			{
				el.id = id||0;
				el.qty = qty||1;
				el.price = SlstApp.nf(price)||0;
			}

            return el;
		},



		add : function()
	    {

	        el = this.mkItem(arguments);

		    f = 0;
			for ( i in this.elements )
			{
				if (this.elements[i].id && this.elements[i].id == el.id)
				{
					this.elements[i].price = el.price ? SlstApp.nf(el.price) : this.elements[i].price;
					this.elements[i].qty += el.qty;
					f = 1;
					break;
				}
			}
			if (!f)
			{
				l = this.elements.length;
				this.elements[l] = el;
			}

			this.save();
		},


		del : function()
		{
			el = this.mkItem(arguments);

			newItems = new Array();
			n = 0;
			for ( i in this.elements )
			{
				if (this.elements[i].id && this.elements[i].id != el.id)
				{
					newItems[n] = this.elements[i];
					n++;
				}
			}
			this.elements = newItems;

			this.save();
		},


		find : function ()
		{
			el = this.mkItem(arguments);

			for ( i in this.elements )
			{
				if (this.elements[i].id && this.elements[i].id == el.id)
				{
					return i;
				}
			}
		},


		getItem : function()
		{
			el = this.mkItem(arguments);
			n = this.find(el);

			if (n)
				return this.elements[n];

		},


		update : function ()
		{
			el = this.mkItem(arguments);
			n = this.find(el);

			if (n)
			{
				this.elements[n] = el;
			}

			this.save();

		},


        getCount : function()
        {
        	return this.count;
        },

        getPrice : function()
        {
        	return this.price;
        },


		clear : function()
		{
			this.elements = [];
			this.save();
		}




	}; // end :: Cart


jQuery(document).ready(function($){

	$(document).on('click', '.app-window-close', function(){

		if (window.Telegram && window.Telegram.WebApp)
		{
			window.Telegram.WebApp.close();
		}

		if (!window.close())
		{
			$('.app-window-close').hide();

			$('.app-window-close-notice').removeClass('d-none').fadeOut().fadeIn();
		}


	});



	$(document).on('click', '.app-query', function(){

		var params = {};

		$.each(this.attributes, function() {
			if (this.name.indexOf('data-param-') === 0)
	    		params[this.name.replace('data-param-', '')] = this.value;
	    });

	    var cacheKey = $(this).attr('data-method') + '-' + $(this).attr('data-param-id');

	    if ($(this).attr('data-no-cache'))
	    	cacheKey = '';

	    $('.app-query').removeClass('active');
	    $('.app-query-' + cacheKey).addClass('active');

	    var config = {
	    	cacheKey : cacheKey
	    };

		SlstApp.query($(this).attr('data-method'), params, config);

		return false;

	});


	$(document).on('submit', '.app-query-form', function(){

	    var config = {
	    	cacheKey : ''
	    };

	    var params = $(this).serialize();

		SlstApp.query($(this).attr('data-method'), params, config);

		return false;

	});


	$('.loader').hide();

});




