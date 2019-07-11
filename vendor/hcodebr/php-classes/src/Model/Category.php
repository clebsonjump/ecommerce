<?php 

namespace Hcode\Model;

use \Hcode\DB\Sql;
use \Hcode\Model;

class Category extends Model {

	
	public static function listAll(){
		$sql = new Sql();
		return $sql->select("SELECT * FROM tb_categories ORDER BY descategory");
	}

	public function save(){
		$sql = new Sql();
		
		$results = $sql->select("CALL db_ecommerce.sp_categories_save (:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()			
		));

		$this->setData($results[0]);

		Category::updatefile();
	}

	public function get($idcategory){
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM tb_categories WHERE idcategory = :idcategory",[
			':idcategory'=>$idcategory
		]);
		$this->setData($results[0]);
	}

	public function delete(){
		$sql = new Sql();
		$sql->query("DELETE FROM tb_categories WHERE idcategory = :idcategory",[
			':idcategory'=>$this->getidcategory()
		]);
		
		Category::updatefile();
	}

	public static function updatefile(){
		$categories = Category::listAll();

		$html = [];
		foreach ($categories as $row) {
			array_push($html, '<li><a href="/ecommerce/views/categories/'.$row['idcategory'].'">'.$row['descategory'].'</a></li>');
		}
		file_put_contents($_SERVER["DOCUMENT_ROOT"].DIRECTORY_SEPARATOR."ecommerce".DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode('',$html));
	}

	public function getProducts($related = true){
		$sql = new Sql();
		if ($related === true){

			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct IN(
				SELECT a.idproduct
				FROM tb_products a
				INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
				WHERE b.idcategory = :idcategory
			);
		", [':idcategory'=>$this->getidcategory()
		]);

		} else {

			return $sql->select("
				SELECT * FROM tb_products WHERE idproduct NOT IN(
				SELECT a.idproduct
				FROM tb_products a
				INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
				WHERE b.idcategory = :idcategory
			);
		", [':idcategory'=>$this->getidcategory()
		]);

		}
	}

	public function getProductsPage($page = 1, $itemsPerPage = 3){

		$sql = new Sql();
		$sql->select("
			SELECT SQL_CALC_FOUND_ROWS *
			FROM tb_products a
			INNER JOIN tb_productscategories b ON a.idproduct = b.idproduct
			INNER JOIN tb_categories c ON c.idcategory = b.idcategory
			WHERE c.idcategory = 3
			LIMIT 0, 3;

			SELECT FOUND_ROWS() AS nrtotal;
			");
	}

	public function addProduct(Product $product){

		$sql = new Sql();
		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) VALUES(:idcategory, :idproduct)",[
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);

	}

	public function removeProduct(Product $product){

		$sql = new Sql();
		$sql->query("DELETE FROM tb_productscategories WHERE idcategory = :idcategory AND idproduct = :idproduct",[
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
		]);

	}
}

 ?>