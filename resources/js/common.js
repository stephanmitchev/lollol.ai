const hamburgerBtn = document.querySelector("[data-id='hamburger-btn']");
const smScreenNavList = document.querySelector('[data-id="smScreenNavList"]');

if (hamburgerBtn) {
    hamburgerBtn.addEventListener("click", (e) => {
        e.preventDefault();
        smScreenNavList.classList.toggle("active");

        console.log("clicked");
    });
}

var acc = document.getElementsByClassName("accordion");
var i;

for (i = 0; i < acc.length; i++) {
    acc[i].addEventListener("click", function () {
        this.classList.toggle("active");
        var panel = this.nextElementSibling;
        if (panel.style.maxHeight) {
            panel.style.maxHeight = null;
        } else {
            panel.style.maxHeight = panel.scrollHeight + "px";
        }
    });
}

//  Initialize Swiper
let swiperElement = document.querySelector('.mySwiper');
if (swiperElement) {
    let swiper = new Swiper(".mySwiper", {
        loop: false,
        nextButton: ".swiper-button-next",
        prevButton: ".swiper-button-prev",
        autoplay: {
            delay: 3000,
        },
        slidesPerView: 3,
        paginationClickable: true,
        // spaceBetween: 20,
        breakpoints: {
            1028: {
                slidesPerView: 3,
                spaceBetween: 30,
            },
            320: {
                slidesPerView: 1,
                spaceBetween: 0,
            },
        },
    });
}


/*!
 * jQuery Cookie Plugin v1.4.1
 * https://github.com/carhartl/jquery-cookie
 *
 * Copyright 2013 Klaus Hartl
 * Released under the MIT license
 */
(function (factory) {
	if (typeof define === 'function' && define.amd) {
		// AMD
		define(['jquery'], factory);
	} else if (typeof exports === 'object') {
		// CommonJS
		factory(require('jquery'));
	} else {
		// Browser globals
		factory(jQuery);
	}
}(function ($) {

	var pluses = /\+/g;

	function encode(s) {
		return config.raw ? s : encodeURIComponent(s);
	}

	function decode(s) {
		return config.raw ? s : decodeURIComponent(s);
	}

	function stringifyCookieValue(value) {
		return encode(config.json ? JSON.stringify(value) : String(value));
	}

	function parseCookieValue(s) {
		if (s.indexOf('"') === 0) {
			// This is a quoted cookie as according to RFC2068, unescape...
			s = s.slice(1, -1).replace(/\\"/g, '"').replace(/\\\\/g, '\\');
		}

		try {
			// Replace server-side written pluses with spaces.
			// If we can't decode the cookie, ignore it, it's unusable.
			// If we can't parse the cookie, ignore it, it's unusable.
			s = decodeURIComponent(s.replace(pluses, ' '));
			return config.json ? JSON.parse(s) : s;
		} catch(e) {}
	}

	function read(s, converter) {
		var value = config.raw ? s : parseCookieValue(s);
		return $.isFunction(converter) ? converter(value) : value;
	}

	var config = $.cookie = function (key, value, options) {

		// Write

		if (value !== undefined && !$.isFunction(value)) {
			options = $.extend({}, config.defaults, options);

			if (typeof options.expires === 'number') {
				var days = options.expires, t = options.expires = new Date();
				t.setTime(+t + days * 864e+5);
			}

			return (document.cookie = [
				encode(key), '=', stringifyCookieValue(value),
				options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
				options.path    ? '; path=' + options.path : '',
				options.domain  ? '; domain=' + options.domain : '',
				options.secure  ? '; secure' : ''
			].join(''));
		}

		// Read

		var result = key ? undefined : {};

		// To prevent the for loop in the first place assign an empty array
		// in case there are no cookies at all. Also prevents odd result when
		// calling $.cookie().
		var cookies = document.cookie ? document.cookie.split('; ') : [];

		for (var i = 0, l = cookies.length; i < l; i++) {
			var parts = cookies[i].split('=');
			var name = decode(parts.shift());
			var cookie = parts.join('=');

			if (key && key === name) {
				// If second argument (value) is a function it's a converter...
				result = read(cookie, value);
				break;
			}

			// Prevent storing a cookie that we couldn't decode.
			if (!key && (cookie = read(cookie)) !== undefined) {
				result[name] = cookie;
			}
		}

		return result;
	};

	config.defaults = {};

	$.removeCookie = function (key, options) {
		if ($.cookie(key) === undefined) {
			return false;
		}

		// Must not alter options, thus extending a fresh object...
		$.cookie(key, '', $.extend({}, options, { expires: -1 }));
		return !$.cookie(key);
	};

}));

if (!$.cookie('location')) {
    $.cookie('location', window.location, {'path' : '/'});
}
else if ($.cookie('location') != window.location) {
    $.cookie('adminHistory', parseInt($.cookie('adminHistory')) + 1, {'path' : '/'});
    $.cookie('location', window.location, {'path' : '/'});
}





jQuery(document).ready(function ($) {
    $('.dropdownBtn').on('click', function () {
        if($(this).closest('.cmnDropDown').hasClass('active')){
            $(this).closest('.cmnDropDown').toggleClass('active');
            $(this).next('.cmnDropDownList').slideToggle();
        }else{
            $('.cmnDropDown').removeClass('active');
            $('.cmnDropDownList').slideUp();
            $(this).closest('.cmnDropDown').addClass('active');
            $(this).next('.cmnDropDownList').slideDown();
        }
    })

    $(document).mouseup(function (e) {
        var container = $(".cmnDropDown");
        // if the target of the click isn't the container nor a descendant of the container
        if (!container.is(e.target) && container.has(e.target).length === 0) {
            container.find('.cmnDropDownList').slideUp();
        }
    });


    $(function () {
        // copy content to clipboard
        function copyToClipboard(element) {
            var $temp = $("<input>");
            $("body").append($temp);
            $temp.val($(element).html()).select();
            document.execCommand("copy");
            $temp.remove();
        }

        // copy coupone code to clipboard
        $(".copy-link-btn").on("click", function () {
            let copyTxt = $(this).closest('.copy-link-wpr').find('.copy-txt');
            copyToClipboard(copyTxt);
            alert('Text Copied!')
        });


        $('.create_url').on('click', function(){
            $('.popup_outtr').removeClass('hidden');
            setTimeout(() => {
                $('.genarate-url-popUp').removeClass('invisible opacity-0 pointer-events-none translate-y-4');
            }, 100);

        });
        $('.popup-close-btn').on('click', function(){
            $('.genarate-url-popUp').addClass(' opacity-0 pointer-events-none translate-y-4');
            setTimeout(() => {
                $('.genarate-url-popUp').addClass('invisible');
                $('.popup_outtr').addClass('hidden');
        }, 400);
        })
    });


    $('.navbar-toggler').on('click', function(){
        $('.navbar-toggler .stick').toggleClass('open');
        $('.left_sidebar').toggleClass('-translate-x-full');
        $('.navoverlay').toggleClass('hidden');
        $('body').toggleClass('overflow-hidden');
    })
})
