require(['jquery', 'mcp_touchwipe'], 
		function($){
		(function($) {
			$('.menu-creator-pro-responsive .fa-angle-right').removeClass("fa-angle-right").addClass("fa-angle-down");
			var current_height = $('.menu-creator-pro-responsive').height();
			$('.menu-creator-pro-responsive').height(0);
			$('.menu-creator-pro-responsive > .switcher').click(function(){
				current_height = $('.menu-creator-pro-responsive').height();
				var max_height = $('.menu-creator-pro-responsive').css("height","auto").height();
				$('.menu-creator-pro-responsive').height(current_height);
				if($(this).parent().hasClass("active")){
					$(this).parent().removeClass("active").animate({height:"0px"});
				}else{
					$(this).parent().addClass("active").animate({height:max_height});
				}
				return false;
			});
			$('.menu-creator-pro-responsive > li:not(.parent) .fa-angle-down').remove();
			$('.menu-creator-pro-responsive > li .fa-angle-down').addClass("icon_toggle")
			$('.menu-creator-pro-responsive > li .icon_toggle').click(function(){
				var max_height = $('.menu-creator-pro-responsive').css("height","auto");
				if($(this).hasClass("fa-angle-down")){
					$(this).removeClass('fa-angle-down').addClass('fa-angle-up').next().slideDown(500);
				}else{
					$(this).removeClass('fa-angle-up').addClass('fa-angle-down').next().slideUp(500);
				};
			});
			$('.menu-creator-pro .fa-angle-down').click(function(){
			   if(!$(this).parent().hasClass("is-click-active")){
				   $(this).parent().addClass("is-click-active");
				   $(this).parent().siblings('li').removeClass("is-click-active");
			   }else{
				   $(this).parent().removeClass("is-click-active");
			   }
			});
			$('.menu-creator-pro .use-bg').each(function() {
				var menuBg = $(this).find('> a > i > img').attr('src');
				$(this).find('> div').css('background-image', 'url('+ menuBg +')');
			});	
			/* Reponsive side menu */
			$(window).touchwipe({
				wipeLeft: function() {
				  // Close
				  $.sidr('close', 'sidr-main');
				},
				wipeRight: function() {
				  // Open
				  $.sidr('open', 'sidr-main');
				},
				preventDefaultEvents: false
			  });
			//------------------Active State--------------------
			MCP = {
				activeClass : 'current',
				addActiveClass : function(selector) {
					/* Add active class to parent when a child active */
					var current_url = window.location.href;
					var link = null;
					var li_class = null;
					$(selector + ' li a').each(function() {
						link = $(this).attr('href');
						if(link == current_url){
							$(this).addClass(MCP.activeClass);
							$(this).parents('li').addClass(MCP.activeClass);
						}
					});
					/****NOTICE: If you just want active class visiable in li level0, and remove in all another level then uncomment below code */
					/* $(selector + ' li').each(function() {
						try{
							li_class = $(this).attr('class');
							if (li_class != "" && li_class != undefined) {
								if(li_class.indexOf('level0') == -1) {
									$(this).removeClass(MCP.activeClass);
								}
							}

						}catch(error){
							//Do nothing in here
						}
					});*/
				}
			}
			MCP.addActiveClass('.mcp-wrapper');
			var demoMCP = {
				scroll_fix_value: 150,
				init: function() {
					this.cacheDom();
					this.bindEvents();
					this.loadDefaultSettings()
				},
				cacheDom: function() {
				   this.$tableItem = $('body').find('.menu-creator-pro .table-layout');
				   this.$tabItem = $('body').find('.menu-creator-pro .tab-layout');
				},
				bindEvents: function() {
				   $('body').on('click', '.menu-creator-pro.trigger-click > li > a', this.triggerMenu);
				   $('body').on('mouseover', '.table-layout > ul > li.mcpdropdown', this.tableMenuHover);
				   $('body').on('click', '.tab-layout > ul > li.mcpdropdown', this.tabMenuClick);
				   $(document).on('click', 'html', this.triggerClickOutsideNav);
				   $(window).on('scroll', this.fixedTop.bind(this));
				   $('body').on('click', '#mobile-menu', this.firstMenuOnly);
				},
				loadDefaultSettings: function() {
				   this.cacheDom();
				   this.createTableDefault();
				   this.createTabDefault();
				},
				createTableDefault: function() {
					$.each(this.$tableItem, function(index, element) {
						var subDefault = $(element).find('>ul>li:first-child > div').html();
						$(element).append('<div class="table-dropdown-content"></div>');
						$(element).find('>ul>li:first-child').addClass('is-active');
						$(element).find('>.table-dropdown-content').html(subDefault);
					})
				},  
				createTabDefault: function() {
					$.each(this.$tabItem, function(index, element) {
						var subDefault = $(element).find('>ul>li:first-child > div').html();
						$(element).append('<div class="tab-dropdown-content"></div>');
						$(element).find('>ul>li:first-child').addClass('is-active');
						$(element).find('>.tab-dropdown-content').html(subDefault);
					})
				},
				triggerMenu: function(e) {
				   e.preventDefault();
				   var _this = $(this);
				   _this.parent('li').toggleClass('is-click-active');
				   _this.parent('li').siblings('li').removeClass('is-click-active');
				   return false;
				},
				triggerClickOutsideNav: function(e) {
				   if ($(e.target).closest('.mcp-wrapper').length === 0) {
					$('.trigger-click > li').removeClass('is-click-active')
				   }
				},
				tableMenuHover: function() {
					var _this = $(this);
					var data_dropdown = _this.find('> div').html();
					_this.addClass('is-active').siblings('li').removeClass('is-active');
					_this.parent().next('.table-dropdown-content').html(data_dropdown);
				},
				tabMenuClick: function(e) {
					e.preventDefault();
					var _this = $(this);
					var data_dropdown = _this.find('> div').html();
					_this.addClass('is-active').siblings('li').removeClass('is-active');
					_this.parent().next('.tab-dropdown-content').html(data_dropdown);
					return false;
				},
				firstMenuOnly: function(event) {
				   var _this = $(this);
				   var main_nav = '.menu-creator-pro';
				   var menu_visible = 'menu-visible'
				   var animation = 'animation-hingeTop';
				   _this.toggleClass(menu_visible);
				   if (_this.hasClass(menu_visible)) {
					_this.next().find(main_nav).children('li').removeClass('is-click-active');
					$.each(_this.next().find(main_nav).children('li'), function(index, element) {
					 setTimeout(function() {
					  $(element).fadeIn()
					 }, (index * 150))
					})
				   }
				   event.preventDefault()
				},  
				fixedTop: function() {
				   var nav_ft = $('.mcp-wrapper.enable-fixed-top');
				   if ($(window).scrollTop() > this.scroll_fix_value) {
					nav_ft.addClass('navigation-fixed-top')
				   } else {
					nav_ft.removeClass('navigation-fixed-top')
				   }
				}
			}
			demoMCP.init();
		})(jQuery);	
});
