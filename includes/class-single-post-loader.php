<?php
/**
 * Created by PhpStorm.
 * @package Single_Post_Loader.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Single_Post_Loader {

	/**
	 * The single instance of Single_Post_Loader.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for JavaScripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * Single_Post_Loader constructor.
	 *
	 * @param string $file
	 * @param string $version
	 */
	public function __construct ( $file = '', $version = '1.0.0' ) {
		define('SINGLE_POST_LOADER_SCRIPT_DEBUG', false);

		$this->_version = $version;
		$this->_token = 'single_post_loader';

		// Load plugin environment variables
		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );

		$this->script_suffix = defined( 'SINGLE_POST_LOADER_SCRIPT_DEBUG' ) && SINGLE_POST_LOADER_SCRIPT_DEBUG ? '' : '.min';

		register_activation_hook( $this->file, array( $this, 'install' ) );

		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		add_action( 'wp_print_footer_scripts', array( $this, 'localize_script'), 99 );

		// Load admin CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );

		// Load API for generic admin functions
		if ( is_admin() ) {
			$this->admin = new Single_Post_Loader_Admin();
		}

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
	} // End __construct ()

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		if (!get_option('spl_disable')) {
			wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-frontend' );

			$text_color = get_option('spl_text_color');
			$inline_styles = '.spl-loading-icon { fill: ' . $text_color . '; stroke: ' . $text_color . '; }' . "\n";

			wp_add_inline_style( $this->_token . '-frontend', $inline_styles );
		}
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		if (!get_option('spl_disable')) {
			wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version, true );
			wp_enqueue_script( $this->_token . '-frontend' );
		}
	} // End enqueue_scripts ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.1
	 * @return  void
	 */
	public function localize_script () {
		?>
		<script type="text/javascript">
            var spl_button_vars = { wrapper: '<?php echo get_option('spl_wrapper_selector'); ?>' };
		</script>
		<?php
	} // End localize_script ()

	/**
	 * @param string $hook
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'single-post-loader', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
		$domain = 'single-post-loader';

		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * @param string $file
	 * @param string $version
	 *
	 * @return object|Single_Post_Loader
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

	/**
	 * Get get current page url
	 * @access  private
	 * @since   1.0.0
	 * @return  string
	 */
	private function get_current_url() {
		return ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	} // End get_current_url()


	/**
	 * Generate the HTML for the loading icon
	 * @access  public
	 * @since   1.0.0
	 * @return  string
	 */
	public function build_loading_icon($icon_option) {

		$html = '<div class="spl-loading-anim">' . "\n";

		switch ($icon_option) {
			case 'option_a':
				$html .= '  <svg class="spl-loading-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 40 40" enable-background="new 0 0 40 40" xml:space="preserve">' . "\n";
				$html .= '  <path opacity="0.2" d="M20.201,5.169c-8.254,0-14.946,6.692-14.946,14.946c0,8.255,6.692,14.946,14.946,14.946 s14.946-6.691,14.946-14.946C35.146,11.861,28.455,5.169,20.201,5.169z M20.201,31.749c-6.425,0-11.634-5.208-11.634-11.634 c0-6.425,5.209-11.634,11.634-11.634c6.425,0,11.633,5.209,11.633,11.634C31.834,26.541,26.626,31.749,20.201,31.749z"/>' . "\n";
				$html .= '  <path d="M26.013,10.047l1.654-2.866c-2.198-1.272-4.743-2.012-7.466-2.012h0v3.312h0 C22.32,8.481,24.301,9.057,26.013,10.047z">' . "\n";
				$html .= '    <animateTransform attributeType="xml"' . "\n";
				$html .= '      attributeName="transform"' . "\n";
				$html .= '      type="rotate"' . "\n";
				$html .= '      from="0 20 20"' . "\n";
				$html .= '      to="360 20 20"' . "\n";
				$html .= '      dur="0.5s"' . "\n";
				$html .= '      repeatCount="indefinite"/>' . "\n";
				$html .= '    </path>' . "\n";
				$html .= '  </svg>' . "\n";

				break;

			case 'option_b':
				$html .= '  <svg class="spl-loading-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">' . "\n";
				$html .= '  <path d="M25.251,6.461c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615V6.461z">' . "\n";
				$html .= '    <animateTransform attributeType="xml"' . "\n";
				$html .= '      attributeName="transform"' . "\n";
				$html .= '      type="rotate"' . "\n";
				$html .= '      from="0 25 25"' . "\n";
				$html .= '      to="360 25 25"' . "\n";
				$html .= '      dur="0.6s"' . "\n";
				$html .= '      repeatCount="indefinite"/>' . "\n";
				$html .= '    </path>' . "\n";
				$html .= '  </svg>' . "\n";
				break;

			case 'option_c':
				$html .= '  <svg class="spl-loading-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 50 50" style="enable-background:new 0 0 50 50;" xml:space="preserve">' . "\n";
				$html .= '  <path d="M43.935,25.145c0-10.318-8.364-18.683-18.683-18.683c-10.318,0-18.683,8.365-18.683,18.683h4.068c0-8.071,6.543-14.615,14.615-14.615c8.072,0,14.615,6.543,14.615,14.615H43.935z">' . "\n";
				$html .= '    <animateTransform attributeType="xml"' . "\n";
				$html .= '      attributeName="transform"' . "\n";
				$html .= '      type="rotate"' . "\n";
				$html .= '      from="0 25 25"' . "\n";
				$html .= '      to="360 25 25"' . "\n";
				$html .= '      dur="0.6s"' . "\n";
				$html .= '      repeatCount="indefinite"/>' . "\n";
				$html .= '    </path>' . "\n";
				$html .= '  </svg>' . "\n";
				break;

			case 'option_d':
				$html .= '  <svg class="spl-loading-icon" viewBox="0 0 44 44" xmlns="http://www.w3.org/2000/svg">' . "\n";
				$html .= '    <g fill="none" fill-rule="evenodd" stroke-width="2">' . "\n";
				$html .= '        <circle cx="22" cy="22" r="1">' . "\n";
				$html .= '            <animate attributeName="r"' . "\n";
				$html .= '                begin="0s" dur="1.8s"' . "\n";
				$html .= '                values="1; 20"' . "\n";
				$html .= '                calcMode="spline"' . "\n";
				$html .= '                keyTimes="0; 1"' . "\n";
				$html .= '                keySplines="0.165, 0.84, 0.44, 1"' . "\n";
				$html .= '                repeatCount="indefinite" />' . "\n";
				$html .= '            <animate attributeName="stroke-opacity"' . "\n";
				$html .= '                begin="0s" dur="1.8s"' . "\n";
				$html .= '                values="1; 0"' . "\n";
				$html .= '                calcMode="spline"' . "\n";
				$html .= '                keyTimes="0; 1"' . "\n";
				$html .= '                keySplines="0.3, 0.61, 0.355, 1"' . "\n";
				$html .= '                repeatCount="indefinite" />' . "\n";
				$html .= '        </circle>' . "\n";
				$html .= '        <circle cx="22" cy="22" r="1">' . "\n";
				$html .= '            <animate attributeName="r"' . "\n";
				$html .= '                begin="-0.9s" dur="1.8s"' . "\n";
				$html .= '                values="1; 20"' . "\n";
				$html .= '                calcMode="spline"' . "\n";
				$html .= '                keyTimes="0; 1"' . "\n";
				$html .= '                keySplines="0.165, 0.84, 0.44, 1"' . "\n";
				$html .= '                repeatCount="indefinite" />' . "\n";
				$html .= '            <animate attributeName="stroke-opacity"' . "\n";
				$html .= '                begin="-0.9s" dur="1.8s"' . "\n";
				$html .= '                values="1; 0"' . "\n";
				$html .= '                calcMode="spline"' . "\n";
				$html .= '                keyTimes="0; 1"' . "\n";
				$html .= '                keySplines="0.3, 0.61, 0.355, 1"' . "\n";
				$html .= '                repeatCount="indefinite" />' . "\n";
				$html .= '        </circle>' . "\n";
				$html .= '    </g>' . "\n";
				$html .= '  </svg>' . "\n";
				break;

			case 'option_e':
				$html .= '  <svg class="spl-loading-icon" viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg">' . "\n";
				$html .= '	    <circle cx="15" cy="15" r="15">' . "\n";
				$html .= '	        <animate attributeName="r" from="9" to="9"' . "\n";
				$html .= '	                 begin="0s" dur="0.8s"' . "\n";
				$html .= '	                 values="9;15;9" calcMode="linear"' . "\n";
				$html .= '	                 repeatCount="indefinite" />' . "\n";
				$html .= '	        <animate attributeName="fill-opacity" from="0.5" to="0.5"' . "\n";
				$html .= '	                 begin="0s" dur="0.8s"' . "\n";
				$html .= '	                 values=".5;1;.5" calcMode="linear"' . "\n";
				$html .= '	                 repeatCount="indefinite" />' . "\n";
				$html .= '	    </circle>' . "\n";
				$html .= '	    <circle cx="60" cy="15" r="9" fill-opacity="0.3">' . "\n";
				$html .= '	        <animate attributeName="r" from="9" to="9"' . "\n";
				$html .= '	                 begin="0.2s" dur="0.8s"' . "\n";
				$html .= '	                 values="9;15;9" calcMode="linear"' . "\n";
				$html .= '	                 repeatCount="indefinite" />' . "\n";
				$html .= '	        <animate attributeName="fill-opacity" from="0.5" to="0.5"' . "\n";
				$html .= '	                 begin="0.2s" dur="0.8s"' . "\n";
				$html .= '	                 values=".5;1;.5" calcMode="linear"' . "\n";
				$html .= '	                 repeatCount="indefinite" />' . "\n";
				$html .= '	    </circle>' . "\n";
				$html .= '	    <circle cx="105" cy="15" r="15">' . "\n";
				$html .= '	        <animate attributeName="r" from="9" to="9"' . "\n";
				$html .= '	                 begin="0.4s" dur="0.8s"' . "\n";
				$html .= '	                 values="9;15;9" calcMode="linear"' . "\n";
				$html .= '	                 repeatCount="indefinite" />' . "\n";
				$html .= '	        <animate attributeName="fill-opacity" from="0.5" to="0.5"' . "\n";
				$html .= '	                 begin="0.4s" dur="0.8s"' . "\n";
				$html .= '	                 values=".5;1;.5" calcMode="linear"' . "\n";
				$html .= '	                 repeatCount="indefinite" />' . "\n";
				$html .= '	    </circle>' . "\n";
				$html .= '	</svg>' . "\n";
				break;

			case 'option_f':
				$html .= '  <svg class="spl-loading-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 24 24" style="enable-background:new 0 0 50 50;" xml:space="preserve">' . "\n";
				$html .= '    <rect x="0" y="0" width="4" height="7">' . "\n";
				$html .= '      <animateTransform  attributeType="xml"' . "\n";
				$html .= '        attributeName="transform" type="scale"' . "\n";
				$html .= '        values="1,1; 1,3; 1,1"' . "\n";
				$html .= '        begin="0s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '    </rect>' . "\n";
				$html .= '    <rect x="10" y="0" width="4" height="7">' . "\n";
				$html .= '      <animateTransform  attributeType="xml"' . "\n";
				$html .= '        attributeName="transform" type="scale"' . "\n";
				$html .= '        values="1,1; 1,3; 1,1"' . "\n";
				$html .= '        begin="0.2s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '    </rect>' . "\n";
				$html .= '    <rect x="20" y="0" width="4" height="7">' . "\n";
				$html .= '      <animateTransform  attributeType="xml"' . "\n";
				$html .= '        attributeName="transform" type="scale"' . "\n";
				$html .= '        values="1,1; 1,3; 1,1"' . "\n";
				$html .= '        begin="0.4s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '    </rect>' . "\n";
				$html .= '  </svg>' . "\n";
				break;

			case 'option_g':
				$html .= '  <svg class="spl-loading-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 24 30" style="enable-background:new 0 0 50 50;" xml:space="preserve">' . "\n";
				$html .= '    <rect x="0" y="13" width="4" height="5">' . "\n";
				$html .= '      <animate attributeName="height" attributeType="XML"' . "\n";
				$html .= '        values="5;21;5" ' . "\n";
				$html .= '        begin="0s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '      <animate attributeName="y" attributeType="XML"' . "\n";
				$html .= '        values="13; 5; 13"' . "\n";
				$html .= '        begin="0s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '    </rect>' . "\n";
				$html .= '    <rect x="10" y="13" width="4" height="5">' . "\n";
				$html .= '      <animate attributeName="height" attributeType="XML"' . "\n";
				$html .= '        values="5;21;5" ' . "\n";
				$html .= '        begin="0.15s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '      <animate attributeName="y" attributeType="XML"' . "\n";
				$html .= '        values="13; 5; 13"' . "\n";
				$html .= '        begin="0.15s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '    </rect>' . "\n";
				$html .= '    <rect x="20" y="13" width="4" height="5">' . "\n";
				$html .= '      <animate attributeName="height" attributeType="XML"' . "\n";
				$html .= '        values="5;21;5" ' . "\n";
				$html .= '        begin="0.3s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '      <animate attributeName="y" attributeType="XML"' . "\n";
				$html .= '        values="13; 5; 13"' . "\n";
				$html .= '        begin="0.3s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '    </rect>' . "\n";
				$html .= '  </svg>' . "\n";
				break;

			case 'option_h':
				$html .= '  <svg class="spl-loading-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 24 30" style="enable-background:new 0 0 50 50;" xml:space="preserve">' . "\n";
				$html .= '    <rect x="0" y="0" width="4" height="20">' . "\n";
				$html .= '      <animate attributeName="opacity" attributeType="XML"' . "\n";
				$html .= '        values="1; .2; 1" ' . "\n";
				$html .= '        begin="0s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '    </rect>' . "\n";
				$html .= '    <rect x="7" y="0" width="4" height="20">' . "\n";
				$html .= '      <animate attributeName="opacity" attributeType="XML"' . "\n";
				$html .= '        values="1; .2; 1" ' . "\n";
				$html .= '        begin="0.2s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '    </rect>' . "\n";
				$html .= '    <rect x="14" y="0" width="4" height="20">' . "\n";
				$html .= '      <animate attributeName="opacity" attributeType="XML"' . "\n";
				$html .= '        values="1; .2; 1" ' . "\n";
				$html .= '        begin="0.4s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '    </rect>' . "\n";
				$html .= '  </svg>' . "\n";
				break;

			case 'option_j':
				$html .= '  <svg class="spl-loading-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 24 30" style="enable-background:new 0 0 50 50;" xml:space="preserve">' . "\n";
				$html .= '    <rect x="0" y="10" width="4" height="10" opacity="0.2">' . "\n";
				$html .= '      <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '      <animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '     <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '    </rect>' . "\n";
				$html .= '    <rect x="8" y="10" width="4" height="10"  opacity="0.2">' . "\n";
				$html .= '      <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0.15s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '      <animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0.15s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '      <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0.15s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '    </rect>' . "\n";
				$html .= '    <rect x="16" y="10" width="4" height="10"  opacity="0.2">' . "\n";
				$html .= '      <animate attributeName="opacity" attributeType="XML" values="0.2; 1; .2" begin="0.3s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '      <animate attributeName="height" attributeType="XML" values="10; 20; 10" begin="0.3s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '      <animate attributeName="y" attributeType="XML" values="10; 5; 10" begin="0.3s" dur="0.6s" repeatCount="indefinite" />' . "\n";
				$html .= '    </rect>' . "\n";
				$html .= '  </svg>' . "\n";
				break;
		}

		$html .= '</div>' . "\n";

		return $html;
	}


}
