<?php

require_once( "../../../wp-load.php" );

if ( $_SERVER['REQUEST_METHOD'] === 'DELETE' ) {
	delete_option( 'lop_token_api' );
	exit();
}

if ( $_SERVER['REQUEST_METHOD'] === 'PUT' ) {

	parse_str( file_get_contents( 'php://input' ), $_PUT );

	delete_option( 'lop_checkable' );
	delete_option( 'lop_ads_type' );
	delete_option( 'lop_linkout_url' );
	delete_option( 'lop_include_domain' );
	delete_option( 'lop_exclude_domain' );

	$checkable      = $_PUT['checkable'];
	$ads_type = $_PUT['ads_type'];
	$linkout_url = $_PUT['linkout_url'];
	$include_domain = $_PUT['include_domain'];
	$exclude_domain = $_PUT['exclude_domain'];

	add_option( 'lop_checkable', $checkable );
	add_option( 'lop_ads_type', $ads_type );
	add_option( 'lop_linkout_url', $linkout_url );
	add_option( 'lop_include_domain', $include_domain );
	add_option( 'lop_exclude_domain', $exclude_domain );

	exit();
}

$username = $_POST['username'];
$password = $_POST['password'];

require_once './vendor/autoload.php';

use Curl\Curl;

$getTokenLogin = 'https://desame.com/linkout/auth/signin';
$loginUrl      = 'https://desame.com/linkout/auth/signin';
$getTokenAPI   = 'https://desame.com/linkout/member/tools/api';
$tokenLogin    = new Curl();
$tokenLogin->get( $getTokenLogin );
$postData = [
	'_method'          => 'POST',
	'username'         => $username,
	'password'         => $password,
	'_Token[fields]'   => 'fbd2868b2b6749475706ecf7ecdae05dee31c1aa:',
	'_Token[unlocked]' => 'adcopy_challenge|adcopy_response|g-recaptcha-response',
	'_csrfToken'       => ''
];

preg_match( '/\<input\s+type="hidden"\s+name="_csrfToken"\s+value="([^"]+)"\s*\/\>/i', $tokenLogin->rawResponse, $matches );

if ( ! array_key_exists( 1, $matches ) ) {
	echo 'error ', $tokenLogin->errorMessage;
	exit();
}

$postData['_csrfToken'] = $matches[1];
$postLogin              = new Curl();
$postLogin->setCookie( 'csrfToken', $postData['_csrfToken'] );
$postLogin->setHeader( 'Content-Type', 'application/x-www-form-urlencoded' );

$postLogin->post( $loginUrl, $postData );
$tokenAPI = new Curl();

foreach ( $postLogin->responseCookies as $cookieName => $cookieValue ) {
	$tokenAPI->setCookie( $cookieName, $cookieValue );
}

$tokenAPI->get( $getTokenAPI );
preg_match( '/\<input\s+value="([^"]+)"\s+readonly\s+class="form-control"\s+onclick="select\(\);"\s*\/\>/', $tokenAPI->rawResponse, $matches );

if ( ! array_key_exists( 1, $matches ) ) {
	echo 'false';
	exit();
}

add_option( 'lop_token_api', $matches[1] );
// Set default value
add_option( 'lop_checkable', 'true' );
add_option( 'lop_ads_type', '2' );
add_option( 'lop_linkout_url', 'https://desame.com/linkout/' );
add_option( 'lop_include_domain', '' );
add_option( 'lop_exclude_domain', get_home_url() );

echo 'Your token api : ', $matches[1];
