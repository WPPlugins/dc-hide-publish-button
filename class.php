<?php

class dc_hide_publish_button {
	
	private static $disp_name = 'DC Hide Publish Button';
	private static $name = 'dc_hide_publish_button';
	private static $slug = 'dc-hide-publish-button';
	private static $options = 'dc_hide_publish_button_option';
	private static $section_1 = 'dc-hide-publish-button_sec1';
	private static $field_1 = 'dc-hide-publish-button_enabled';
	private static $field_2 = 'dc-hide-publish-button_mode';
	private static $field_3 = 'dc-hide-publish-button_confirm';
	private $options_value;
	private $scripts;
	
	function __construct() {
		// admin section
		if(!(current_user_can('edit_posts') || current_user_can('publish_posts') || current_user_can('edit_pages') || current_user_can('publish_pages')) && !is_admin()) {
			return;
		}
		$this->get_options_value();
		add_action('admin_menu', array($this, 'init_admin') );
		add_action('admin_menu', array($this, 'do_hide_publish_button') );
		add_action('admin_head', array($this, 'enqueue_script') );
	}
	
	public function enqueue_script() {
		?>
		<script>
			jQuery(document).ready(function($) {
				$('.dc_hide_publish_button').change(function(e) {
					var id = $(this).attr('id');
					id = id.replace('ck_', '');
					var val = 'NO';
					if($(this).attr('checked')) {
						val = 'YES';
					}
					$('#'+id).val(val);
				});
				<?php echo $this->scripts; ?>
			});
		</script>
		<?php
	}

	public function init_admin() {
		add_action( 'admin_init', array($this, 'init_fields') );

		add_options_page(self::$disp_name, self::$disp_name, 'manage_options', self::$slug, array($this, 'show_option_page'));

	}
	
	private function get_options_value() {
		$this->options_value[self::$field_1] = get_option( self::$field_1, 'YES');
		$this->options_value[self::$field_2] = get_option( self::$field_2, '1');
		$this->options_value[self::$field_3] = get_option( self::$field_3, 'NO');
	}
	
	public function section_callback() {
	}
	
	public function init_fields() {

		register_setting( self::$options, self::$field_1 );
		register_setting( self::$options, self::$field_2 );
		register_setting( self::$options, self::$field_3 );

		add_settings_section(
			self::$section_1,
			'General Settings',
			array($this, 'section_callback'),
			self::$slug
			);
		
		add_settings_field( 
			self::$field_1, 
			'Enabled', 
			array($this, 'field_enabled'),
			self::$slug,
			self::$section_1,
			array( $this, 'sanitize' )
			);
		
		add_settings_field( 
			self::$field_2, 
			'Hide Mode', 
			array($this, 'field_mode'),
			self::$slug,
			self::$section_1,
			array( $this, 'sanitize' )
			);
		
		add_settings_field( 
			self::$field_3, 
			'Publish Confirmation', 
			array($this, 'field_confirm'),
			self::$slug,
			self::$section_1,
			array( $this, 'sanitize' )
			);
		
	}
	
	public function sanitize( $input )
    {
        $new_input = array();
		if( isset( $input[self::$field_1] ) ) {
            $new_input[self::$field_1] = sanitize_text_field( $input[self::$field_1] );
		}
		if( isset( $input[self::$field_2] ) ) {
            $new_input[self::$field_2] = sanitize_text_field( $input[self::$field_2] );
		}
		if( isset( $input[self::$field_3] ) ) {
            $new_input[self::$field_3] = sanitize_text_field( $input[self::$field_3] );
		}

        return $new_input;
    }
	
	public function field_enabled() {
		$val = $this->options_value[self::$field_1];
		if(trim($val)=='') {
			$val = 'YES';
		}
		if($val=='YES') {
			$checked = 'CHECKED';
		}
		echo "<input type='hidden' id='".self::$field_1."' name='".self::$field_1."' value='".$val."'>";
		echo "<input type='checkbox' id='ck_".self::$field_1."' ".$checked." value='YES' class='".self::$name."'>";
	}
	
	public function field_mode() {
		$val = $this->options_value[self::$field_2];
		echo "<select id='".self::$field_2."' name='".self::$field_2."' style='width: auto%; min-width: 100px;' placeholder='Hide mode' required='YES'>
				<option value='1'".($val=="1"?" SELECTED":"").">Move Publish Button to Bottom</option>
				<option value='2'".($val=="2"?" SELECTED":"").">Hide Publish Button Until Post/Page Status=\"PENDING REVIEW\"</option>
			</select>";
		echo "<br>\"Hide Publish Button\" will not work with Social Media Auto Publish v1.7 from xyzscript, because they wont auto publish post that publish from PENDING status.";
	}
	
	public function field_confirm() {
		$val = $this->options_value[self::$field_3];
		if(trim($val)=='') {
			$val = 'YES';
		}
		if($val=='YES') {
			$checked = 'CHECKED';
		}
		echo "<input type='hidden' id='".self::$field_3."' name='".self::$field_3."' value='".$val."'>";
		echo "<input type='checkbox' id='ck_".self::$field_3."' ".$checked." value='YES' class='".self::$name."'>";
		echo "&nbsp;Enable confirmation publish button clicked, to avoid accidentally publish. It only confirm when the button caption is \"Publish\"";
	}

	public function show_option_page() {
	?>
	<div style="width: 100%; padding: 10px; margin: 10px;"> 
	
		<h1><?php echo self::$disp_name;?></h1>
		<!-- Start Options Form -->
		<form action="options.php" method="post" id="dc-hide-publish-button-admin">
		<?php
				settings_fields( self::$options );
                do_settings_sections( self::$slug );
                submit_button( "Save" );
		?>
		</form>
		<!-- End Options Form -->
		
	</div>

<?php
	}
	
	private function mode_1() {
		$this->scripts .= "$('#publishing-action').insertAfter('#postimagediv');";
	}
	
	private function show_confirm() {
		$this->scripts .= "
			$('#publish').click(function(e) {
				var caption = $(this).val();
				var publish = '".ucfirst(translate("publish", "my-text-domain"))."';
				var schedule = '".ucfirst(translate("schedule", "my-text-domain"))."';
				if(caption==publish || caption==publish) {
					if(!confirm(publish+' ?')) {
						e.preventDefault();
						e.stop();
					}
				}
			});";
	}
	
	public function do_hide_publish_button() {
		
		if($this->options_value[self::$field_1]=='NO') {
			return;
		}
		
		if($this->options_value[self::$field_2]=='1') {
			$this->mode_1();
		}
		else if($this->options_value[self::$field_2]=='2') {
			$show_publish_button_for_status = array(
					'pending',
					// The statuses below are WordPress' statuses
					'future',
					'publish',
					'schedule',
					'private',
				);

			if ( ! in_array( get_post_status(), $show_publish_button_for_status ) ) {
				?>
				<style>
					#publishing-action { display: none; }
				</style>
				<?php
			}
		}
		
		if($this->options_value[self::$field_3]=='YES') {
			$this->show_confirm();
		}
	}
	
}

?>