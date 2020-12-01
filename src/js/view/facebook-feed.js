jQuery(window).on("elementor/frontend/init", function () {
    let FacebookFeed = function ($scope, $) {
        if (!isEditMode) {
            let $facebook_gallery = $(".eael-facebook-feed", $scope).isotope({
                itemSelector: ".eael-facebook-feed-item",
                percentPosition: true,
                columnWidth: ".eael-facebook-feed-item"
            });

            $facebook_gallery.imagesLoaded().progress(function () {
                $facebook_gallery.isotope("layout");
            });
        }

        // ajax load more
        $(".eael-load-more-button", $scope).on("click", function (e) {
            e.preventDefault();

            let $this = $(this),
                $LoaderSpan = $("span", $this),
                $text = $LoaderSpan.html(),
                $widget_id = $this.data("widget-id"),
                $post_id = $this.data("post-id"),
                $page = parseInt($this.data("page"), 10);

            // update load moer button
            $this.addClass("button--loading");
            $LoaderSpan.html(localize.i18n.loading);

            $.ajax({
                url: localize.ajaxurl,
                type: "post",
                data: {
                    action: "facebook_feed_load_more",
                    security: localize.nonce,
                    page: $page,
                    post_id: $post_id,
                    widget_id: $widget_id,
                },
                success: function (response) {
                    let $html = $(response.html);

                    // append items
                    let $facebook_gallery = $(".eael-facebook-feed", $scope).isotope();
                    $(".eael-facebook-feed", $scope).append($html);
                    $facebook_gallery.isotope("appended", $html);
                    $facebook_gallery.imagesLoaded().progress(function () {
                        $facebook_gallery.isotope("layout");
                    });

                    // update load more button
                    if (response.num_pages > $page) {
                        $this.attr("data-page", parseInt($page) + 1);
                        $this.removeClass("button--loading");
                        $LoaderSpan.html($text);
                    } else {
                        $this.remove();
                    }
                },
                error: function () {
                }
            });
        });
    };
    elementorFrontend.hooks.addAction(
        "frontend/element_ready/eael-facebook-feed.default",
        FacebookFeed
    );
});
