<?php //aldwin
require_once '../config/Database.php';

class ProductController {
    private $conn;
    private $table = "products";

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }


    public function getProductsByShop($shopId) {
        $query = "SELECT p.*, c.nama_kategori 
                  FROM " . $this->table . " p 
                  JOIN categories c ON p.category_id = c.id 
                  WHERE p.shop_id = :shop_id ORDER BY p.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':shop_id', $shopId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    
    public function addProduct($shopId, $nama, $kategori, $harga, $stok, $deskripsi, $file) {
        
        $targetDir = "../assets/images/"; 
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = time() . '_' . basename($file["name"]);
        $targetFilePath = $targetDir . $fileName;
        $fileType = pathinfo($targetFilePath, PATHINFO_EXTENSION);
        
        
        $allowTypes = array('jpg','png','jpeg','gif'); 
        if(in_array(strtolower($fileType), $allowTypes)){
            if(move_uploaded_file($file["tmp_name"], $targetFilePath)){
                
               
                $query = "INSERT INTO " . $this->table . " 
                          (shop_id, category_id, nama_produk, deskripsi, harga, stok, gambar) 
                          VALUES (:shop_id, :cat_id, :nama, :desc, :harga, :stok, :img)";
                
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(':shop_id', $shopId);
                $stmt->bindParam(':cat_id', $kategori);
                $stmt->bindParam(':nama', $nama);
                $stmt->bindParam(':desc', $deskripsi);
                $stmt->bindParam(':harga', $harga);
                $stmt->bindParam(':stok', $stok);
                $stmt->bindParam(':img', $fileName);

                if($stmt->execute()){
                    return json_encode(['status' => 'success', 'message' => 'Produk berhasil ditambahkan!']);
                }
            }
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal upload gambar atau simpan data.']);
    }

    
    public function deleteProduct($id, $shopId) {
        
        $query = "DELETE FROM " . $this->table . " WHERE id = :id AND shop_id = :shop_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':shop_id', $shopId);

        if($stmt->execute()){
            return json_encode(['status' => 'success', 'message' => 'Produk berhasil dihapus.']);
        }
        return json_encode(['status' => 'error', 'message' => 'Gagal menghapus produk.']);
    }
}
?>