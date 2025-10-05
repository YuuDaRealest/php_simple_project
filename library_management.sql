CREATE DATABASE IF NOT EXISTS library_management;
USE library_management;

CREATE TABLE books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    category VARCHAR(100),
    published_year YEAR,
    quantity INT DEFAULT 1,
    available_quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    membership_number VARCHAR(20) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE borrowings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    member_id INT NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
    FOREIGN KEY (member_id) REFERENCES members(id) ON DELETE CASCADE
);

INSERT INTO books (title, author, isbn, category, published_year, quantity, available_quantity) VALUES
('The Great Gatsby', 'F. Scott Fitzgerald', '9780743273565', 'Fiction', 1925, 3, 3),
('To Kill a Mockingbird', 'Harper Lee', '9780061120084', 'Fiction', 1960, 2, 2),
('1984', 'George Orwell', '9780451524935', 'Dystopian Fiction', 1949, 2, 2),
('Pride and Prejudice', 'Jane Austen', '9780141439518', 'Romance', 1813, 1, 1),
('The Catcher in the Rye', 'J.D. Salinger', '9780316769174', 'Fiction', 1951, 2, 2);

INSERT INTO members (name, email, phone, address, membership_number) VALUES
('Nguyễn Văn An', 'an.nguyen@email.com', '0123456789', '123 Đường ABC, Quận 1, TP.HCM', 'MEM001'),
('Trần Thị Bình', 'binh.tran@email.com', '0987654321', '456 Đường XYZ, Quận 2, TP.HCM', 'MEM002'),
('Lê Văn Cường', 'cuong.le@email.com', '0369258147', '789 Đường DEF, Quận 3, TP.HCM', 'MEM003'),
('Phạm Thị Dung', 'dung.pham@email.com', '0741852963', '321 Đường GHI, Quận 4, TP.HCM', 'MEM004');

INSERT INTO borrowings (book_id, member_id, borrow_date, due_date, status) VALUES
(1, 1, '2024-01-15', '2024-02-15', 'borrowed'),
(2, 2, '2024-01-20', '2024-02-20', 'borrowed'),
(3, 3, '2024-01-10', '2024-02-10', 'returned'),
(4, 4, '2024-01-25', '2024-02-25', 'borrowed');
