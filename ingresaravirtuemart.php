<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$bd_host = "db123456789.db.servidor.com";// 
$bd_usuario = "dbo123456789"; // 
$bd_password = "clavesecreta";
$bd_base = "db123456789";// 
$prefijo ="pocoy_";//
$con = mysql_connect($bd_host, $bd_usuario, $bd_password);
mysql_select_db($bd_base);//, $con
mysql_query("SET NAMES 'utf8'");
mysql_query("SET CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'");
//tablas---------------------------------------------
$tabla_productos=$prefijo."virtuemart_products_es_es";
$tabla_caracteristicas=$prefijo."virtuemart_products";
$tabla_categorias=$prefijo."virtuemart_product_categories";
$tabla_fabricantes=$prefijo."virtuemart_manufacturers_es_es";
$tabla_fabpublicar=$prefijo."virtuemart_manufacturers";
$tabla_produc_fabri=$prefijo."virtuemart_product_manufacturers";//relacion entre fabricantes y productos
$tabla_precios=$prefijo."virtuemart_product_prices";
$tabla_imagenes=$prefijo."virtuemart_medias";
$tabla_imag_produc=$prefijo."virtuemart_product_medias";//relacion entre imagenes y productos

$url="http://www.wmotion.net/blog/exportar_virtuamart.txt";// UBICACION DEL ARCHIVO DE TEXTOS
//recupero el último número de una tabla
function UltimoId($tablaeleg,$nombreid){
	$bd_host = "db123456789.db.servidor.com";// 
	$bd_usuario = "dbo123456789"; // 
	$bd_password = "clavesecreta";
	$bd_base = "db123456789";// 
	$con = mysql_connect($bd_host, $bd_usuario, $bd_password);
	mysql_select_db($bd_base);//, $con
	mysql_query("SET NAMES 'utf8'");
	mysql_query("SET CHARACTER SET 'utf8' COLLATE 'utf8_general_ci'");
	$sql35 = "SELECT * FROM $tablaeleg order by $nombreid DESC";
	$rs35 = mysql_query($sql35, $con);
	while($row = mysql_fetch_array($rs35))
		{
		 	$idultimo =$row["$nombreid"];//nombre de la pestaña	
		    break;
		
		}
	return $idultimo;
	
}

//funcion leer archivo csv----------------------------
function leer_contenido_completo($url){
   //abrimos el fichero, puede ser de texto o una URL
   $fichero_url = fopen ($url, "r");
   $texto = "";
   //bucle para ir recibiendo todo el contenido del fichero en bloques de 1024 bytes
   while ($trozo = fgets($fichero_url, 1024)){
      $texto .= $trozo;
   }
   return $texto;
}

$hoy = date("Y/m/d H:i"); //


$texto1=leer_contenido_completo($url);

echo"texto que lee=$texto1</br>";
//exit();

$dato=explode("$$", $texto1);
$cantdatos=count($dato);
for($i=0;$i<$cantdatos;$i++){

			$existe=2;
			$campos=explode("#", $dato[$i]);
			// busco si ya esta insertado el producto
			$campos[0] = utf8_encode(trim($campos[0]));
			$campos[1] = utf8_encode(trim($campos[1]));
			$campos[2] = utf8_encode(trim($campos[2]));
			if($campos[1]=="" ) $campos[1] =substr($campos[2], 0,105)."...";
			$sql3 = "SELECT * FROM $tabla_productos ";
			$rs3 = mysql_query($sql3, $con) ;
			while($row3 = mysql_fetch_array($rs3))
				{   
				$producto=$row3["product_name"];//nombre del producto
				if($producto==$campos[0])://existe en bd
					$existe=1;
				endif;
				}
			if($existe==2 and $campos[0]!="" and $campos[11]!=""):// que no exista el producto , que no sea vacio y que tenga una categoría
				$existe_fab=2;
				// busco si ya esta insertado el fabricante
				$sql3 = "SELECT * FROM $tabla_fabricantes ";
				$rs3 = mysql_query($sql3, $con) ;
				while($row3 = mysql_fetch_array($rs3))
					{   
					$fabricante=$row3["mf_name"];//nombre del fabricante
					$id_fabricante=$row3["virtuemart_manufacturer_id"];//nombre del fabricante
					if($fabricante==$campos[5])://existe en bd
						$existe_fab=1;
						break;
					endif;
					}
				if($existe_fab==2 and $campos[5]!="" ):
					//ingreso de fabricante---------------------------------------------------
					$id_fab=UltimoId($tabla_fabricantes, "virtuemart_manufacturer_id")+1;
					$nombre_fab=strtolower(str_replace(" ","-",$campos[5]));//reemplazo los caracteresen blanco por guiones
					$sql2= "INSERT INTO $tabla_fabricantes (virtuemart_manufacturer_id,mf_name,slug)";
					$sql2.=" VALUES ('$id_fab','$campos[5]','$nombre_fab') ";
					$rs2 = mysql_query($sql2, $con) ;
					$ver.="--</br>".$sql2;
					//ingreso de fabricante publicar---------------------------------------------------
					$sql2= "INSERT INTO $tabla_fabpublicar (virtuemart_manufacturer_id,published)";
					$sql2.=" VALUES ('$id_fab','1') ";
					$rs2 = mysql_query($sql2, $con) ;
					$ver.="--</br>".$sql2;
				else:
					$id_fab=$id_fabricante;
				endif;
				//ingreso de producto---------------------------------------------------
				$id_pro=UltimoId($tabla_productos, "virtuemart_product_id")+1;
				$nombre_pro=strtolower(str_replace(" ","-",$campos[0]));//reemplazo los caracteres en blanco por guiones
				if($campos[3]=="") $campos[3]=$campos[1];
				if($campos[4]=="") $campos[4]=$campos[0];
				$sql2= "INSERT INTO $tabla_productos (virtuemart_product_id,product_name,product_s_desc,product_desc,metadesc,metakey,slug)";
				$sql2.=" VALUES ('$id_pro','$campos[0]','$campos[1]','$campos[2]','$campos[3]','$campos[4]','$nombre_pro') ";
				$rs2 = mysql_query($sql2, $con) ;	
				$ver.="--</br>".$sql2;			
				//ingreso de caracteristicas del producto---------------------------------------------------
				$id_car=UltimoId($tabla_caracteristicas, "virtuemart_product_id")+1;
				$param='min_order_level=""|max_order_level=""|product_box=""|';
				$sql2= "INSERT INTO $tabla_caracteristicas (virtuemart_product_id,virtuemart_vendor_id,product_sku,product_weight_uom,product_lwh_uom,product_in_stock,product_special,product_unit,product_params,published)";
				$sql2.=" VALUES ('$id_car','1','$campos[0]','KG','M','$campos[10]','1','KG','$param','1') ";
				$rs2 = mysql_query($sql2, $con) ;
				$ver.="--</br>".$sql2;
				//ingreso de relacion producto-categoria---------------------------------------------------
				$catego=explode(",", $campos[11]);
				$cantcate=count($catego);
				for($j=0;$j<$cantcate;$j++){
					if($cantcate[$j]!=""):
						$id_cat=UltimoId($tabla_categorias, "virtuemart_product_id")+1;
						$sql2= "INSERT INTO $tabla_categorias (id,virtuemart_product_id,virtuemart_category_id)";
						$sql2.=" VALUES ('$id_cat','$id_pro','$cantcate[$j]') ";
						$rs2 = mysql_query($sql2, $con) ;
						$ver.="--</br>".$sql2;
					endif;	
				}
				//ingreso del precio---------------------------------------------------
				$id_pre=UltimoId($tabla_precios, "virtuemart_product_price_id")+1;
				$sql2= "INSERT INTO $tabla_precios (virtuemart_product_price_id,virtuemart_product_id,product_price,product_tax_id,product_currency)";
				$sql2.=" VALUES ('$id_pre','$id_pro','$campos[6]','$campos[7]','47') ";
				$rs2 = mysql_query($sql2, $con) ;	
				$ver.="--</br>".$sql2;			
				if($campos[5]!="" ):
					//ingreso de relacion con producto
					$id_pro_fab=UltimoId($tabla_produc_fabri, "id")+1;
					$sql2= "INSERT INTO $tabla_produc_fabri (id,virtuemart_product_id,virtuemart_manufacturer_id)";
					$sql2.=" VALUES ('$id_pro_fab','$id_pro','$id_fab') ";
					$rs2 = mysql_query($sql2, $con) ;
					$ver.="--</br>".$sql2;
				endif;
				if($campos[8]!=""):
					//ingreso de imagenes---------------------------------------------------
					$id_img=UltimoId($tabla_imagenes, "virtuemart_media_id")+1;
					$img1="images/stories/virtuemart/product/".$campos[8];//imagen normal
					$img2="images/stories/virtuemart/product/resized/".$campos[9];//imagen miniatura
					$sql2= "INSERT INTO $tabla_imagenes (virtuemart_media_id,file_meta,virtuemart_vendor_id,file_title,file_type,file_url,file_url_thumb,published,file_mimetype,file_params)";
					$sql2.=" VALUES ('$id_img','$campos[0]','1','$campos[8]','product','$img1','$img2','1','image/jpeg','') ";
					$rs2 = mysql_query($sql2, $con) ;
					$ver.="--</br>".$sql2;
					//ingreso de relacion con producto
					$id_pro_img=UltimoId($tabla_imag_produc, "id")+1;
					$sql2= "INSERT INTO $tabla_imag_produc (id,virtuemart_product_id,virtuemart_media_id)";
					$sql2.=" VALUES ('$id_pro_img','$id_pro','$id_img') ";
					$rs2 = mysql_query($sql2, $con) ;
					$ver.="--</br>".$sql2;
				endif;	

			endif;


}
echo" datos:</br>".$ver;
?>
