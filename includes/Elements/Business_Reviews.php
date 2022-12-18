<?php
namespace Essential_Addons_Elementor\Elements;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use \Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use \Elementor\Group_Control_Border;
use \Elementor\Group_Control_Box_Shadow;
use \Elementor\Group_Control_Image_Size;
use \Elementor\Group_Control_Typography;
use Elementor\Icons_Manager;
use \Elementor\Plugin;
use \Elementor\Utils;
use \Elementor\Widget_Base;
use \Essential_Addons_Elementor\Classes\Helper;
use ParagonIE\Sodium\Core\Curve25519\Ge\P2;

class Business_Reviews extends Widget_Base {
	
	public function get_name() {
		return 'eael-business-reviews';
	}

	public function get_title() {
		return esc_html__( 'Business Reviews', 'essential-addons-for-elementor-lite' );
	}

	public function get_icon() {
		return 'eaicon-business-reviews';
	}

	public function get_categories() {
		return [ 'essential-addons-elementor' ];
	}

	public function get_keywords() {
		return [
			'reviews',
			'ea reviews',
			'business reviews',
			'ea business reviews',
			'google reviews',
			'ea google reviews',
			'ea',
			'essential addons'
		];
	}

	public function get_custom_help_url() {
		return 'https://essential-addons.com/elementor/docs/business-reviews/';
	}

	protected function register_controls() {

		/**
		 * Business Reviews Settings
		 */
		$this->start_controls_section(
			'eael_section_business_reviews_general_settings',
			[
				'label' => esc_html__( 'General', 'essential-addons-for-elementor-lite' ),
			]
		);

		$this->add_control(
			'eael_business_reviews_sources',
			[
				'label'   => __( 'Source', 'essential-addons-for-elementor-lite' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'google-reviews',
				'options' => [
					'google-reviews' => __( 'Google Reviews', 'essential-addons-for-elementor-lite' ),
				],
			]
		);

        if (empty(get_option('eael_br_google_place_api_key'))) {
            $this->add_control('eael_br_google_place_api_key_missing', [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => sprintf(__('Google Place API key is missing. Please add it from EA Dashboard » Elements » <a href="%s" target="_blank">Business Reviews Settings</a>', 'essential-addons-elementor'), esc_attr( site_url('/wp-admin/admin.php?page=eael-settings') )),
                'content_classes' => 'eael-warning',
                'condition' => [
                    'eael_business_reviews_sources' => 'google-reviews',
                ],
            ]);
        }

		$this->end_controls_section();

		/**
		 * Business Reviews Layout Settings
		 */
		$this->start_controls_section(
			'eael_section_business_reviews_layout_settings',
			[
				'label' => esc_html__( 'Layout', 'essential-addons-for-elementor-lite' ),
			]
		);

		$this->end_controls_section();

		/**
		 * Business Reviews Content
		 */
		$this->start_controls_section(
			'eael_section_business_reviews_content',
			[
				'label' => esc_html__( 'Content', 'essential-addons-for-elementor-lite' ),
			]
		);

		$this->end_controls_section();
	}

	/**
     * API Call to Get Business Reviews
     */
	public function fetch_business_reviews_from_api(){
		$settings = $this->get_settings();
		$settings['eael_business_reviews_source_key'] = get_option( 'eael_br_google_place_api_key' );

		$response                        	  = [];
		$business_reviews                     = [];
		$business_reviews['source']           = ! empty( $settings['eael_business_reviews_sources'] ) ? esc_html( $settings['eael_business_reviews_sources'] ) : 'google-reviews';
		$business_reviews['api_key']          = ! empty( $settings['eael_business_reviews_source_key'] ) ? esc_html( $settings['eael_business_reviews_source_key'] ) : '';
		$business_reviews['reviews_sort']	  = sanitize_text_field( 'most_relevant' );

		$expiration = DAY_IN_SECONDS;
		$md5        = md5( $business_reviews['api_key'] . $this->get_id() );
		$cache_key  = "eael_{$business_reviews['source']}_{$expiration}_{$md5}_brev_cache";
		$items      = get_transient( $cache_key );

		$error_message = '';

		if ( false === $items && 'google-reviews' === $business_reviews['source'] ) {
			$url   = "https://maps.googleapis.com/maps/api/place/details/json";
			$param = array();

			$args = array(
				'key' 	  => sanitize_text_field( 'API key value' ),
				'placeid' => sanitize_text_field( 'ChIJ0cpDbNvBVTcRGX9JNhhpC8I' ),
				'fields'  => sanitize_text_field( 'formatted_address,international_phone_number,name,rating,reviews,url,user_ratings_total,website,photos' ),
			);

			if( ! empty( $business_reviews['reviews_sort'] ) ){
				$args['reviews_sort'] = $business_reviews['reviews_sort'];
			}
			
			$param = array_merge( $param, $args );

			$headers = array(
				'headers' => array(
					'Content-Type' => 'application/json',
				)
			);
			$options = array(
				'timeout' => 240
			);

			$options = array_merge( $headers, $options );

			if ( empty( $error_message ) ) {
				$response = wp_remote_get(
					esc_url_raw( add_query_arg( $param, $url ) ),
					$options
				);

				$body     = json_decode( wp_remote_retrieve_body( $response ) );
				$response = 'OK' === $body->status ? $body->result : false;				

				if ( ! empty( $response ) ) {
					set_transient( $cache_key, $response, $expiration );
				} else {
					$error_message = $this->fetch_api_response_error_message($body->status);
				}
			}

			$data = [
				'items'         => $response,
				'error_message' => $error_message,
			];

			return $data;
		}

		$response = $items ? $items : $response;

		$data = [
			'items'         => $response,
			'error_message' => $error_message,
		];

		return $data;
	}

	public function fetch_api_response_error_message( $status = 'OK' ){
		$error_message = '';

		switch( $status ){
			case 'OK':
				break;

			case 'ZERO_RESULTS':
				$error_message = esc_html__( 'The referenced location, place_id, was valid but no longer refers to a valid result. This may occur if the establishment is no longer in business.', 'essential-addons-for-elementor-lite' );
				break;

			case 'NOT_FOUND':
				$error_message = esc_html__( 'The referenced location, place_id, was not found in the Places database.', 'essential-addons-for-elementor-lite' );
				break;

			case 'INVALID_REQUEST':
				$error_message = esc_html__( 'The API request was malformed.', 'essential-addons-for-elementor-lite' );
				break;

			case 'OVER_QUERY_LIMIT':
				$error_message = esc_html__( 'You have exceeded the QPS limits. Or, Billing has not been enabled on your account. Or, The monthly $200 credit, or a self-imposed usage cap, has been exceeded. Or, The provided method of payment is no longer valid (for example, a credit card has expired).', 'essential-addons-for-elementor-lite' );
				break;

			case 'REQUEST_DENIED':
				$error_message = esc_html__( 'The request is missing an API key. Or, The key parameter is invalid.', 'essential-addons-for-elementor-lite' );
				break;

			case 'UNKNOWN_ERROR':
				$error_message = esc_html__( 'An unknown error occurred.', 'essential-addons-for-elementor-lite' );
				break;

			default:
				break;								
		}

		return $error_message;
	}

	public function print_business_reviews( $business_review_items ){
		$settings = $this->get_settings();
		ob_start();

		$business_reviews   = [];
		$business_review_obj         = isset( $business_review_items['items'] ) ? $business_review_items['items'] : false;
		$error_message = ! empty( $business_review_items['error_message'] ) ? $business_review_items['error_message'] : "";

		$business_reviews['source']            = ! empty( $settings['eael_busines$business_reviews_sources'] ) ? esc_html( $settings['eael_busines$business_reviews_sources'] ) : 'opensea';
		$business_reviews['layout']            = ! empty( $settings['eael_busines$business_reviews_items_layout'] ) ? $settings['eael_busines$business_reviews_items_layout'] : 'grid';
		$business_reviews['preset']            = ! empty( $settings['eael_busines$business_reviews_style_preset'] ) && 'grid' === $business_reviews['layout'] ? $settings['eael_busines$business_reviews_style_preset'] : 'preset-1';
		
		$this->add_render_attribute( 'eael-business-reviews-wrapper', [
			'class'                 => [
				'eael-business-reviews-wrapper',
				'eael-business-reviews-' . $this->get_id(),
				'clearfix',
			],
		] );

		$this->add_render_attribute(
			'eael-business-reviews-items',
			[
				'id'    => 'eael-business-reviews-' . esc_attr( $this->get_id() ),
				'class' => [
					'eael-business-reviews-items',
					'eael-reviews-' . esc_attr( $business_reviews['layout'] ),
					esc_attr( $business_reviews['preset'] ),
				],
			]
		);
		?>

		<div <?php echo $this->get_render_attribute_string('eael-business-reviews-wrapper') ?> >
			<?php if ( is_object( $business_review_obj ) && ! is_null( $business_review_obj ) ) : ?>
			<div <?php echo $this->get_render_attribute_string('eael-business-reviews-items'); ?> >
					<?php 
						echo "<pre>";
						print_r($business_review_obj);
						$item_formatted['title'] = ! empty( $business_review_obj->name ) ? $business_review_obj->name : '';
					?>
				<!-- /.column  -->
			</div>
			<?php else: ?>
				<?php printf( '<div class="eael-business-reviews-error-message">%s</div>', esc_html( $error_message ) ); ?>
			<?php endif; ?>
		</div>

		<?php
		echo ob_get_clean();
	}

	protected function render() {
		$business_review_items = $this->fetch_business_reviews_from_api(); 
		$this->print_business_reviews( $business_review_items );
	}
}