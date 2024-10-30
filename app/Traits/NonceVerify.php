<?php

namespace HexCoupon\App\Traits;;

trait NonceVerify {
	private function verify_nonce( $type='GET' )
	{
		if( $type === 'POST' || $type === 'post' ) {
			return ! empty( $_POST['nonce'] ) && wp_verify_nonce( $_POST['nonce'],'hexCuponData-react_nonce' ) == 1 ;
		}

		return ! empty( $_GET['nonce'] ) && wp_verify_nonce( $_GET['nonce'],'hexCuponData-react_nonce' ) == 1;
	}
}
