<?php

defined('ABSPATH') || exit;

/**
 * Prompt login customizer
 *
 * @package prompt
 */
function prompt_custom_login_logo() {
    $logo = get_theme_mod('custom_logo');

    if ($logo) {
        $image = wp_get_attachment_image_src($logo, 'full');
        $image_url = esc_url($image[0]);
    } else {
        $image_url = ASSETS_URL . 'images/logo.webp';
    }

    echo '<style type="text/css">
            #login h1 a {
                background: transparent url(' . $image_url . ') no-repeat center / contain;
              	width: 180px;
			  	height: 106px;
				margin-bottom: 0;
			}
			body{
				background: url(' . ASSETS_URL . 'images/login-bg.webp) no-repeat center / cover;
			}
			a:focus{
				box-shadow:none;
			}
			.login form{
				margin-top: 0;
				border: none;
				box-shadow: none;
			}
			#login {
				padding: 20px;
				background: white;
				border-radius: 20px;
				transform: translateY(-50%);
				position: relative;
				top: 50%;
				box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
			}
        </style>';
}
add_action('login_head', 'prompt_custom_login_logo');

function prompt_custom_login_logo_url() {
    return home_url();
}
add_filter('login_headerurl', 'prompt_custom_login_logo_url');
