<?
/*
Change the max_execution_time = 36000 in php.ini file based on no of records to be updated
*/
include ('configs/config.php');


$SelectQry ="SELECT `SKU`,`Title`,`UPC`,`MPN`,`Price`,`Quantity`,`ItemCondition`,`Weight`,`Height`,`Description`,`MagentoCategoryID` 
			FROM `edealer_toadd_magento`";


//$SelectQry ="SELECT `SKU`,`Title`,`UPC`,`MPN`,`Price`,`Quantity`,`ItemCondition`,`Weight`,`Height`,`Description` 
//			FROM `edealer_toadd_magento` WHERE `SKU`='EVGA GTX TITAN Z-Superclocked AN'";
			
//$SelectQry ="SELECT `SKU`,`Title`,`UPC`,`MPN`,`Price`,`Quantity`,`ItemCondition`,`Weight`,`Height`,`Description` 
//			FROM `edealer_toadd_magento` WHERE `SKU`='Tommy Bahama Sunglasses Gunmetal Blue AN'";
			
$ResultQry = mysql_query($SelectQry,$con) or die(mysql_error());
$num = mysql_num_rows($ResultQry);
//echo $num."<br />";
while($Row = mysql_fetch_array($ResultQry))
{
	$SKU =  mb_convert_encoding($Row['SKU'], "UTF-8");
	$Title = mb_convert_encoding($Row['Title'], "UTF-8");
	$UPC = $Row['UPC'];
	$MPN = $Row['MPN'];
	$Price = $Row['Price'];
	$Quantity = $Row['Quantity'];
	$ItemCondition = $Row['ItemCondition'];
	$Height = $Row['Height'];
	$Weight = $Row['Weight'];
	$Description = $Row['Description'];
	$MagentoCategoryID = $Row['MagentoCategoryID'];

//	echo $SKU."<br />".$Row['Description']."<br />";
	echo $SKU."<br />";

	$a=AddProductViaSoapV1($SKU,$Title,$UPC,$MPN,$Price,$Quantity,$ItemCondition,$Height,$Weight,$Description,$MagentoCategoryID);
	echo $a."<br />";
}
	

function AddProductViaSoapV1($SKU,$Title,$UPC,$MPN,$Price,$Quantity,$ItemCondition,$Height,$Weight,$Description,$MagentoCategoryID)
	{
		try
		{
		$client = new SoapClient('http://164.40.134.117/eshopv2/index.php/api/soap/?wsdl');
		
		// If some stuff requires api authentification,
		// then get a session token
		$session = $client->login('me', 'welcome');
		
		// get attribute set
		$attributeSets = $client->call($session, 'product_attribute_set.list');
//		var_dump($attributeSets);
//		$attributeSet = current($attributeSets);
//		var_dump($attributeSet);
		$attributeSet = $attributeSets[12];
		//var_dump($attributeSet);
		
		
		$result = $client->call($session, 'catalog_product.create', array('simple', $attributeSet['set_id'], $SKU,array(
			'name' => $Title,
			'categories' => array($MagentoCategoryID),
			'websites' => array(1),
			'description' => $Description,
			'short_description' => ' ',
			'weight' => '10',
			'status' => '1',
			'visibility' => '4',
			'price' => $Price,
			'tax_class_id' => 2,
			'upc'=>$UPC,
			'mpn'=>$MPN,
			'height'=>$Height,
			'itemcondition'=>$ItemCondition)));
	
	$client->call($session, 'product_stock.update', array($SKU, array('qty'=>$Quantity, 'is_in_stock'=>1,'manage_stock'=>1)));
	
		}catch(SoapFault $e)
		{
			echo $e->faultcode."<br />"; 
			echo $e->faultstring;
		}
		
		
		
		/******************Uploading Images ***************************************/
		
//			if($result)
//			{
//				
////				$remove_result = $client->catalogProductAttributeMediaRemove($session, $result, 'No image');
//				
//				
//				for($i=sizeof($files['name']);$i>=0;$i--)
//				{
//					
//				if(!empty($files['tmp_name'][$i]))
//				{					
//					$file = array(
//								'file' => array(
//									'name' => $files['name'][$i],
//									'content' => base64_encode(file_get_contents($files['tmp_name'][$i])),
//									'mime'    => 'image/jpeg'
//									),
//								 'label' => $i, 
//								 'position' => $i, 
//								 'types' => array('image','small_image','thumbnail'), 
//								 'exclude' => 0);
//								 
//					
//					
//					
//					$result2 = $client->call($session,'catalog_product_attribute_media.create',
//								array($result,$file));
//					
////					$result2 = $client->catalogProductAttributeMediaCreate($session,$result,$file);
//				}
////		echo $result2;
//				}
//			}
		
		/****************** End of Uploading Images ***************************************/
		
		
		$attri = new stdClass();
		$attri->additional_attributes = array('upc','mpn','height','itemcondition');
		
//		$result4 = $client->catalogProductInfo($session, $result,NULL,$attri);
		return $result;
	}
	
	
function AddProductViaSoapV2($SKU,$Title,$UPC,$MPN,$Price,$Quantity,$ItemCondition,$Height,$Weight,$Description)
	{
		$client = new SoapClient('http://164.40.134.117/eshopv2/index.php/api/v2_soap?wsdl=1');
		
		// If some stuff requires api authentification,
		// then get a session token
		$session = $client->login('me', 'welcome');
		
		// get attribute set
		$attributeSets = $client->catalogProductAttributeSetList($session);
//		var_dump($attributeSets);
		$attributeSet = current($attributeSets);
		
		
		$productData = new stdClass();
		$additionalAttrs = array();
		
		$upcnew = new stdClass();
		$upcnew->key = "upc";
		$upcnew->value = $Upc;
		$additionalAttrs['single_data'][] = $upcnew;
		
		$manufacturernew = new stdClass();
		$manufacturernew->key = "manufacturer";
		$manufacturernew->value = $Manufacturer;
		$additionalAttrs['single_data'][] = $manufacturernew;
		
		$ingredientsnew = new stdClass();
		$ingredientsnew->key = "ingredients";
		$ingredientsnew->value = $Ingredients;
		$additionalAttrs['single_data'][] = $ingredientsnew;
		
		$benefitsnew = new stdClass();
		$benefitsnew->key = "benefits";
		$benefitsnew->value = $Benefits;
		$additionalAttrs['single_data'][] = $benefitsnew;
		
		$directionsnew = new stdClass();
		$directionsnew->key = "directions";
		$directionsnew->value = $Directions;
		$additionalAttrs['single_data'][] = $directionsnew;
//		var_dump($additionalAttrs);
		
		$productData->name                   = $Title; 
		$productData->description            = $Description;
		$productData->short_description      = 'short_description';
		$productData->weight                 = 2;
		$productData->status                 = 1; // 1 = active
		$productData->visibility             = 4; //visible in search/catalog
		$productData->category_ids           = $CategoryIds; 
		$productData->price                  = $Price;
		$productData->tax_class_id           = 4; // 2=standard
		$productData->meta_title			 = $MetaTitle;
		$productData->meta_keyword 			 = $MetaKeyword;
		$productData->meta_description 		 = $MetaDesc;
		$productData->additional_attributes  = $additionalAttrs;
		
		// Create new product
		try {
			$result =$client->catalogProductCreate($session, 'simple', $attributeSet->set_id, $SKU, $productData); // 9 is courses
		} catch (SoapFault $e) {
			print $e->getMessage();  //Internal Error. Please see log for details.
			exit();
		}
		
		
		
		/******************Uploading Images ***************************************/
		
			if($result)
			{
				
//				$remove_result = $client->catalogProductAttributeMediaRemove($session, $result, 'No image');
				
				
				for($i=sizeof($files['name']);$i>=0;$i--)
				{
					
				if(!empty($files['tmp_name'][$i]))
				{					
					$file = array(
								'file' => array(
									'name' => $files['name'][$i],
									'content' => base64_encode(file_get_contents($files['tmp_name'][$i])),
									'mime'    => 'image/jpeg'
									),
								 'label' => $i, 
								 'position' => $i, 
								 'types' => array('image','small_image','thumbnail'), 
								 'exclude' => 0);
								 
					
					$result2 = $client->catalogProductAttributeMediaCreate($session,$result,$file);
				}
//		echo $result2;
				}
			}
		
		/****************** End of Uploading Images ***************************************/
		
		
		$attri = new stdClass();
		$attri->additional_attributes = array('upc','manufacturer','ingredients','benefits','directions');
		
		$result4 = $client->catalogProductInfo($session, $result,NULL,$attri);
		return $result;
	}
	
		


if($value){echo "Product Successfully added Product ID:".$value;}
