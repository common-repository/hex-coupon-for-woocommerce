<?php

namespace HexCoupon\App\Core\Helpers;

use HexCoupon\App\Core\Lib\SingleTon;

class RenderHelpers
{

	use SingleTon;

	public function Wp_Kses_Allowed_For_Forms()
	{
		return array_merge( $this->common_allowed(), [
			'input' => 	[
				'type' => true,
				'value' => true,
				'name' => true,
				'class' => true,
				'id' => true
			],
			'select' => 	[
				'id' => true,
				'name' => true,
				'class' => true,
				'multiple' => true,
				'tabindex' => true,
				'aria-hidden' => true,
				'data-placeholder' => true,
				'title' => true
			],
			'option' => 	[
				'value' => true,
				'selected' => true,
				'class' => true,
				'id' => true,
				'title' => true
			],
			'optgroup' => 	[
				'label' => true
			],
		] );
	}

	private function common_allowed()
	{
		return [
			'label'      => array(
				'for' => true,
			),
			'a'          => array(
				'href'     => true,
				'rel'      => true,
				'rev'      => true,
				'name'     => true,
				'target'   => true,
				'download' => array(
					'valueless' => 'y',
				),
			),
			'br'         => array(),
			'div'        => array(
				'class' => true,
				'id' => true,
				'align' => true,
			),
			'button'     => array(
				'disabled' => true,
				'name'     => true,
				'type'     => true,
				'value'    => true,
			),
			'caption'    => array(
				'align' => true,
			),
			'cite'       => array(),
			'code'       => array(),
			'col'        => array(
				'align'   => true,
				'char'    => true,
				'charoff' => true,
				'span'    => true,
				'valign'  => true,
				'width'   => true,
			),
			'h1'         => array(
				'align' => true,
			),
			'h2'         => array(
				'align' => true,
			),
			'h3'         => array(
				'align' => true,
			),
			'h4'         => array(
				'align' => true,
			),
			'h5'         => array(
				'align' => true,
			),
			'h6'         => array(
				'align' => true,
			),
			'ul'         => array(
				'type' => true,
			),
			'ol'         => array(
				'start'    => true,
				'type'     => true,
				'reversed' => true,
			),
			'var'        => array(),
			'video'      => array(
				'autoplay'    => true,
				'controls'    => true,
				'height'      => true,
				'loop'        => true,
				'muted'       => true,
				'playsinline' => true,
				'poster'      => true,
				'preload'     => true,
				'src'         => true,
				'width'       => true,
			),
			'sup'        => array(),
			'table'      => array(
				'align'       => true,
				'bgcolor'     => true,
				'border'      => true,
				'cellpadding' => true,
				'cellspacing' => true,
				'rules'       => true,
				'summary'     => true,
				'width'       => true,
			),
			'tbody'      => array(
				'align'   => true,
				'char'    => true,
				'charoff' => true,
				'valign'  => true,
			),
			'td'         => array(
				'abbr'    => true,
				'align'   => true,
				'axis'    => true,
				'bgcolor' => true,
				'char'    => true,
				'charoff' => true,
				'colspan' => true,
				'headers' => true,
				'height'  => true,
				'nowrap'  => true,
				'rowspan' => true,
				'scope'   => true,
				'valign'  => true,
				'width'   => true,
			),
			'textarea'   => array(
				'cols'     => true,
				'rows'     => true,
				'disabled' => true,
				'name'     => true,
				'readonly' => true,
			),
			'tfoot'      => array(
				'align'   => true,
				'char'    => true,
				'charoff' => true,
				'valign'  => true,
			),
			'th'         => array(
				'abbr'    => true,
				'align'   => true,
				'axis'    => true,
				'bgcolor' => true,
				'char'    => true,
				'charoff' => true,
				'colspan' => true,
				'headers' => true,
				'height'  => true,
				'nowrap'  => true,
				'rowspan' => true,
				'scope'   => true,
				'valign'  => true,
				'width'   => true,
			),
			'thead'      => array(
				'align'   => true,
				'char'    => true,
				'charoff' => true,
				'valign'  => true,
			),
			'title'      => array(),
			'tr'         => array(
				'align'   => true,
				'bgcolor' => true,
				'char'    => true,
				'charoff' => true,
				'valign'  => true,
			),
			'small'      => array(),
			'strike'     => array(),
			'strong'     => array(),
			'sub'        => array(),
			'span'       => array(
				'align' => true,
			),
			'section'    => array(
				'align' => true,
			),
			'p'          => array(
				'align' => true,
				'class' => true,
			),
			'pre'        => array(
				'width' => true,
			),
			'nav'        => array(
				'align' => true,
			),
			'li'         => array(
				'align' => true,
				'value' => true,
			),
			'hr'         => array(
				'align'   => true,
				'noshade' => true,
				'size'    => true,
				'width'   => true,
			),
			'i'          => array(),
			'img'        => array(
				'alt'      => true,
				'align'    => true,
				'border'   => true,
				'height'   => true,
				'hspace'   => true,
				'loading'  => true,
				'longdesc' => true,
				'vspace'   => true,
				'src'      => true,
				'usemap'   => true,
				'width'    => true,
			),
		];
	}

	public function Wp_Kses_Allowed_String()
	{
		return array_merge( $this->common_allowed(), array(
			'address'    => array(),
			'abbr'       => array(),
			'acronym'    => array(),
			'area'       => array(
				'alt'    => true,
				'coords' => true,
				'href'   => true,
				'nohref' => true,
				'shape'  => true,
				'target' => true,
			),
			'article'    => array(
				'align' => true,
			),
			'aside'      => array(
				'align' => true,
			),
			'audio'      => array(
				'autoplay' => true,
				'controls' => true,
				'loop'     => true,
				'muted'    => true,
				'preload'  => true,
				'src'      => true,
			),
			'b'          => array(),
			'bdo'        => array(),
			'big'        => array(),
			'blockquote' => array(
				'cite' => true,
			),


			'colgroup'   => array(
				'align'   => true,
				'char'    => true,
				'charoff' => true,
				'span'    => true,
				'valign'  => true,
				'width'   => true,
			),
			'del'        => array(
				'datetime' => true,
			),
			'dd'         => array(),
			'dfn'        => array(),
			'details'    => array(
				'align' => true,
				'open'  => true,
			),

			'dl'         => array(),
			'dt'         => array(),
			'em'         => array(),
			'fieldset'   => array(),
			'figure'     => array(
				'align' => true,
			),
			'figcaption' => array(
				'align' => true,
			),
			'font'       => array(
				'color' => true,
				'face'  => true,
				'size'  => true,
			),
			'footer'     => array(
				'align' => true,
			),

			'header'     => array(
				'align' => true,
			),
			'hgroup'     => array(
				'align' => true,
			),

			'ins'        => array(
				'datetime' => true,
				'cite'     => true,
			),
			'kbd'        => array(),
			'label'      => array(
				'for' => true,
			),
			'legend'     => array(
				'align' => true,
			),

			'main'       => array(
				'align' => true,
			),
			'map'        => array(
				'name' => true,
			),
			'mark'       => array(),
			'menu'       => array(
				'type' => true,
			),

			'object'     => array(
				'data' => array(
					'required'       => true,
					'value_callback' => '_wp_kses_allow_pdf_objects',
				),
				'type' => array(
					'required' => true,
					'values'   => array( 'application/pdf' ),
				),
			),

			'q'          => array(
				'cite' => true,
			),
			'rb'         => array(),
			'rp'         => array(),
			'rt'         => array(),
			'rtc'        => array(),
			'ruby'       => array(),
			's'          => array(),
			'samp'       => array(),

			'summary'    => array(
				'align' => true,
			),

			'track'      => array(
				'default' => true,
				'kind'    => true,
				'label'   => true,
				'src'     => true,
				'srclang' => true,
			),
			'tt'         => array(),
			'u'          => array(),

		) );
	}
}
