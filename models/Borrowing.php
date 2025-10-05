<?php
class Borrowing {
    private $conn;
    private $table_name = "borrowings";

    public $id;
    public $book_id;
    public $member_id;
    public $borrow_date;
    public $due_date;
    public $return_date;
    public $status;
    public $fine_amount;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Lấy tất cả giao dịch mượn sách
    public function read() {
        $query = "SELECT b.*, bk.title as book_title, m.name as member_name, m.membership_number
                  FROM " . $this->table_name . " b
                  LEFT JOIN books bk ON b.book_id = bk.id
                  LEFT JOIN members m ON b.member_id = m.id
                  ORDER BY b.borrow_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Lấy giao dịch mượn sách theo ID
    public function readOne() {
        $query = "SELECT b.*, bk.title as book_title, m.name as member_name, m.membership_number
                  FROM " . $this->table_name . " b
                  LEFT JOIN books bk ON b.book_id = bk.id
                  LEFT JOIN members m ON b.member_id = m.id
                  WHERE b.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if($row) {
            $this->book_id = $row['book_id'];
            $this->member_id = $row['member_id'];
            $this->borrow_date = $row['borrow_date'];
            $this->due_date = $row['due_date'];
            $this->return_date = $row['return_date'];
            $this->status = $row['status'];
            $this->fine_amount = $row['fine_amount'];
            return true;
        }
        return false;
    }

    // Mượn sách
    public function borrow() {
        // Kiểm tra sách có sẵn không
        $query = "SELECT available_quantity FROM books WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->book_id);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if($row['available_quantity'] <= 0) {
            return false; // Sách không có sẵn
        }

        // Tạo giao dịch mượn sách
        $query = "INSERT INTO " . $this->table_name . " 
                  SET book_id=:book_id, member_id=:member_id, borrow_date=:borrow_date, due_date=:due_date";
        
        $stmt = $this->conn->prepare($query);
        
        $this->book_id = htmlspecialchars(strip_tags($this->book_id));
        $this->member_id = htmlspecialchars(strip_tags($this->member_id));
        $this->borrow_date = htmlspecialchars(strip_tags($this->borrow_date));
        $this->due_date = htmlspecialchars(strip_tags($this->due_date));
        
        $stmt->bindParam(":book_id", $this->book_id);
        $stmt->bindParam(":member_id", $this->member_id);
        $stmt->bindParam(":borrow_date", $this->borrow_date);
        $stmt->bindParam(":due_date", $this->due_date);
        
        if($stmt->execute()) {
            // Giảm số lượng sách có sẵn
            $query = "UPDATE books SET available_quantity = available_quantity - 1 WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->book_id);
            $stmt->execute();
            return true;
        }
        return false;
    }

    // Trả sách
    public function return() {
        $query = "UPDATE " . $this->table_name . " 
                  SET return_date=:return_date, status='returned'
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);
        
        $this->return_date = htmlspecialchars(strip_tags($this->return_date));
        $this->id = htmlspecialchars(strip_tags($this->id));
        
        $stmt->bindParam(":return_date", $this->return_date);
        $stmt->bindParam(":id", $this->id);
        
        if($stmt->execute()) {
            // Tăng số lượng sách có sẵn
            $query = "UPDATE books SET available_quantity = available_quantity + 1 WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->book_id);
            $stmt->execute();
            return true;
        }
        return false;
    }

    // Lấy sách đang mượn của một thành viên
    public function getMemberBorrowings($member_id) {
        $query = "SELECT b.*, bk.title as book_title, bk.author
                  FROM " . $this->table_name . " b
                  LEFT JOIN books bk ON b.book_id = bk.id
                  WHERE b.member_id = ? AND b.status = 'borrowed'
                  ORDER BY b.borrow_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $member_id);
        $stmt->execute();
        return $stmt;
    }

    // Lấy sách quá hạn
    public function getOverdueBooks() {
        $query = "SELECT b.*, bk.title as book_title, m.name as member_name, m.membership_number
                  FROM " . $this->table_name . " b
                  LEFT JOIN books bk ON b.book_id = bk.id
                  LEFT JOIN members m ON b.member_id = m.id
                  WHERE b.due_date < CURDATE() AND b.status = 'borrowed'
                  ORDER BY b.due_date ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
