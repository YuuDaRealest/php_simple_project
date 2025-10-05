<?php
class Member {
    private $conn;
    private $table_name = "members";

    public $id;
    public $name;
    public $email;
    public $phone;
    public $address;
    public $membership_number;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy tất cả thành viên
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY id DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Lấy thành viên theo ID
    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        $stmt->execute();
        
        return $stmt;
    }

    // Tạo thành viên mới
    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, email=:email, phone=:phone, address=:address, membership_number=:membership_number";
        
        $stmt = $this->conn->prepare($query);
        
        $name = htmlspecialchars(strip_tags($data['name']));
        $email = htmlspecialchars(strip_tags($data['email']));
        $phone = htmlspecialchars(strip_tags($data['phone']));
        $address = htmlspecialchars(strip_tags($data['address'] ?? ''));
        $membership_number = $this->generateMembershipNumber();
        
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":membership_number", $membership_number);
        
        return $stmt->execute();
    }

    // Cập nhật thành viên
    public function update($id, $data) {
        $query = "UPDATE " . $this->table_name . " 
                  SET name=:name, email=:email, phone=:phone, address=:address
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $name = htmlspecialchars(strip_tags($data['name']));
        $email = htmlspecialchars(strip_tags($data['email']));
        $phone = htmlspecialchars(strip_tags($data['phone']));
        $address = htmlspecialchars(strip_tags($data['address'] ?? ''));
        
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":email", $email);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":address", $address);
        $stmt->bindParam(":id", $id);
        
        return $stmt->execute();
    }

    // Xóa thành viên
    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        
        return $stmt->execute();
    }

    // Tạo mã thành viên tự động
    public function generateMembershipNumber() {
        $query = "SELECT COUNT(*) as count FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $row['count'] + 1;
        return "MEM" . str_pad($count, 3, '0', STR_PAD_LEFT);
    }
}
?>
