<?php
require_once '../config/database.php';
require_once '../models/Book.php';
require_once '../models/Member.php';
require_once '../models/Borrowing.php';

$database = new Database();
$db = $database->getConnection();
$book = new Book($db);
$member = new Member($db);
$borrowing = new Borrowing($db);

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? $_GET['id'] : 0;

$message = '';
$message_type = '';

// Xử lý success message từ URL parameter
if(isset($_GET['success']) && $_GET['success'] == 'return') {
    $message = 'Trả sách thành công!';
    $message_type = 'success';
}

// Xử lý các action
if($action == 'create' && $_POST) {
    $data = [
        'book_id' => $_POST['book_id'],
        'member_id' => $_POST['member_id'],
        'borrow_date' => $_POST['borrow_date'],
        'due_date' => $_POST['due_date']
    ];
    
    if($borrowing->create($data)) {
        $message = 'Thêm giao dịch mượn sách thành công!';
        $message_type = 'success';
    } else {
        $message = 'Có lỗi xảy ra khi thêm giao dịch mượn sách! Sách có thể không có sẵn.';
        $message_type = 'danger';
    }
}

if($action == 'update' && $_POST) {
    $id = $_POST['id'];
    $data = [
        'book_id' => $_POST['book_id'],
        'member_id' => $_POST['member_id'],
        'borrow_date' => $_POST['borrow_date'],
        'due_date' => $_POST['due_date'],
        'return_date' => $_POST['return_date'] ?? null,
        'status' => $_POST['status']
    ];
    
    if($borrowing->update($id, $data)) {
        $message = 'Cập nhật giao dịch mượn sách thành công!';
        $message_type = 'success';
    } else {
        $message = 'Có lỗi xảy ra khi cập nhật giao dịch mượn sách!';
        $message_type = 'danger';
    }
}

if($action == 'return' && $_POST) {
    $id = $_POST['borrowing_id'];
    $return_date = isset($_POST['return_date']) ? $_POST['return_date'] : null;
    
    // Debug thông tin
    error_log("Return action - ID: $id, Return date: " . ($return_date ?: 'current date'));
    
    if($borrowing->returnBook($id, $return_date)) {
        $message = 'Trả sách thành công!';
        $message_type = 'success';
        
        // Redirect để tránh resubmit form
        header("Location: " . $_SERVER['PHP_SELF'] . "?success=return");
        exit;
    } else {
        $message = 'Có lỗi xảy ra khi trả sách! Kiểm tra console để xem chi tiết lỗi.';
        $message_type = 'danger';
        error_log("Return book failed - ID: $id, Return date: " . ($return_date ?: 'current date'));
    }
}

if($action == 'delete' && $id) {
    if($borrowing->delete($id)) {
        $message = 'Xóa giao dịch thành công!';
        $message_type = 'success';
    } else {
        $message = 'Có lỗi xảy ra khi xóa giao dịch!';
        $message_type = 'danger';
    }
}

// Lấy danh sách giao dịch mượn sách
$stmt = $borrowing->read();
$borrowings_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách sách và thành viên cho dropdown
$books_stmt = $book->read();
$books_data = $books_stmt->fetchAll(PDO::FETCH_ASSOC);

$members_stmt = $member->read();
$members_data = $members_stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy thông tin giao dịch để edit
$borrowing_to_edit = null;
if($action == 'edit' && $id) {
    /** @var PDOStatement $edit_stmt */
    $edit_stmt = $borrowing->readOne($id);
    $borrowing_to_edit = $edit_stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý mượn sách - Hệ thống thư viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .status-badge {
            font-size: 0.8em;
        }
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
                    <h4 class="text-white mb-4">
                        <i class="fas fa-book"></i> Thư viện
                    </h4>
                    <nav class="nav flex-column">
                        <a class="nav-link text-white" href="../index.php">
                            <i class="fas fa-home"></i> Trang chủ
                        </a>
                        <a class="nav-link text-white" href="../books/index.php">
                            <i class="fas fa-book"></i> Quản lý sách
                        </a>
                        <a class="nav-link text-white" href="../members/index.php">
                            <i class="fas fa-users"></i> Quản lý thành viên
                        </a>
                        <a class="nav-link text-white active" href="index.php">
                            <i class="fas fa-exchange-alt"></i> Mượn/Trả sách
                        </a>
                        <a class="nav-link text-white" href="../reports/index.php">
                            <i class="fas fa-chart-bar"></i> Báo cáo
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1>Quản lý mượn sách</h1>
                        <div>

                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#borrowingModal">
                                <i class="fas fa-plus"></i> Mượn sách
                            </button>
                        </div>
                    </div>

                    <!-- Message -->
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
                                <input type="text" class="form-control" id="searchInput" placeholder="Tìm kiếm giao dịch mượn sách...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Borrowings Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Sách</th>
                                            <th>Thành viên</th>
                                            <th>Ngày mượn</th>
                                            <th>Hạn trả</th>
                                            <th>Ngày trả</th>
                                            <th>Trạng thái</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (count($borrowings_data) > 0): ?>
                                            <?php foreach ($borrowings_data as $borrowing_item): ?>
                                                <?php
                                                $is_overdue = $borrowing_item['status'] === 'borrowed' && 
                                                           strtotime($borrowing_item['due_date']) < time();
                                                $status_class = $borrowing_item['status'] === 'returned' ? 'success' : 
                                                               ($is_overdue ? 'danger' : 'warning');
                                                $status_text = $borrowing_item['status'] === 'returned' ? 'Đã trả' : 
                                                              ($is_overdue ? 'Quá hạn' : 'Đang mượn');
                                                ?>
                                                <tr class="<?php echo $is_overdue ? 'table-danger' : ''; ?>">
                                                    <td><?php echo $borrowing_item['id']; ?></td>
                                                    <td>
                                                        <strong><?php echo htmlspecialchars($borrowing_item['book_title']); ?></strong><br>
                                                        <small class="text-muted"><?php echo htmlspecialchars($borrowing_item['author']); ?></small>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($borrowing_item['member_name']); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($borrowing_item['borrow_date'])); ?></td>
                                                    <td><?php echo date('d/m/Y', strtotime($borrowing_item['due_date'])); ?></td>
                                                    <td>
                                                        <?php echo $borrowing_item['return_date'] ? 
                                                              date('d/m/Y', strtotime($borrowing_item['return_date'])) : 
                                                              '<span class="text-muted">Chưa trả</span>'; ?>
                                                    </td>
                                                    <td>
                                                        <span class="badge bg-<?php echo $status_class; ?> status-badge">
                                                            <?php echo $status_text; ?>
                                                        </span>
                                                        <?php if ($is_overdue): ?>
                                                            <br><small class="text-danger">
                                                                <i class="fas fa-exclamation-triangle"></i>
                                                                <?php echo floor((time() - strtotime($borrowing_item['due_date'])) / 86400); ?> ngày quá hạn
                                                            </small>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-warning" 
                                                                onclick="editBorrowing(<?php echo htmlspecialchars(json_encode($borrowing_item)); ?>)">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <?php if ($borrowing_item['status'] === 'borrowed'): ?>
                                                            <button type="button" class="btn btn-sm btn-success" 
                                                                    onclick="quickReturn(<?php echo $borrowing_item['id']; ?>)"
                                                                    title="Trả sách">
                                                                <i class="fas fa-undo"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <a href="?action=delete&id=<?php echo $borrowing_item['id']; ?>" 
                                                           class="btn btn-sm btn-danger"
                                                           onclick="return confirm('Bạn có chắc chắn muốn xóa giao dịch này?')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="8" class="text-center text-muted">Chưa có giao dịch mượn sách nào</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Borrowing Modal -->
    <div class="modal fade" id="borrowingModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="borrowingModalTitle">Mượn sách mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?action=create">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="borrowingId">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="book_id" class="form-label">Sách *</label>
                                    <select class="form-select" id="book_id" name="book_id" required>
                                        <option value="">Chọn sách</option>
                                        <?php foreach ($books_data as $book_item): ?>
                                            <option value="<?php echo $book_item['id']; ?>">
                                                <?php echo htmlspecialchars($book_item['title'] . ' - ' . $book_item['author']); ?>
                                                (Có sẵn: <?php echo $book_item['available_quantity']; ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="member_id" class="form-label">Thành viên *</label>
                                    <select class="form-select" id="member_id" name="member_id" required>
                                        <option value="">Chọn thành viên</option>
                                        <?php foreach ($members_data as $member_item): ?>
                                            <option value="<?php echo $member_item['id']; ?>">
                                                <?php echo htmlspecialchars($member_item['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="borrow_date" class="form-label">Ngày mượn *</label>
                                    <input type="date" class="form-control" id="borrow_date" name="borrow_date" 
                                           value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="due_date" class="form-label">Hạn trả *</label>
                                    <input type="date" class="form-control" id="due_date" name="due_date" 
                                           value="<?php echo date('Y-m-d', strtotime('+14 days')); ?>" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3" id="return_date_group" style="display: none;">
                            <label for="return_date" class="form-label">Ngày trả</label>
                            <input type="date" class="form-control" id="return_date" name="return_date">
                        </div>
                        
                        <div class="mb-3" id="status_group" style="display: none;">
                            <label for="status" class="form-label">Trạng thái</label>
                            <select class="form-select" id="status" name="status">
                                <option value="borrowed">Đang mượn</option>
                                <option value="returned">Đã trả</option>
                            </select>
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

    <!-- Return Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="?action=return">
                    <div class="modal-header">
                        <h5 class="modal-title">Trả sách</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="return">
                        <input type="hidden" name="borrowing_id" id="returnBorrowingId">
                        
                        <div class="mb-3">
                            <label for="return_date_modal" class="form-label">Ngày trả *</label>
                            <input type="date" class="form-control" id="return_date_modal" name="return_date" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            <strong>Thông tin giao dịch:</strong>
                            <div id="returnInfo"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        <button type="submit" class="btn btn-success">Xác nhận trả sách</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editBorrowing(borrowing) {
            document.getElementById('borrowingModalTitle').textContent = 'Chỉnh sửa giao dịch mượn sách';
            document.getElementById('borrowingId').value = borrowing.id;
            document.getElementById('book_id').value = borrowing.book_id;
            document.getElementById('member_id').value = borrowing.member_id;
            document.getElementById('borrow_date').value = borrowing.borrow_date;
            document.getElementById('due_date').value = borrowing.due_date;
            document.getElementById('return_date').value = borrowing.return_date || '';
            document.getElementById('status').value = borrowing.status;
            
            // Show/hide fields based on action
            document.getElementById('return_date_group').style.display = 'block';
            document.getElementById('status_group').style.display = 'block';
            
            // Thay đổi action của form trong borrowing modal (không ảnh hưởng đến return modal)
            const borrowingForm = document.querySelector('#borrowingModal form');
            if (borrowingForm) {
                borrowingForm.action = '?action=update';
            }
            
            new bootstrap.Modal(document.getElementById('borrowingModal')).show();
        }
        
        function quickReturn(borrowingId) {
            console.log('quickReturn called with ID:', borrowingId);
            
            // Validate input
            if (!borrowingId || borrowingId <= 0) {
                console.error('Invalid borrowing ID:', borrowingId);
                alert('ID giao dịch không hợp lệ!');
                return;
            }
            
            document.getElementById('returnBorrowingId').value = borrowingId;
            
            // Find the borrowing info
            const returnButton = document.querySelector(`button[onclick*="quickReturn(${borrowingId})"]`);
            if (!returnButton) {
                console.error('Return button not found for ID:', borrowingId);
                alert('Không tìm thấy thông tin giao dịch!');
                return;
            }
            
            const row = returnButton.closest('tr');
            if (!row) {
                console.error('Row not found for borrowing ID:', borrowingId);
                alert('Không tìm thấy dòng dữ liệu!');
                return;
            }
            
            const bookTitle = row.cells[1].querySelector('strong')?.textContent || 'N/A';
            const memberName = row.cells[2].textContent || 'N/A';
            const dueDate = row.cells[4].textContent || 'N/A';
            
            document.getElementById('returnInfo').innerHTML = `
                <strong>Sách:</strong> ${bookTitle}<br>
                <strong>Thành viên:</strong> ${memberName}<br>
                <strong>Hạn trả:</strong> ${dueDate}
            `;
            
            // Set today's date as default return date
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('return_date_modal').value = today;
            
            console.log('Opening modal for borrowing ID:', borrowingId);
            var modal = new bootstrap.Modal(document.getElementById('returnModal'));
            modal.show();
        }
        
        // Reset form khi đóng modal
        document.getElementById('borrowingModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('borrowingModalTitle').textContent = 'Mượn sách mới';
            // Chỉ reset form trong borrowing modal
            const borrowingForm = document.querySelector('#borrowingModal form');
            if (borrowingForm) {
                borrowingForm.action = '?action=create';
                borrowingForm.reset();
            }
            document.getElementById('return_date_group').style.display = 'none';
            document.getElementById('status_group').style.display = 'none';
        });
        
        // Auto-set due date when borrow date changes
        document.getElementById('borrow_date').addEventListener('change', function() {
            const borrowDate = new Date(this.value);
            const dueDate = new Date(borrowDate);
            dueDate.setDate(dueDate.getDate() + 14);
            document.getElementById('due_date').value = dueDate.toISOString().split('T')[0];
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
