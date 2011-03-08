<?php
class ArrayXML
{
	/**
	 * The main function for converting to an XML document.
	 * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
	 *
	 * @param array $data
	 * @param string $rootNodeName - what you want the root node to be - defaultsto data.
	 * @param SimpleXMLElement $xml - should only be used recursively
	 * @return string XML
	 */
	public static function arrayToXml($data, $rootNodeName = 'data', $xml=null)
	{
		// turn off compatibility mode as simple xml throws a wobbly if you don't.
		if (ini_get('zend.ze1_compatibility_mode') == 1)
		{
			ini_set ('zend.ze1_compatibility_mode', 0);
		}
		
		if ($xml == null)
		{
			$xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><$rootNodeName />");
		}
		
		// loop through the data passed in.
		foreach($data as $key => $value)
		{
			// no numeric keys in our xml please!
			if (is_numeric($key))
			{
				// make string key...
				$key = "unknownNode_". (string) $key;
			}
			
			// replace anything not alpha numeric
			$key = preg_replace('/[^a-z_]/i', '', $key);
			
			// if there is another array found recrusively call this function
			if (is_array($value))
			{
				$node = $xml->addChild($key);
				// recrusive call.
				self::arrayToXml($value, $rootNodeName, $node);
			}
			else 
			{
				// add single node.
                                $value = htmlentities($value);
				$xml->addChild($key,$value);
			}
			
		}
		// pass back as string. or simple xml object if you want!
		return $xml->asXML();
	}


	// Sherwin R. Terunez
	//
	// convert xml object to array
	//
	public static function XMLToArray($obj, $level=0) {
   
	    $items = array();
   
	    if(!is_object($obj)) return $items;
       
	    $child = (array)$obj;

	    if(sizeof($child)>1) {
	        foreach($child as $aa=>$bb) {
      	         if(is_array($bb)) {
                   foreach($bb as $ee=>$ff) {
                     if(!is_object($ff)) {
                         $items[$aa][$ee] = $ff;
                     } else
                     if(get_class($ff)=='SimpleXMLElement') {
                        $items[$aa][$ee] = self::XMLToArray($ff,$level+1);
                     }
                   }
                } else
                if(!is_object($bb)) {
                  $items[$aa] = $bb;
                } else
                if(get_class($bb)=='SimpleXMLElement') {
                  $items[$aa] = self::XMLToArray($bb,$level+1);
                }
            }
          } else
          if(sizeof($child)>0) {
            foreach($child as $aa=>$bb) {
              if(!is_array($bb)&&!is_object($bb)) {
                 $items[$aa] = $bb;
              } else
              if(is_object($bb)) {
                 $items[$aa] = self::XMLToArray($bb,$level+1);
              } else {
                 foreach($bb as $cc=>$dd) {
                    if(!is_object($dd)) {
                        $items[$obj->getName()][$cc] = $dd;
                    } else
                    if(get_class($dd)=='SimpleXMLElement') {
                        $items[$obj->getName()][$cc] = self::XMLToArray($dd,$level+1);
                    }
                 }
             }
           }
         }

       return $items;
    } 
}
?>
