<?php

namespace HexCoupon\App\Core\Helpers;

use HexCoupon\App\Core\Lib\SingleTon;
use function Automattic\Jetpack\Extensions\Business_Hours\render;

class FormHelpers
{
	public static function Init( array $args )
	{
		return ( new self( $args ) )->render();
	}
	protected array $args;
	public function __construct( array $args )
	{
		$defaults = [
			'label' => esc_html__('label','hex-coupon-for-woocommerce'),
			'name' => 'name',
			'id' => '',
			'parent_class' => 'options_group',
			'input_class' => '',
			'input_wrapper_class' => 'form-field',
			'input_wrapper_tag' => 'p',
			'value' => null,
			'type' => 'input', // select
			'class' => 'short',
			'select2' => false
		];

		$this->args = array_merge( $defaults, $args );
	}
	/**
	 * return field name
	 * @package hexcupon
	 * @since 1.0.0
	 * */
	private function getName()
	{
		return $this->args['name'];
	}
	private function getId()
	{
		return $this->args['id'];
	}
	private function getClass()
	{
		return $this->args['class'];
	}
	private function render() : string
	{
		$markup = '<div class="'.$this->getParentClass().'"><'.$this->getInputWrapperTag().' class="'.$this->getInputWrapperClass().'"><label for="'.$this->getName().'">'.$this->getLabel() .'</label>';
		$markup .= $this->renderFields();
		$markup .='</'.$this->getInputWrapperTag().'></div>';
		return $markup;
	}

	private function getLabel()
	{
		return $this->args['label'];
	}

	private function getParentClass()
	{
		return $this->args['parent_class'];
	}

	private function getInputWrapperClass()
	{
		return $this->args['input_wrapper_class'];
	}
	private function getSelect2()
	{
		return $this->args['select2'];
	}
	private function getInputWrapperTag()
	{
		return $this->args['input_wrapper_tag'];
	}
	private function getValue()
	{
		$default =  $this->args['default'] ?? '';
		return $this->args['value'] ?? $default;
	}
	private function getType()
	{
		return $this->args['type'] ?? '';
	}
	private function renderFields() : string
	{
		$markup = '';

		switch ( $this->getType() )
		{
			case('checkbox'):
			case('radio'):

				$class = $this->getClass();
				$classes = in_array($this->getType(),['checkbox','radio']) ?  str_replace('short',' ',$class) : $class;

				$checked = !empty($this->args['checked']) ? 'checked' : '';
				$markup .= '<input type="'.$this->getType().'" name="'.$this->getName().'" id="'.$this->getName().'" class="'.$classes.'" value="'.$this->getValue().'" '.$checked.' />';
				if (!empty($this->args['description'])){
					$markup .= '<span class="description">'.$this->getDescription().'</span>';
				}

				break;
				//select, multi select
			case('select'):
				$value =  $this->getValue();
				$options = $this->args['options'] ?? [];
				$multiple = !empty($this->args['multiple']) && $this->args['multiple'] ? 'multiple' : '';
				$select2 = !empty($this->args['select2']) && $this->args['select2'] ? 'hex__select2' : '';
				$placeholderData = !empty($this->args['placeholder']) && $this->args['placeholder'] ?  'data-placeholder="'.$this->args['placeholder'].'"' : '';
				$search = '';
				$multipleArrayAppend = $multiple === 'multiple' ? '[]' : '';
				$markup .= '<select '.$placeholderData.'  name="'.$this->getName().$multipleArrayAppend.'" id="'.$this->getId().'" class="select '.$this->getClass().' '.$select2.'" '.$multiple.' >';

				foreach($options as $opt => $val){
					$selected = ($opt === $value) ? 'selected' : '';

					if ( $multiple === 'multiple' && is_array($value) ){
						$selected = in_array($opt,$value) ? 'selected' : '';
					}

					$markup .= '<option title="'.esc_html($val).'" value="'.esc_attr($opt).'" '.$selected.' >'.esc_html($val).'</option>';
				}

				$markup .= '</select>';

				if (!empty($this->args['description'])){
					$markup .= '<span class="description">'.$this->getDescription().'</span>';
				}
				break;

			default:
				$markup .= '<input type="'.$this->getType().'" name="'.$this->getName().'" id="'.$this->getName().'" class="'.$this->getClass().'" value="'.$this->getValue().'" />';
				if ( !empty($this->args['description']) ){
					$markup .= '<span class="description">'.$this->getDescription().'</span>';
				}
				break;
		}

		return $markup;
	}

	private function getDescription()
	{
		return $this->args['description'] ?? '';
	}
}
