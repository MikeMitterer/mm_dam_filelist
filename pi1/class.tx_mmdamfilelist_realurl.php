<?php
require_once(PATH_tslib.'class.tslib_content.php');

class tx_mmdamfilelist_realurl  {
		var $table_cat	= 'tx_dam_cat';
		var $table_dam  = 'tx_dam';
		var $prefix = 'category:';
		
        function category($params, $ref)    {
				//t3lib_div::debug($params,'category: $params');
				
                if ($params['decodeAlias'])     {

                        return $this->alias2category($params['value']);

                } else {

                        return $this->category2alias($params['value']);

                }

        }

        function details($params, $ref)    {
				//t3lib_div::debug($params,'details: $params');
				
                if ($params['decodeAlias'])     {

                        return $this->alias2uid($params['value']);

                } else {

                        return $this->uid2alias($params['value']);

                }

        }
        
        function uid2alias($value)       {
                $uid = intval($value);
                
                $where = "uid='$uid'" . tslib_cObj::enableFields($this->table_dam);
                //t3lib_div::debug($where,'where');
                
                $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title',$this->table_dam,$where);
                //t3lib_div::debug($result,'$result');
                if($result) {
                	$record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
                return $this->prepareAlias($uid,$record['title']); 	
                } 
                
                return $value;
        }
        
        function category2alias($value)       {

                if(strstr($value,$this->prefix) != false) {
                	list(,$uid) = explode(':',$value);
                	
                	$where = "uid='$uid'" . tslib_cObj::enableFields($this->table_cat);
                	//t3lib_div::debug($where,'where');
                	
                	$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,title',$this->table_cat,$where);
                	//t3lib_div::debug($result,'$result');
                	if($result) {
                		$record = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
                		return $this->prepareAlias($uid,$record['title']); 	
                	} 
                	
                } 
                
                return $value;

        }

        function alias2uid($value) {
        		//t3lib_div::debug($where,'where');
                if (preg_match('/.*__([0-9]+)$/',$value,$reg)) {
                    return $reg[1];
                }
                return $value;
        }
        
        
        function alias2category($value) {
        		//t3lib_div::debug($where,'where');
                if (preg_match('/.*__([0-9]+)$/',$value,$reg)) {
                    return $this->prefix . $reg[1];
                }
                return $value;
                }
        
		function prepareAlias($uid,$title) {
			$alias = strtolower(str_replace(' ','_',$title));
			$alias = urlencode($alias);
			$alias .= '__' . $uid;
			
			return $alias;
		}

}
?>