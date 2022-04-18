(function ($) {
	"use strict";

	$(document).on("click", ".eael-load-more-button", function (e) {
		e.preventDefault();
		e.stopPropagation();
		e.stopImmediatePropagation();
		var $this = $(this),
			$LoaderSpan = $("span", $this),
			$text = $LoaderSpan.html(),
			$widget_id = $this.data("widget"),
			$page_id = $this.data("page-id"),
			$nonce = $this.data("nonce"),
			$scope = $(".elementor-element-" + $widget_id),
			$class = $this.data("class"),
			$args = $this.data("args"),
			$layout = $this.data("layout"),
			$template_info = $this.data("template"),
			$page = parseInt($this.data("page")) + 1,
			$max_page = $this.data("max-page") != undefined ? parseInt($this.data("max-page")) : false,
			$exclude_ids = [],
			$active_term_id = 0,
			$active_taxonomy = '';
		if (typeof $widget_id == "undefined" || typeof $args == "undefined") {
			return;
		}

		var obj = {};
		var $data = {
			action: "load_more",
			class: $class,
			args: $args,
			page: $page,
			page_id: $page_id,
			widget_id: $widget_id,
			nonce: $nonce,
			template_info: $template_info,
		};

		if ( $data.class == "Essential_Addons_Elementor\\Elements\\Woo_Product_Gallery" ) {

			const $taxonomy = {
				taxonomy: $('.eael-cat-tab li a.active', $scope).data('taxonomy'),
				field: 'term_id',
				terms: $('.eael-cat-tab li a.active', $scope).data('id'),
			};
			const eael_cat_tab = localStorage.getItem('eael-cat-tab');

			if( eael_cat_tab == 'true') {
				localStorage.removeItem('eael-cat-tab');
				 var $gallery_page = 1 + 1;

			} else {
				 var $gallery_page = parseInt($('.eael-cat-tab li a.active', $scope).data("page")) + 1;
			}

			$data.taxonomy = $taxonomy;
			$data.page = $gallery_page;
		}

		if ( $data.class === "Essential_Addons_Elementor\\Pro\\Elements\\Dynamic_Filterable_Gallery" ) {
			$('.dynamic-gallery-item-inner').each(function() {
				$exclude_ids.push($(this).data('itemid'));
			});
			
			$active_term_id = $('.dynamic-gallery-category.active').data('termid');
			$active_taxonomy = $('.dynamic-gallery-category.active').data('taxonomy');

			$data.page = 1; //page flag is not needed since we are using exclude ids
			$data.exclude_ids = JSON.stringify($exclude_ids);
			$data.active_term_id = typeof $active_term_id === 'undefined' ? 0 : $active_term_id;
			$data.active_taxonomy = typeof $active_taxonomy === 'undefined' ? '' : $active_taxonomy;
		}

		String($args)
			.split("&")
			.forEach(function (item, index) {
				var arr = String(item).split("=");
				obj[arr[0]] = arr[1];
			});

		if (obj.orderby == "rand") {
			var $printed = $(".eael-grid-post");

			if ($printed.length) {
				var $ids = [];
				$printed.each(function (index, item) {
					var $id = $(item).data("id");
					$ids.push($id);
				});

				$data.post__not_in = $ids;
			}
		}

		$this.addClass("button--loading");
		$LoaderSpan.html(localize.i18n.loading);

		$.ajax({
			url: localize.ajaxurl,
			type: "post",
			data: $data,
			success: function (response) {
				var $content = $(response);

				if (
					$content.hasClass("no-posts-found") ||
					$content.length === 0
				) {
					if ($data.class == "Essential_Addons_Elementor\\Elements\\Woo_Product_Gallery") {
						$this.removeClass('button--loading').addClass('hide-load-more');
						$LoaderSpan.html($text);
					} else if ($data.class == "Essential_Addons_Elementor\\Pro\\Elements\\Dynamic_Filterable_Gallery") {
						$this.addClass('hide');
						$this.closest('.eael-filter-gallery-wrapper').find('.dynamic-gallery-category.active').addClass('no-more-posts');
					} else {
						$this.remove();
					}

				} else {
					if (
						$data.class ==
						"Essential_Addons_Elementor\\Elements\\Product_Grid"
					) {
						$content = $content.filter("li");

						$(".eael-product-grid .products", $scope).append(
							$content
						);

						if ($layout == "masonry") {
							const dynamicID = "eael-product-" + Date.now();
							var $isotope = $(
								".eael-product-grid .products",
								$scope
							).isotope();

							$isotope
								.isotope("appended", $content)
								.isotope("layout");

							$isotope.imagesLoaded().progress(function () {
								$isotope.isotope("layout");
							});

							$content
								.find(".woocommerce-product-gallery")
								.addClass(dynamicID);
							$content
								.find(".woocommerce-product-gallery")
								.addClass("eael-new-product");

							$(
								".woocommerce-product-gallery." + dynamicID,
								$scope
							).each(function () {
								$(this).wc_product_gallery();
							});
						} else {
							const dynamicID = "eael-product-" + Date.now();
							$content.find('.woocommerce-product-gallery').addClass(dynamicID);
							$content.find('.woocommerce-product-gallery').addClass('eael-new-product');

							$(".woocommerce-product-gallery."+dynamicID, $scope).each(function() {
								$(this).wc_product_gallery();
							});
						}
					} else {
						$(".eael-post-appender", $scope).append($content);

						if ($layout == "masonry") {
							var $isotope = $(
								".eael-post-appender",
								$scope
							).isotope();
							$isotope
								.isotope("appended", $content)
								.isotope("layout");

							$isotope.imagesLoaded().progress(function () {
								$isotope.isotope("layout");
							});
						}
					}

					$this.removeClass("button--loading");
					$LoaderSpan.html($text);

					if ( $data.class == "Essential_Addons_Elementor\\Elements\\Woo_Product_Gallery" ) {
						$('.eael-cat-tab li a.active', $scope).data("page", $gallery_page);
					} else {
						$this.data("page", $page);
					}

					if ($data.class == "Essential_Addons_Elementor\\Pro\\Elements\\Dynamic_Filterable_Gallery") {
						let found_posts = $($content[0]);

						if (found_posts.hasClass('found_posts') && found_posts.text() - obj.posts_per_page < 1) {
							let active_tab = $this.closest('.eael-filter-gallery-wrapper').find('.dynamic-gallery-category.active'),
								active_filter = active_tab.data('filter');

							$this.addClass('hide');
							active_tab.addClass('no-more-posts');
							if (active_filter === '*') {
								active_tab.siblings().addClass('no-more-posts');
							}
						}
					} else {
						if ($max_page && $data.page >= $max_page) {
							$this.remove();
						}
					}
				}
			},
			error: function (response) {
				console.log(response);
			},
		});
	});
})(jQuery);
