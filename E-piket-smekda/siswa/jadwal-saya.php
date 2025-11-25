<?php
/**
 * ============================================
 * E-PIKET SMEKDA - Jadwal Saya
 * ============================================
 * File: siswa/jadwal-saya.php
 * Deskripsi: Halaman untuk melihat semua jadwal piket
 * ============================================
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$student_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Filter
$filter_bulan = isset($_GET['bulan']) ? escape($_GET['bulan']) : date('Y-m');

// Parse bulan
$bulan_parts = explode('-', $filter_bulan);
$tahun = $bulan_parts[0];
$bulan = $bulan_parts[1];

// Query jadwal
$jadwal_list = fetch_all("SELECT s.*, 
                          a.status, a.check_in_time,
                          c.class_name
                          FROM schedules s
                          LEFT JOIN attendances a ON s.id = a.schedule_id AND a.attendance_date = s.schedule_date
                          JOIN classes c ON s.class_id = c.id
                          WHERE s.student_id = '$student_id'
                          AND YEAR(s.schedule_date) = '$tahun'
                          AND MONTH(s.schedule_date) = '$bulan'
                          ORDER BY s.schedule_date ASC");

// Hitung statistik
$total_jadwal = count($jadwal_list);
$jadwal_selesai = count(array_filter($jadwal_list, fn($j) => $j['status'] != null));
$jadwal_mendatang = count(array_filter($jadwal_list, fn($j) => $j['schedule_date'] >= $today && $j['status'] == null));
$jadwal_terlewat = count(array_filter($jadwal_list, fn($j) => $j['schedule_date'] < $today && $j['status'] == null));

$current_page = 'jadwal-saya';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Saya - E-piket SMEKDA</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f6fa;
        }
        
        .hamburger-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 2000;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            border-radius: 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            transition: all 0.3s;
        }
        
        .hamburger-btn:hover {
            transform: translateY(-2px);
        }
        
        .sidebar {
            position: fixed;
            left: -260px;
            top: 0;
            height: 100vh;
            width: 260px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            z-index: 1999;
            overflow-y: auto;
            transition: left 0.3s ease;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .sidebar::-webkit-scrollbar {
            width: 5px;
        }
        
        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }
        
        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1998;
            display: none;
        }
        
        .sidebar-overlay.active {
            display: block;
        }
        
        .sidebar-header {
            color: white;
            text-align: center;
            padding: 20px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            margin-bottom: 30px;
            margin-top: 40px;
        }
        
        .sidebar-header h3 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .close-sidebar {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .close-sidebar:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .nav-menu {
            list-style: none;
        }
        
        .nav-item {
            margin-bottom: 5px;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(5px);
        }
        
        .nav-link i {
            width: 20px;
            margin-right: 10px;
        }
        
        .main-content {
            margin-left: 0;
            padding: 20px;
            padding-top: 90px;
        }
        
        .top-bar {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .top-bar h4 {
            margin: 0;
            color: #333;
            font-weight: 600;
        }
        
        .btn-primary-custom {
            padding: 10px 25px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .stats-mini {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-mini-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .stat-mini-card h3 {
            margin: 0 0 5px 0;
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }
        
        .stat-mini-card p {
            margin: 0;
            color: #999;
            font-size: 13px;
        }
        
        .stat-mini-card.blue { border-top: 3px solid #667eea; }
        .stat-mini-card.green { border-top: 3px solid #11998e; }
        .stat-mini-card.orange { border-top: 3px solid #f5576c; }
        .stat-mini-card.yellow { border-top: 3px solid #ffc107; }
        
        .content-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }
        
        .section-header {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section-header h5 {
            margin: 0;
            color: #333;
            font-weight: 600;
        }
        
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: 'Poppins', sans-serif;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
        }
        
        .jadwal-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            transition: all 0.3s;
            border-left: 4px solid #667eea;
        }
        
        .jadwal-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .jadwal-card.done {
            border-left-color: #28a745;
            background: #d4edda;
        }
        
        .jadwal-card.upcoming {
            border-left-color: #ffc107;
            background: #fff3cd;
        }
        
        .jadwal-card.missed {
            border-left-color: #dc3545;
            background: #f8d7da;
        }
        
        .jadwal-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .jadwal-date {
            font-size: 24px;
            font-weight: 700;
            color: #333;
            margin: 0 0 5px 0;
        }
        
        .jadwal-day {
            color: #666;
            font-size: 14px;
        }
        
        .jadwal-info {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        .jadwal-info p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-secondary { background: #e2e3e5; color: #383d41; }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state i {
            font-size: 48px;
            margin-bottom: 15px;
            opacity: 0.5;
        }
        
        @media (max-width: 768px) {
            .hamburger-btn {
                width: 45px;
                height: 45px;
                font-size: 18px;
            }
            
            .main-content {
                padding: 15px;
                padding-top: 85px;
            }
            
            .top-bar {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-mini {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .calendar-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Hamburger Button -->
    <button class="hamburger-btn" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>
    
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <button class="close-sidebar" onclick="toggleSidebar()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="sidebar-header">
            <h3>E-PIKET</h3>
            <p>SMEKDA Siswa</p>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="absensi-hadir.php" class="nav-link">
                    <i class="fas fa-check-circle"></i>
                    <span>Absensi Hadir</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="pengajuan-izin.php" class="nav-link">
                    <i class="fas fa-file-alt"></i>
                    <span>Pengajuan Izin</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="riwayat.php" class="nav-link">
                    <i class="fas fa-history"></i>
                    <span>Riwayat Absensi</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="jadwal-saya.php" class="nav-link <?php echo $current_page == 'jadwal-saya' ? 'active' : ''; ?>">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Jadwal Saya</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <div>
                <h4><i class="fas fa-calendar-alt"></i> Jadwal Piket Saya</h4>
                <small style="color: #999;">Lihat semua jadwal piket Anda</small>
            </div>
            <a href="dashboard.php" class="btn-primary-custom">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        
        <!-- Mini Stats -->
        <div class="stats-mini">
            <div class="stat-mini-card blue">
                <h3><?php echo $total_jadwal; ?></h3>
                <p>Total Jadwal</p>
            </div>
            <div class="stat-mini-card green">
                <h3><?php echo $jadwal_selesai; ?></h3>
                <p>Sudah Selesai</p>
            </div>
            <div class="stat-mini-card yellow">
                <h3><?php echo $jadwal_mendatang; ?></h3>
                <p>Akan Datang</p>
            </div>
            <div class="stat-mini-card orange">
                <h3><?php echo $jadwal_terlewat; ?></h3>
                <p>Terlewat</p>
            </div>
        </div>
        
        <!-- Filter & Calendar -->
        <div class="content-section">
            <div class="section-header">
                <h5><i class="fas fa-filter"></i> Filter Jadwal</h5>
            </div>
            
            <div class="filter-section">
                <form method="GET" action="">
                    <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 200px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #666; font-size: 14px;">
                                Pilih Bulan
                            </label>
                            <input type="month" name="bulan" class="form-control" value="<?php echo $filter_bulan; ?>">
                        </div>
                        <div style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn-primary-custom">
                                <i class="fas fa-search"></i> Tampilkan
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="section-header">
                <h5><i class="fas fa-calendar-check"></i> Kalender Jadwal (<?php echo date('F Y', strtotime($filter_bulan . '-01')); ?>)</h5>
            </div>
            
            <?php if (count($jadwal_list) > 0): ?>
            <div class="calendar-grid">
                <?php foreach ($jadwal_list as $jadwal): 
                    $card_class = '';
                    $badge_class = 'badge-secondary';
                    $badge_text = 'Belum Absen';
                    
                    if ($jadwal['status']) {
                        $card_class = 'done';
                        if ($jadwal['status'] == 'hadir') {
                            $badge_class = 'badge-success';
                            $badge_text = 'Hadir';
                        } elseif ($jadwal['status'] == 'izin') {
                            $badge_class = 'badge-info';
                            $badge_text = 'Izin';
                        } elseif ($jadwal['status'] == 'sakit') {
                            $badge_class = 'badge-info';
                            $badge_text = 'Sakit';
                        } elseif ($jadwal['status'] == 'alpha') {
                            $badge_class = 'badge-danger';
                            $badge_text = 'Alpha';
                        }
                    } elseif ($jadwal['schedule_date'] >= $today) {
                        $card_class = 'upcoming';
                        $badge_class = 'badge-warning';
                        $badge_text = 'Akan Datang';
                    } else {
                        $card_class = 'missed';
                        $badge_class = 'badge-danger';
                        $badge_text = 'Terlewat';
                    }
                    
                    $date_parts = explode('-', $jadwal['schedule_date']);
                    $tanggal = $date_parts[2];
                ?>
                <div class="jadwal-card <?php echo $card_class; ?>">
                    <div class="jadwal-header">
                        <div>
                            <div class="jadwal-date"><?php echo $tanggal; ?></div>
                            <div class="jadwal-day"><?php echo $jadwal['day_name']; ?></div>
                        </div>
                        <span class="badge <?php echo $badge_class; ?>"><?php echo $badge_text; ?></span>
                    </div>
                    
                    <div class="jadwal-info">
                        <p><i class="fas fa-clock"></i> <strong>Shift:</strong> <?php echo ucfirst($jadwal['shift']); ?></p>
                        <p><i class="fas fa-school"></i> <strong>Kelas:</strong> <?php echo $jadwal['class_name']; ?></p>
                        <?php if ($jadwal['check_in_time']): ?>
                        <p><i class="fas fa-sign-in-alt"></i> <strong>Check-in:</strong> <?php echo $jadwal['check_in_time']; ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>Tidak ada jadwal untuk bulan ini</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Legenda -->
        <div class="content-section">
            <div class="section-header">
                <h5><i class="fas fa-info-circle"></i> Keterangan</h5>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 20px; height: 20px; background: #d4edda; border-left: 4px solid #28a745; border-radius: 4px;"></div>
                    <span style="font-size: 14px;">Sudah Selesai</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 20px; height: 20px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;"></div>
                    <span style="font-size: 14px;">Akan Datang</span>
                </div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 20px; height: 20px; background: #f8d7da; border-left: 4px solid #dc3545; border-radius: 4px;"></div>
                    <span style="font-size: 14px;">Terlewat</span>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.querySelector('.sidebar-overlay');
            
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
        }
    </script>
</body>
</html>