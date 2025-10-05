<?php
require_once 'config/database.php';
require_once 'models/Book.php';
require_once 'models/Member.php';
require_once 'models/Borrowing.php';

$database = new Database();
$db = $database->getConnection();

$book = new Book($db);
$member = new Member($db);
$borrowing = new Borrowing($db);

// Lấy thống kê
$books_count = $book->read()->rowCount();
$members_count = $member->read()->rowCount();
$borrowings_count = $borrowing->read()->rowCount();
$overdue_count = $borrowing->getOverdueBooks()->rowCount();

// Lấy sách mới nhất
$latest_books = $book->read();
$latest_books->execute();
$latest_books_data = $latest_books->fetchAll(PDO::FETCH_ASSOC);
$latest_books_data = array_slice($latest_books_data, 0, 5);

// Lấy giao dịch mượn sách gần đây
$recent_borrowings = $borrowing->read();
$recent_borrowings->execute();
$recent_borrowings_data = $recent_borrowings->fetchAll(PDO::FETCH_ASSOC);
$recent_borrowings_data = array_slice($recent_borrowings_data, 0, 5);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hệ thống quản lý thư viện</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .card-stat {
            transition: transform 0.2s;
        }
        .card-stat:hover {
            transform: translateY(-5px);
        }
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
                        <a class="nav-link text-white active" href="index.php">
                            <i class="fas fa-home"></i> Trang chủ
                        </a>
                        <a class="nav-link text-white" href="books/index.php">
                            <i class="fas fa-book"></i> Quản lý sách
                        </a>
                        <a class="nav-link text-white" href="members/index.php">
                            <i class="fas fa-users"></i> Quản lý thành viên
                        </a>
                        <a class="nav-link text-white" href="borrowings/index.php">
                            <i class="fas fa-exchange-alt"></i> Mượn/Trả sách
                        </a>
                        <a class="nav-link text-white" href="reports/index.php">
                            <i class="fas fa-chart-bar"></i> Báo cáo
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="p-4">
                    <h1 class="mb-4">Dashboard - Hệ thống quản lý thư viện</h1>
                    
                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card card-stat bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo $books_count; ?></h4>
                                            <p class="mb-0">Tổng số sách</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-book fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card card-stat bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo $members_count; ?></h4>
                                            <p class="mb-0">Thành viên</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-users fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card card-stat bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo $borrowings_count; ?></h4>
                                            <p class="mb-0">Giao dịch mượn</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-exchange-alt fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 mb-3">
                            <div class="card card-stat bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h4><?php echo $overdue_count; ?></h4>
                                            <p class="mb-0">Sách quá hạn</p>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="fas fa-exclamation-triangle fa-2x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-book"></i> Sách mới nhất</h5>
                                </div>
                                <div class="card-body">
                                    <?php if(count($latest_books_data) > 0): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach($latest_books_data as $book_item): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo $book_item['title']; ?></h6>
                                                        <small class="text-muted"><?php echo $book_item['author']; ?></small>
                                                    </div>
                                                    <span class="badge bg-primary rounded-pill">
                                                        <?php echo $book_item['available_quantity']; ?> có sẵn
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">Chưa có sách nào</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5><i class="fas fa-exchange-alt"></i> Giao dịch gần đây</h5>
                                </div>
                                <div class="card-body">
                                    <?php if(count($recent_borrowings_data) > 0): ?>
                                        <div class="list-group list-group-flush">
                                            <?php foreach($recent_borrowings_data as $borrowing_item): ?>
                                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <h6 class="mb-1"><?php echo $borrowing_item['book_title']; ?></h6>
                                                        <small class="text-muted"><?php echo $borrowing_item['member_name']; ?></small>
                                                    </div>
                                                    <span class="badge bg-<?php echo $borrowing_item['status'] == 'returned' ? 'success' : 'warning'; ?> rounded-pill">
                                                        <?php echo $borrowing_item['status'] == 'returned' ? 'Đã trả' : 'Đang mượn'; ?>
                                                    </span>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted">Chưa có giao dịch nào</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
