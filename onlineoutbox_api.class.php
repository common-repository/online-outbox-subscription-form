<?

class OO_Api  {

	function OO_Api($oo_username = '', $oo_api_token = '') {
		
		$this->oo_username = $oo_username ? $oo_username : (isset($oosf_options['oosf_username']) ? $oosf_options['oosf_username'] : '');
		$this->oo_api_token = $oo_api_token ? $oo_api_token : (isset($oosf_options['oosf_api_token']) ? $oosf_options['oosf_api_token'] : '');
		$this->api_url = 'http://www.onlineoutbox.com/mailv6/xml.php';

	}


	function xml_curl_request($xml_pass = '') {
		
		global $oosf_options;

		$xml_start = '<xmlrequest>
		<username>' . $oosf_options['oosf_username'] . '</username>
		<usertoken>' . $oosf_options['oosf_api_token'] . '</usertoken>';

		$xml_end = '</xmlrequest>';

		$xml = $xml_start . $xml_pass . $xml_end;

		$ch = curl_init($this->api_url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
		$result = @curl_exec($ch);

		$xml_doc = simplexml_load_string($result);

		// print_r($xml_doc);

		if ($xml_doc->status == 'SUCCESS') {

			return $this->xml2array($result);

		} else {
			
			$this->xml_doc_error = $xml_doc->errormessage;
			return FALSE;

		}

	}

	function AddSubscriberToList($xml_params = '') {

		global $oosf_subscribe_form, $oosf_options, $sf;
	
		// $lists = $this->getLists();

		// print_r($oosf_subscribe_form);
		
			$custom_fields_array = $this->GetCustomFields( array('list_id' =>$oosf_subscribe_form->list_id) );

		if(!empty($custom_fields_array)) {

			foreach($custom_fields_array as $item) {
		
				if(isset($_POST[$item['fieldid']])) {

					$custom_fields[$item['fieldid']] = $_POST[$item['fieldid']];
				
				}
			
			}

		}

		if(isset($_POST['emailaddress']) && trim($_POST['emailaddress']) !== '') { $email = $_POST['emailaddress']; } else { $email = $_POST['your-email']; }

		$xml_pass = '
			<requesttype>subscribers</requesttype>
			<requestmethod>AddSubscriberToList</requestmethod>
			<details>
				<emailaddress>' . $email  . '</emailaddress>
				<mailinglist>' . $oosf_subscribe_form->list_id . '</mailinglist>';
				if(isset($custom_fields)) {
					$xml_pass .= '<customfields>';
					foreach($custom_fields as $c_field_id => $c_field_val) {
						$xml_pass .= '<item><fieldid>' . $c_field_id . '</fieldid><value>' . $c_field_val . '</value></item>';
					}
					$xml_pass .= '</customfields>';
				}
		$xml_pass .= '<format>html</format>';
		$xml_pass .= '<confirmed>yes</confirmed>';
		$xml_pass .= '</details>';
		
		// echo $xml_pass;

		$xml_return = $this->xml_curl_request($xml_pass);

		if($xml_return['response']['status'] == 'SUCCESS') {

			unset($xml_return);

			return TRUE;

		} else {
	
			unset($xml_return);

			return FALSE;

		}

	}

	function GetCustomFields($xml_params) {

		global $oosf_options;

		if(!isset($xml_params['list_id'])) { $this->xml_doc_error = 'List id not set.'; return FALSE; }

		$xml_pass = '
		<requesttype>lists</requesttype>
		<requestmethod>GetCustomFields</requestmethod>
		<details>
		<listids>' . $xml_params['list_id'] . '</listids>
		</details>';
		
		$xml_return = $this->xml_curl_request($xml_pass);

		if($xml_return !== FALSE) {
			
			if(!empty($xml_return['response']['data'])) {

				if(isset($xml_return['response']['data']['item'][0]['fieldid'])) {
					
					foreach($xml_return['response']['data']['item'] as $item) {

						$return_custom_fields[] = $item;

					}

				} else {

					$return_custom_fields = array(0 => $xml_return['response']['data']['item'] );

				}

				return $return_custom_fields;

			} else {

				return FALSE;

			}

		} else {
			
			return FALSE;
		
		}

	}

	function GetLists($xml_params = '') {

		$xml_pass = '
		<requesttype>user</requesttype>
		<requestmethod>GetLists</requestmethod>
		<details>
		</details>';
		
		$xml_return = $this->xml_curl_request($xml_pass);

		if($xml_return !== FALSE) {

			if(!empty($xml_return['response']['data'])) {

				if(isset($xml_return['response']['data']['item'][0]['listid'])) {
					return $xml_return['response']['data']['item'];
				} else {
				return $xml_return['response']['data'];
				}

			} else {

				return FALSE;

			}

		} else {

			return FALSE;

		}

	}

function xml2array($contents, $get_attributes=1, $priority = 'tag') {
    if(!$contents) return array();

    if(!function_exists('xml_parser_create')) {
        //print "'xml_parser_create()' function not found!";
        return array();
    }

    //Get the XML parser of PHP - PHP must have this module for the parser to work
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);

    if(!$xml_values) return;//Hmm...

    //Initializations
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();

    $current = &$xml_array; //Refference

    //Go through the tags.
    $repeated_tag_index = array();//Multiple tags with same name will be turned into an array
    foreach($xml_values as $data) {
        unset($attributes,$value);//Remove existing values, or there will be trouble

        //This command will extract these variables into the foreach scope
        // tag(string), type(string), level(int), attributes(array).
        extract($data);//We could use the array by itself, but this cooler.

        $result = array();
        $attributes_data = array();
        
        if(isset($value)) {
            if($priority == 'tag') $result = $value;
            else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
        }

        //Set the attributes too.
        if(isset($attributes) and $get_attributes) {
            foreach($attributes as $attr => $val) {
                if($priority == 'tag') $attributes_data[$attr] = $val;
                else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }

        //See tag status and do the needed.
        if($type == "open") {//The starting of the tag '<tag>'
            $parent[$level-1] = &$current;
            if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
                $current[$tag] = $result;
                if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
                $repeated_tag_index[$tag.'_'.$level] = 1;

                $current = &$current[$tag];

            } else { //There was another element with the same tag name

                if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    $repeated_tag_index[$tag.'_'.$level]++;
                } else {//This section will make the value an array if multiple tags with the same name appear together
                    $current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
                    $repeated_tag_index[$tag.'_'.$level] = 2;
                    
                    if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                        $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                        unset($current[$tag.'_attr']);
                    }

                }
                $last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
                $current = &$current[$tag][$last_item_index];
            }

        } elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
            //See if the key is already taken.
            if(!isset($current[$tag])) { //New Key
                $current[$tag] = $result;
                $repeated_tag_index[$tag.'_'.$level] = 1;
                if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

            } else { //If taken, put all things inside a list(array)
                if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

                    // ...push the new element into that array.
                    $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
                    
                    if($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag.'_'.$level]++;

                } else { //If it is not an array...
                    $current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
                    $repeated_tag_index[$tag.'_'.$level] = 1;
                    if($priority == 'tag' and $get_attributes) {
                        if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
                            
                            $current[$tag]['0_attr'] = $current[$tag.'_attr'];
                            unset($current[$tag.'_attr']);
                        }
                        
                        if($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
                }
            }

        } elseif($type == 'close') { //End of tag '</tag>'
            $current = &$parent[$level-1];
        }
    }
    
    return($xml_array);
}  

}