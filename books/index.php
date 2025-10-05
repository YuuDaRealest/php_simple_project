<?php
require_once '../config/database.php';
require_once '../models/Book.php';

$database = new Database();
$db = $database->getConnection();
$book = new Book($db);

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? $_GET['id'] : 0;

$message = '';
$message_type = '';

// Xử lý các action
if($action == 'create' && $_POST) {
    $book->title = $_POST['title'];
    $book->author = $_POST['author'];
    $book->isbn = $_POST['isbn'];
    $book->category = $_POST['category'];
    $book->published_year = $_POST['published_year'];
    $book->quantity = $_POST['quantity'];
    $book->available_quantity = $_POST['available_quantity'];
    
    if($book->create()) {
        $message = 'Thêm sách thành công!';
        $message_type = 'success';
    } else {
        $message = 'Có lỗi xảy ra khi thêm sách!';
        $message_type = 'danger';
    }
}

if($action == 'update' && $_POST) {
    $book->id = $_POST['id'];
    $book->title = $_POST['title'];
    $book->author = $_POST['author'];
    $book->isbn = $_POST['isbn'];
    $book->category = $_POST['category'];
    $book->published_year = $_POST['published_year'];
    $book->quantity = $_POST['quantity'];
    $book->available_quantity = $_POST['available_quantity'];
    
    if($book->update()) {
        $message = 'Cập nhật sách thành công!';
        $message_type = 'success';
    } else {
        $message = 'Có lỗi xảy ra khi cập nhật sách!';
        $message_type = 'danger';
    }
}

if($action == 'delete' && $id) {
    $book->id = $id;
    if($book->delete()) {
        $message = 'Xóa sách thành công!';
        $message_type = 'success';
    } else {
        $message = 'Có lỗi xảy ra khi xóa sách!';
        $message_type = 'danger';
    }
}

// Lấy danh sách sách
$stmt = $book->read();
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy thông tin sách để edit
$book_to_edit = null;
if($action == 'edit' && $id) {
    $book->id = $id;
    if($book->readOne()) {
        $book_to_edit = $book;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý sách - Hệ thống thư viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-3">
                    <h4 class="mb-4">
                        <i class="fas fa-book"></i> Thư viện
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link text-white" href="../index.php">
                            <i class="fas fa-home"></i> Trang chủ
                        </a>
                        <a class="nav-link text-white active" href="index.php">
                            <i class="fas fa-book"></i> Quản lý sách
                        </a>
                        <a class="nav-link text-white" href="../members/index.php">
                            <i class="fas fa-users"></i> Quản lý thành viên
                        </a>
                        <a class="nav-link text-white" href="../borrowings/index.php">  
                            <i class="fas fa-exchange-alt"></i> Mượn/Trả sách
                        </a>
                        <a class="nav-link text-white" href="reports/index.php">
                            <i class="fas fa-chart-bar"></i> Báo cáo
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Quản lý sách</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookModal">
                        <i class="fas fa-plus"></i> Thêm sách mới
                    </button>
                </div>

                <?php if($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Search -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" class="form-control" id="searchInput" placeholder="Tìm kiếm sách...">
                            <button class="btn btn-outline-secondary" type="button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Books Table -->
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Tên sách</th>
                                        <th>Tác giả</th>
                                        <th>ISBN</th>
                                        <th>Thể loại</th>
                                        <th>Năm xuất bản</th>
                                        <th>Tổng số</th>
                                        <th>Có sẵn</th>
                                        <th>Thao tác</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($books as $book_item): ?>
                                        <tr>
                                            <td><?php echo $book_item['id']; ?></td>
                                            <td><?php echo $book_item['title']; ?></td>
                                            <td><?php echo $book_item['author']; ?></td>
                                            <td><?php echo $book_item['isbn']; ?></td>
                                            <td><?php echo $book_item['category']; ?></td>
                                            <td><?php echo $book_item['published_year']; ?></td>
                                            <td><?php echo $book_item['quantity']; ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $book_item['available_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                                    <?php echo $book_item['available_quantity']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-warning" onclick="editBook(<?php echo htmlspecialchars(json_encode($book_item)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <a href="?action=delete&id=<?php echo $book_item['id']; ?>" 
                                                   class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Bạn có chắc chắn muốn xóa sách này?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Book Modal -->
    <div class="modal fade" id="bookModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bookModalTitle">Thêm sách mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?action=create">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="bookId">
                        <div class="mb-3">
                            <label for="title" class="form-label">Tên sách *</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="author" class="form-label">Tác giả *</label>
                            <input type="text" class="form-control" id="author" name="author" required>
                        </div>
                        <div class="mb-3">
                            <label for="isbn" class="form-label">ISBN</label>
                            <input type="text" class="form-control" id="isbn" name="isbn">
                        </div>
                        <div class="mb-3">
                            <label for="category" class="form-label">Thể loại</label>
                            <input type="text" class="form-control" id="category" name="category">
                        </div>
                        <div class="mb-3">
                            <label for="published_year" class="form-label">Năm xuất bản</label>
                            <input type="number" class="form-control" id="published_year" name="published_year" min="1000" max="2024">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="quantity" class="form-label">Tổng số lượng *</label>
                                    <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="available_quantity" class="form-label">Số lượng có sẵn *</label>
                                    <input type="number" class="form-control" id="available_quantity" name="available_quantity" min="0" required>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-primary">Lưu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editBook(book) {
            document.getElementById('bookModalTitle').textContent = 'Chỉnh sửa sách';
            document.getElementById('bookId').value = book.id;
            document.getElementById('title').value = book.title;
            document.getElementById('author').value = book.author;
            document.getElementById('isbn').value = book.isbn;
            document.getElementById('category').value = book.category;
            document.getElementById('published_year').value = book.published_year;
            document.getElementById('quantity').value = book.quantity;
            document.getElementById('available_quantity').value = book.available_quantity;
            
            // Thay đổi action của form
            document.querySelector('form').action = '?action=update';
            
            new bootstrap.Modal(document.getElementById('bookModal')).show();
        }

        // Reset form khi đóng modal
        document.getElementById('bookModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('bookModalTitle').textContent = 'Thêm sách mới';
            document.querySelector('form').action = '?action=create';
            document.querySelector('form').reset();
        });

        // Search functionality
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const tableRows = document.querySelectorAll('tbody tr');
            
            tableRows.forEach(row => {
                const text = row.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
