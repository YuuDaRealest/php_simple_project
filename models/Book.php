<?php
class Book {
    private $conn;
    private $table_name = "books";

    public $id;
    public $title;
    public $author;
    public $isbn;
    public $category;
    public $published_year;
    public $quantity;
    public $available_quantity;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy tất cả sách
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY title ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Lấy sách theo ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->title = $row['title'];
            $this->author = $row['author'];
            $this->isbn = $row['isbn'];
            $this->category = $row['category'];
            $this->published_year = $row['published_year'];
            $this->quantity = $row['quantity'];
            $this->available_quantity = $row['available_quantity'];
            return true;
        }
        return false;
    }

    // Tạo sách mới
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET title=:title, author=:author, isbn=:isbn, category=:category, 
                      published_year=:published_year, quantity=:quantity, available_quantity=:available_quantity";
        
        $stmt = $this->conn->prepare($query);
        
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->isbn = htmlspecialchars(strip_tags($this->isbn));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->published_year = htmlspecialchars(strip_tags($this->published_year));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->available_quantity = htmlspecialchars(strip_tags($this->available_quantity));
        
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":isbn", $this->isbn);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":published_year", $this->published_year);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":available_quantity", $this->available_quantity);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Cập nhật sách
    public function update() {
        $query = "UPDATE " . $this->table_name . " 
                  SET title=:title, author=:author, isbn=:isbn, category=:category, 
                      published_year=:published_year, quantity=:quantity, available_quantity=:available_quantity
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->author = htmlspecialchars(strip_tags($this->author));
        $this->isbn = htmlspecialchars(strip_tags($this->isbn));
        $this->category = htmlspecialchars(strip_tags($this->category));
        $this->published_year = htmlspecialchars(strip_tags($this->published_year));
        $this->quantity = htmlspecialchars(strip_tags($this->quantity));
        $this->available_quantity = htmlspecialchars(strip_tags($this->available_quantity));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":author", $this->author);
        $stmt->bindParam(":isbn", $this->isbn);
        $stmt->bindParam(":category", $this->category);
        $stmt->bindParam(":published_year", $this->published_year);
        $stmt->bindParam(":quantity", $this->quantity);
        $stmt->bindParam(":available_quantity", $this->available_quantity);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Xóa sách
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    // Tìm kiếm sách
    public function search($keyword) {
        $query = "SELECT * FROM " . $this->table_name . " 
                  WHERE title LIKE :keyword OR author LIKE :keyword OR category LIKE :keyword
                  ORDER BY title ASC";
        $stmt = $this->conn->prepare($query);
        $keyword = "%{$keyword}%";
        $stmt->bindParam(":keyword", $keyword);
        $stmt->execute();
        return $stmt;
    }
}
?>
