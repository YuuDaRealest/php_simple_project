<?php
require_once '../config/database.php';
require_once '../models/Member.php';

$database = new Database();
$db = $database->getConnection();
$member = new Member($db);

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$id = isset($_GET['id']) ? $_GET['id'] : 0;

$message = '';
$message_type = '';

// Xử lý các action
if($action == 'create' && $_POST) {
    $data = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address']
    ];
    
    if($member->create($data)) {
        $message = 'Thêm thành viên thành công!';
        $message_type = 'success';
    } else {
        $message = 'Có lỗi xảy ra khi thêm thành viên!';
        $message_type = 'danger';
    }
}

if($action == 'update' && $_POST) {
    $id = $_POST['id'];
    $data = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address']
    ];
    
    if($member->update($id, $data)) {
        $message = 'Cập nhật thành viên thành công!';
        $message_type = 'success';
    } else {
        $message = 'Có lỗi xảy ra khi cập nhật thành viên!';
        $message_type = 'danger';
    }
}

if($action == 'delete' && $id) {
    if($member->delete($id)) {
        $message = 'Xóa thành viên thành công!';
        $message_type = 'success';
    } else {
        $message = 'Có lỗi xảy ra khi xóa thành viên!';
        $message_type = 'danger';
    }
}

// Lấy danh sách thành viên
$stmt = $member->read();
$stmt->execute();
$members_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy thông tin thành viên để edit
$member_to_edit = null;
if($action == 'edit' && $id) {
    /** @var PDOStatement $edit_stmt */
    $edit_stmt = $member->readOne($id);
    $member_to_edit = $edit_stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý thành viên - Hệ thống thư viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .main-content {
            background-color: #f8f9fa;
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
                        <a class="nav-link text-white active" href="index.php">
                            <i class="fas fa-users"></i> Quản lý thành viên
                        </a>
                        <a class="nav-link text-white" href="../borrowings/index.php">
                            <i class="fas fa-exchange-alt"></i> Mượn/Trả sách
                        </a>
                        <a class="nav-link text-white" href="../reports/index.php">
                            <i class="fas fa-chart-bar"></i> Báo cáo
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1>Quản lý thành viên</h1>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#memberModal">
                            <i class="fas fa-plus"></i> Thêm thành viên
                        </button>
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
                                <input type="text" class="form-control" id="searchInput" placeholder="Tìm kiếm thành viên...">
                                <button class="btn btn-outline-secondary" type="button">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Members Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Họ tên</th>
                                            <th>Email</th>
                                            <th>Số điện thoại</th>
                                            <th>Địa chỉ</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($members_data as $member_item): ?>
                                            <tr>
                                                <td><?php echo $member_item['id']; ?></td>
                                                <td><?php echo htmlspecialchars($member_item['name']); ?></td>
                                                <td><?php echo htmlspecialchars($member_item['email']); ?></td>
                                                <td><?php echo htmlspecialchars($member_item['phone']); ?></td>
                                                <td><?php echo htmlspecialchars($member_item['address']); ?></td>
                                                <td>
                                                    <button class="btn btn-sm btn-warning" onclick="editMember(<?php echo htmlspecialchars(json_encode($member_item)); ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="?action=delete&id=<?php echo $member_item['id']; ?>" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('Bạn có chắc chắn muốn xóa thành viên này?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if(count($members_data) == 0): ?>
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">Chưa có thành viên nào</td>
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

    <!-- Member Modal -->
    <div class="modal fade" id="memberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="memberModalTitle">Thêm thành viên mới</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="?action=create">
                    <div class="modal-body">
                        <input type="hidden" name="id" id="memberId">
                        
                        <div class="mb-3">
                            <label for="name" class="form-label">Họ và tên *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại *</label>
                            <input type="tel" class="form-control" id="phone" name="phone" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">Địa chỉ</label>
                            <textarea class="form-control" id="address" name="address" rows="3"></textarea>
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
        function editMember(member) {
            document.getElementById('memberModalTitle').textContent = 'Chỉnh sửa thành viên';
            document.getElementById('memberId').value = member.id;
            document.getElementById('name').value = member.name;
            document.getElementById('email').value = member.email;
            document.getElementById('phone').value = member.phone;
            document.getElementById('address').value = member.address;
            document.querySelector('form').action = '?action=update';
            
            new bootstrap.Modal(document.getElementById('memberModal')).show();
        }

        document.getElementById('memberModal').addEventListener('hidden.bs.modal', function() {
            document.getElementById('memberModalTitle').textContent = 'Thêm thành viên mới';
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
