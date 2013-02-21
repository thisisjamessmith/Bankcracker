<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * @author              James Smith (james@jamessmith.co.uk)
 * @copyright           Copyright (c) 2013 James Smith Web Consultancy
 * @license             http://creativecommons.org/licenses/by-nc-sa/3.0/
 * @link                http://www.jamessmith.co.uk
 */

class Bankcracker_ext {
	
	public $settings 		= array();
	public $description		= 'Multi-Channel Safecracker Submissions';
	public $docs_url		= '';
	public $name			= 'Bankcracker';
	public $settings_exist	= 'n';
	public $version			= '0.6';
	private $EE;
	private $hooks 			= array('safecracker_submit_entry_end');

	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
	}

	// ----------------------------------------------------------------------

	public function safecracker_submit_entry_end(&$SC)
	{		
		$success = ( !$SC->errors && !$SC->field_errors );

		if ( $success )
		{
			// --------------------------------------------
			// Are there any channel_n: prefixed fields in POST?
			// --------------------------------------------

			$pattern = '/^channel_(\d*):(.*)$/';
			$keys = array_keys($_POST);
			$matches = preg_grep($pattern,$keys); // returns an array of matches

			if ( !$matches ) { return; }
			
			// --------------------------------------------
			// Have we processed this before?
			// --------------------------------------------

			if ( !empty($this->EE->session->cache['bankcracker']['submitted'][$SC->channel['channel_id']]) )
			{	
				// bail out if we've done this channel already
				$this->EE->extensions->end_script = TRUE;
				return;			
			}

			// --------------------------------------------
			// Gather all the POST data and channels into a single array
			// --------------------------------------------

			if ( isset($this->EE->session->cache['bankcracker']['channel_fields']) )
			{
				// shortcut if we've been here before...
				$channel_fields = $this->EE->session->cache['bankcracker']['channel_fields'];
			} else {
				// set $channel_fields array to hold everything we need to do
				foreach ($matches as $matchkey => $postkey)
				{
					$parts = explode(":", $postkey);
					$parts[0] = str_replace('channel_', '', $parts[0]);
					$this->EE->session->cache['bankcracker']['channel_fields'][$parts[0]][$parts[1]] 
						= $channel_fields[$parts[0]][$parts[1]]
						= $_POST[$postkey];
				}
				// the lack of normal looping in the foreach below makes it hard to identify when we hit the final item in $channel_fields, so let's just hack one in like this...
				$this->EE->session->cache['bankcracker']['channel_fields']['final'] = 'final';
				$channel_fields['final'] = 'final';		
			}

			if ( !$channel_fields ) { return; }

			// --------------------------------------------
			// Initiate a new Safecracker submission per channel
			// --------------------------------------------

			foreach ($channel_fields as $channel_id => $fields)
			{	
				if ( $channel_id == 'final')
				{			
					// fire a custom extension hook to handle any further processing
					if ($this->EE->extensions->active_hook('bankcracker_end') === TRUE)
					{
						$this->EE->extensions->call('bankcracker_end', $SC);
					}

					// --------------------------------------------
					// Now finally get out of this hell loop... ;-)
					// --------------------------------------------
					$ret = $this->EE->input->post('return');
					$this->EE->functions->redirect($ret);
				}

				// --------------------------------------------
				// Reset the relevant POST data and Safecracker will do the rest
				// --------------------------------------------

				$_POST['channel_id'] = $channel_id;



				foreach ($fields as $field => $value)
				{					
					$_POST[$field] = $value;

					if ( isset($_POST['dynamic_title']) )
					{
						unset($_POST['dynamic_title']);
					}
				}
				
				$this->EE->session->cache['bankcracker']['submitted'][$channel_id] = TRUE;
				$this->EE->safecracker->initialize(TRUE);
				
				// --------------------------------------------
				// IMPORTANT - given that we're using the 
				// safecracker_submit_entry_end hook, remember
				// that this entire method will get called again
				// after using safecracker->submit_entry()... so 
				// this foreach loop will keep starting from the beginning
				// on every pass. We're hacking around that by
				// storing progress in the session cache.
				// Also, due to the redirect in submit_entry(),
				// nothing can execute after this point,
				// making redirection tricky...
				// The saving grace is setting $this->EE->extensions->end_script = TRUE;
				// on the passes where we've been here already.
				// Hacky? sure... but still cool.
				// --------------------------------------------

				$this->EE->safecracker->submit_entry();
			}			
		}
	}


// ----------------------------------------------------------------------
// ----------------------------------------------------------------------
// BORING EXTENSION BOILERPLATE STUFF...
// ----------------------------------------------------------------------
// ----------------------------------------------------------------------

	public function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	public function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	// ----------------------------------------------------------------------

	public function activate_extension()
	{
		foreach($this->hooks as $hook)
		{
			$this->EE->db->insert('extensions',
				array(
					'class'    => __CLASS__,
					'method'   => $hook,
					'hook'     => $hook,
					'settings' => '',
					'priority' => 20,
					'version'  => $this->version,
					'enabled'  => 'y'
				)
			);
		}
	}

	// ----------------------------------------------------------------------
}
/* End of file */