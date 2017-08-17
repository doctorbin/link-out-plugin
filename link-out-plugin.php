<?php

/*
Plugin Name: Linkout Plugin
Plugin URI: https://linkout.net
Description: Link out Plugin
Version: 1.0.1
Author: Dr. Bin
Author URI: https://www.facebook.com/binbinbeobeo
License: GPL3
*/

class options_page {

	function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
	}

	function admin_menu() {
		add_options_page(
			'Link Out Plugin',
			'Link Out Plugin',
			'manage_options',
			'options_page_slug',
			array(
				$this,
				'settings_page'
			)
		);
	}

	function settings_page() {
		$check_api = get_option( 'lop_token_api' );
		$dir       = plugin_dir_url( __FILE__ ); ?>
        <link rel='stylesheet' href='<?php echo $dir ?>css/style.css' type='text/css'/>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
		<?php if ( ! $check_api ) { ?>
            <div id="main-screen">
                <h1>Link Out Plugin</h1>
                <form id="input_form">
                    <div class="form-group">
                        <label>User Name</label>
                        <input type="text" name="lop_username" id="lop_username" placeholder="User name" value=""/>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="lop_password" id="lop_password" placeholder="Password" value=""/>
                    </div>
                    <button type="submit">Login</button>
                    <div class="clr"></div>
                    <div id="input_notice"></div>
                </form>
            </div>
            <script>
                $(document).ready(function () {
                    $("#input_form").submit(function (event) {
                        $('#input_notice').removeClass('check_login');
                        var username = $('#lop_username').val();
                        var password = $('#lop_password').val();

                        if ((username !== "") && (password !== "")) {
                            $.ajax({
                                type: "POST",
                                url: "<?php echo $dir; ?>link-out-api.php",
                                data: {
                                    "username": username,
                                    "password": password
                                },
                                beforeSend: function () {
                                    $('#input_notice').text('Loading ...');
                                },
                                success: function (result) {
                                    if (result === 'false') {
                                        $('#input_notice').addClass('check_login').text('Invalid username or password!');
                                    } else {
                                        location.reload();
                                    }
                                }
                            });

                        } else {
                            alert('Fill in all field please.');
                        }
                        event.preventDefault();
                    });
                });
            </script>

		<?php } else {

			add_action( 'admin_init', 'register_my_plugin_settings' );
			function register_my_plugin_settings() {
				register_setting( 'lop_token_api_setting', 'include_domain' );
				register_setting( 'lop_token_api_setting', 'exclude_domain' );
			}

			?>
            <div id="main-screen">
                <div class="lop_notice">
                    <div>You are logged</div>
                    <form id="change_api">
                        <button class="change_acc" type="submit">Change Account</button>
                        <div class="clr"></div>
                    </form>
                </div>
                <p>Your token api : <?php echo $check_api; ?></p>
                <script>
                    $(document).ready(function () {
                        $("#change_api").submit(function (event) {

                            $.ajax({
                                type: "DELETE",
                                url: "<?php echo $dir; ?>link-out-api.php",
                                success: function (result) {
                                    location.reload();
                                }
                            });

                            event.preventDefault();
                        });
                    });
                </script>
				<?php settings_fields( 'test-settings-group' );
				do_settings_sections( 'test-settings-group' ); ?>
                <form id="setting">

                    <div class="form-group">
                        <input id="enable" name="enable"
                               type="checkbox" <?php if ( get_option( 'lop_checkable' ) == 'true' || ! get_option( 'lop_checkable' ) ) {
							echo 'checked';
						} ?> />
                        <span>Enable on entire website (shorten all external links)</span>
                    </div>

                    <div class="form-group">
                        <label>Ads Type</label>
                        <select id="ads_type" name="ads_type">
                            <option value="1" <?php if ( get_option( 'lop_ads_type' ) == '1' ) {
								echo 'selected';
							} ?> >Interstitial Advertising
                            </option>
                            <option value="2" <?php if ( get_option( 'lop_ads_type' ) == '2' ) {
								echo 'selected';
							} ?> >Banner Advertising
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Shorten Domain</label>
                        <select id="linkout_url" name="ads_type">
                            <option value="https://desame.com/linkout/" <?php if ( get_option( 'lop_linkout_url' ) == 'https://desame.com/linkout/' ) {
								echo 'selected';
							} ?> >example1.com
                            </option>
                            <option value="https://linkkhac.com/linkout/" <?php if ( get_option( 'lop_linkout_url' ) == 'https://linkkhac.com/linkout/' ) {
								echo 'selected';
							} ?> >example2.com
                            </option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Include Domain</label>
                        <input class="checkable" type="text" id="include_domain" name="include_domain"
                               value="<?php echo get_option( 'lop_include_domain' ); ?>"/>
                        <span class="description">đây là giải thích</span>
                    </div>
                    <div class="form-group">
                        <label>Exclude Domain</label>
                        <input class="checkable" type="text" id="exclude_domain" name="exclude_domain"
                               value="<?php if ( get_option( 'lop_exclude_domain' ) === false ) {
							       echo get_home_url();
						       } else {
							       echo get_option( 'lop_exclude_domain' );
						       } ?>"/>
                        <span class="description">explain</span>
                    </div>

                    <button type="submit">Save Changes</button>
                    <div class="clr"></div>
                    <div id="notice_setting"></div>
                </form>
                <script>
                    $(document).ready(function () {
                        if ($('#enable').is(':checked')) {
                            $('.checkable').prop('disabled', true);
                        }

                        $("#enable").change(function () {
                            if (this.checked) {
                                $('.checkable').prop('disabled', true);
                            } else {
                                $('.checkable').prop('disabled', false);
                            }
                        });
                        $("#setting").submit(function (event) {
                            var ads_type = $("#ads_type").val();
                            var linkout_url = $("#linkout_url").val();
                            var include_domain = $('#include_domain').val();
                            var exclude_domain = $('#exclude_domain').val();
                            if ($('#enable').is(':checked')) {
                                var checkable = 'true';
                                var exclude_domain = '<?php echo get_home_url(); ?>';
                            } else {
                                var checkable = 'false';
                            }
                            console.log(exclude_domain);
                            $.ajax({
                                type: "PUT",
                                data: {
                                    "checkable": checkable,
                                    "ads_type": ads_type,
                                    "linkout_url": linkout_url,
                                    "include_domain": include_domain,
                                    "exclude_domain": exclude_domain
                                },
                                url: "<?php echo $dir; ?>link-out-api.php",
                                success: function (result) {
                                    $('#notice_setting').addClass('success_notice').text('Save changes successfully!');
                                }
                            });

                            event.preventDefault();
                        });
                    })
                </script>
            </div>
			<?php
		}
	}
}

$check_api = get_option( 'lop_token_api' );

if ( $check_api ) {
	function option_to_json( $string ) {
		$string = preg_replace( '/\s+/', '', $string );
		$string = explode( ",", $string );

		return json_encode( $string );
	}

	add_action( 'wp_head', 'full_page_script' );
	if ( ! function_exists( 'full_page_script' ) ) {
		function full_page_script() { ?>
            <script type="text/javascript">
                var linkout_url = '<?php echo get_option( 'lop_linkout_url' ); ?>';
                var linkout_api_token = '<?php echo get_option( 'lop_token_api' ); ?>';
                var linkout_advert = <?php echo get_option( 'lop_ads_type' ); ?>;
				<?php
				$linkout_domains = option_to_json( get_option( 'lop_include_domain' ) );
				$linkout_exclude_domains = option_to_json( get_option( 'lop_exclude_domain' ) );
				if ( get_option( 'lop_checkable' ) == 'true' ) { ?>
                var linkout_exclude_domains = ["<?php echo get_home_url();?>"];
				<?php } else {

				if ( $linkout_domains ) { ?>
                var linkout_domains = <?php echo $linkout_domains; ?>;
				<?php } ?>
                var linkout_exclude_domains = <?php echo $linkout_exclude_domains; ?>;

				<?php } ?>
            </script>
            <script src='//desame.com/linkout/js/full-page-script.js'></script>
		<?php }
	}
}

new options_page;
