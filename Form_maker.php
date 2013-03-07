<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Form_maker {

/*
	$this->form_fields = (object)array(
		'nome' => array(
			'type'			 => 'file', //file|text|hidden|password|password_confirm|radios|checkbox|select|multiple|textarea|image',
			'title'			 => 'Titulo',
			'description'	 => 'Texto de ajuda do campo',
			'id'			 => 'field-id',
			'maxlenght'		 => 10, //hidden|passowrd|password_confirm|textarea
			'options'		 => array('key' => 'value'), //checkbox|radios|select
			'default_value'	 => 'key', //radios|select
			'default_value'	 => array('key', 'key'), //checkbox|multiple
			'form_validator' => 'trim|required|is_naural_no_zero|xss_clean',
			'crop'			 => array([min:arr(int,int)], [max:arr(int,int)], [keep_aspect:bool]), //image
		),
	);
*/

	private
		$CI,
		$output = '';

	// --------------------------------------------------------------------

	/**
	 * Start needed resources
	 *
	 * @return	void
	 * @access	public
	 * @author	William Ochetski Hellas
	 */
	function __construct()
	{
		# debug
		log_message('debug', 'Form_maker Class Initialized');

		# call Codeigniter instance
		$this->CI =& get_instance();

		# loads necessary libraries
		$this->CI->load->library(array('form_validation'));

		# loads needed models
		//$this->load->model(array());

		# loads needed helpers
		//$this->load->helper(array());
	}

	// --------------------------------------------------------------------

	/**
	 * Validate form using CI methods
	 *
	 * @return	boolean
	 * @access	public
	 * @author	William Ochetski Hellas
	 */
	public function receive($form_fields)
	{
	}

	// --------------------------------------------------------------------

	/**
	 * Create form based on it's types
	 *
	 * @param	object	$form_fields
	 * @return	string	HTML
	 * @access	public
	 * @author	William Ochetski Hellas
	 */
	public function output($form_fields)
	{
		$this->output = '';
		foreach($form_fields as $key => $val)
		{
			if(method_exists($this, 'add_'.$val->type))
			{
				$this->{'add_'.$val->type}($key, $val);
			} else {
				log_message('debug', "Method 'add_{$val->type}' not found.");
			}
		}
		return $this->output;
	}

	// --------------------------------------------------------------------

	/**
	 * Create form based on it's types
	 *
	 * @param	string	$name
	 * @param	object	$field
	 * @return	void
	 * @access	private
	 * @author	William Ochetski Hellas
	 */
	private function add_multiple($name, $field)
	{
		$this->output .= '<fieldset>'.PHP_EOL;
		if(isset($field->title) && !empty($field->title)) {
			$this->output .= '<legend>'.self::_lang($field->title).'</legend>'.PHP_EOL;
		}
		$form_fields = $field->fields;
		foreach($form_fields as $key => $val)
		{
			if(method_exists($this, 'add_'.$val->type))
			{
				$this->{'add_'.$val->type}($key, $val, TRUE);
			} else {
				log_message('debug', "Method 'add_{$val->type}' not found.");
			}
		}
		$this->output .= '</fieldset>'.PHP_EOL;
		return $this->output;
	}

	// --------------------------------------------------------------------

	/**
	 * Creates input text element
	 *
	 * @param	string	$name
	 * @param	object	$field
	 * @param	boolean	$multiple
	 * @return	void
	 * @access	private
	 * @author	William Ochetski Hellas
	 * @todo	Finish multiple values
	 */
	private function add_text($name, $field, $multiple = FALSE)
	{
		$attrs = self::_general_attrs($field);
		$this->output .=
			'<div class="text_input">'.PHP_EOL.
			'<label>'.self::_lang($field->title).'</label>'.PHP_EOL.
			'<input type="text" name="'.$name.'" value="'.(isset($field->default_value) ? $field->default_value : NULL).'"'.$attrs.' />'.PHP_EOL.
			(isset($field->description) ? '<span>'.self::_lang($field->description).'</span>'.PHP_EOL : NULL).
			'</div>'.PHP_EOL;
	}

	// --------------------------------------------------------------------
	/**
	 * Creates input password element
	 *
	 * @param	string	$name
	 * @param	object	$field
	 * @param	boolean	$multiple
	 * @return	void
	 * @access	private
	 * @author	Flávio da Silva Rodrigues
	 * @todo	Finish multiple values
	 */
	private function add_password($name, $field, $multiple = FALSE)
	{
		$attrs = self::_general_attrs($field);
		$this->output .=
			'<div class="text_input">'.PHP_EOL.
			'<label>'.self::_lang($field->title).'</label>'.PHP_EOL.
			'<input type="password" name="'.$name.'" value="'.(isset($field->default_value) ? $field->default_value : NULL).'"'.$attrs.' />'.PHP_EOL.
			(isset($field->description) ? '<span>'.self::_lang($field->description).'</span>'.PHP_EOL : NULL).
			'</div>'.PHP_EOL;
	}

	// --------------------------------------------------------------------

	/**
	 * Creates checkbox field
	 *
	 * @param	string	$name
	 * @param	object	$field
	 * @return	void
	 * @access	private
	 * @author	William Ochetski Hellas
	 */
	private function add_checkbox($name, $field)
	{
		$attrs = self::_general_attrs($field);
		# join options
		$options = null;
		# add to output
		$this->output .=
			'<div class="text_input">'.PHP_EOL;
		if(isset($field->options) && is_array($field->options))
		{
			foreach($field->options as $key => $val)
			{
				$checked = NULL;
				if(isset($field->default_value))
				{
					if(
						(is_array($field->default_value) && in_array($key, $field->default_value))
						||
						$key == $field->default_value
					)
					{
						$checked = ' checked="checked"';
					}
				}
				$this->output .=
					'<label>'.self::_lang($val).'<input type="checkbox" name="'.$name.'" value="'.$key.'"'.$checked.' /></label>'.PHP_EOL;
			}
		}
		$this->output .=
			(isset($field->description) ? '<span>'.self::_lang($field->description).'</span>'.PHP_EOL : NULL).
			'</div>'.PHP_EOL;
	}

	// --------------------------------------------------------------------

	/**
	 * Creates select field
	 *
	 * @param	string	$name
	 * @param	object	$field
	 * @param	boolean	$multiple
	 * @return	void
	 * @access	private
	 * @author	William Ochetski Hellas
	 * @todo	Finish multiple values
	 */
	private function add_select($name, $field, $multiple = FALSE)
	{
		$attrs = self::_general_attrs($field);
		# join options
		$options = null;
		if(isset($field->options) && is_array($field->options))
		{
			foreach($field->options as $key => $val)
			{
				$selected = isset($field->default_value) ? ($field->default_value == $key ? ' selected="selected"' : NULL) : NULL;
				$options .= '<option value="'.$key.'"'.$selected.'>'.self::_lang($val).'</option>'.PHP_EOL;
			}
		}
		# add to output
		$this->output .=
			'<div class="text_input">'.PHP_EOL.
			'<label>'.self::_lang($field->title).'</label>'.PHP_EOL.
			'<select name="'.$name.'"'.$attrs.'>'.PHP_EOL.
			$options.
			'</select>'.PHP_EOL.
			(isset($field->description) ? '<span>'.self::_lang($field->description).'</span>'.PHP_EOL : NULL).
			'</div>'.PHP_EOL;
	}

	// --------------------------------------------------------------------

	/**
	 * Creates textarea field
	 *
	 * @param	string	$name
	 * @param	object	$field
	 * @param	boolean	$multiple
	 * @return	void
	 * @access	private
	 * @author	William Ochetski Hellas
	 * @todo	Finish multiple values
	 */
	private function add_textarea($name, $field, $multiple = FALSE)
	{
		$attrs = self::_general_attrs($field);
		# add to output
		$this->output .=
			'<div class="text_input">'.PHP_EOL.
			'<label>'.self::_lang($field->title).'</label>'.PHP_EOL.
			'<textarea name="'.$name.'" cols="20" rows="3"'.$attrs.'>'.
			(isset($field->default_value) ? $field->default_value : NULL).
			'</textarea>'.PHP_EOL.
			(isset($field->description) ? '<span>'.self::_lang($field->description).'</span>'.PHP_EOL : NULL).
			'</div>'.PHP_EOL;
	}

	// --------------------------------------------------------------------

	/**
	 * Creates input hidden element
	 *
	 * @param	string	$name
	 * @param	object	$field
	 * @return	void
	 * @access	private
	 * @author	Flávio da Silva Rodrigues
	 */
	private function add_hidden($name, $field)
	{
		$attrs = self::_general_attrs($field);
		$this->output .=
			'<div class="text_input">'.PHP_EOL.
			'<input type="hidden" name="'.$name.'" value="'.(isset($field->default_value) ? $field->default_value : NULL).'"'.$attrs.' />'.PHP_EOL.
			'</div>'.PHP_EOL;
	}

	// --------------------------------------------------------------------

	/**
	 * Creates image with cropping field
	 *
	 * @param	string	$name
	 * @param	object	$field
	 * @param	boolean	$multiple
	 * @return	void
	 * @access	private
	 * @author	William Ochetski Hellas
	 * @todo	Finish multiple values
	 */
	private function add_crop($name, $field, $multiple = FALSE)
	{
		$attrs = self::_general_attrs($field);
		# add to output
		$this->output .=
			'<div class="file_input image_crop">'.PHP_EOL.
			'<label>'.self::_lang($field->title).'</label>'.PHP_EOL.
			'<div id="file-uploader">'.
			'<input type="file" name="'.$name.'" />'.
			'</div>'.PHP_EOL.
			(isset($field->default_image) ? '<img src="'.$field->default_image.'" title="" alt="" class="image-jcrop" />'.PHP_EOL : NULL).
			(isset($field->description) ? '<span>'.self::_lang($field->description).'</span>'.PHP_EOL : NULL).
			'</div>'.PHP_EOL;
	}

	// --------------------------------------------------------------------

	/**
	 * Add validation rules to form_validation class methods
	 *
	 * @param	object	$field
	 * @return	int		number of fields added to validation
	 * @access	public
	 * @author	William Ochetski Hellas
	 */
	public function add_validation($fields)
	{
		$total = 0;
		foreach($fields as $key => $field)
		{
			if(!empty($field->form_validator))
			{
				$this->CI->form_validation->set_rules($key, $field->title, $field->form_validator);
				$total++;
			}
		}
		return $total;
	}

	// --------------------------------------------------------------------

	/**
	 * Make attributes list to place into html tag
	 *
	 * @param	object	$field
	 * @return	string
	 * @access	private
	 * @author	William Ochetski Hellas
	 */
	private function _general_attrs($field)
	{
		# list of attributes to check
		$check = array('id', 'maxlength');
		# set function output
		$output = NULL;
		foreach($check as $attribute)
		{
			if(isset($field->{$attribute}) && !empty($field->{$attribute}))
			{
				$output .= " {$attribute}='{$field->{$attribute}}'";
			}
		}
		return $output;
	}

	// --------------------------------------------------------------------

	/**
	 * Check if requested translated string, if not returns the same string
	 *
	 * @param	string	$text
	 * @return	string
	 * @access	private
	 * @author	William Ochetski Hellas
	 */
	private function _lang($text)
	{
		if(substr($text, 0, 5) == 'lang:')
		{
			$line = substr($text, 5);
			# Were we able to translate the msg?  If not we use $line
			if(false !== ($tranlation = $this->CI->lang->line($line)))
			{
				$text = $tranlation;
			}
		}
		return $text;
	}

}

/* End of file Form_maker.php */
/* Location: ./application_cms/libraries/Form_maker.php */