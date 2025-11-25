<?php
/**
 * ============================================
 * E-PIKET SMEKDA - Dashboard Siswa (Updated)
 * ============================================
 * File: siswa/dashboard.php
 * Deskripsi: Dashboard utama siswa dengan menu navigasi
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

// Ambil data siswa
$siswa = fetch_single("SELECT u.*, c.class_name FROM users u 
                       LEFT JOIN classes c ON u.class_id = c.id 
                       WHERE u.id = '$student_id'");

// ============================================
// JADWAL PIKET HARI INI
// ============================================
$jadwal_hari_ini = fetch_all("SELECT s.*, a.id as attendance_id, a.status, a.check_in_time 
                              FROM schedules s
                              LEFT JOIN attendances a ON s.id = a.schedule_id AND a.attendance_date = '$today'
                              WHERE s.student_id = '$student_id' AND s.schedule_date = '$today'
                              ORDER BY s.shift");

// ============================================
// STATISTIK KEHADIRAN BULAN INI
// ============================================
$stat_bulan = fetch_single("SELECT 
                            COUNT(a.id) as total_jadwal,
                            SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as total_hadir,
                            SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) as total_izin,
                            SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) as total_sakit,
                            SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) as total_alpha
                            FROM attendances a
                            WHERE a.student_id = '$student_id' 
                            AND YEAR(a.attendance_date) = YEAR(NOW())
                            AND MONTH(a.attendance_date) = MONTH(NOW())");

if (!$stat_bulan) {
    $stat_bulan = array(
        'total_jadwal' => 0,
        'total_hadir' => 0,
        'total_izin' => 0,
        'total_sakit' => 0,
        'total_alpha' => 0
    );
}

$persentase_hadir = $stat_bulan['total_jadwal'] > 0 ? round(($stat_bulan['total_hadir'] / $stat_bulan['total_jadwal']) * 100, 2) : 0;

$current_page = 'dashboard';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - E-piket SMEKDA</title>
    
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
            padding: 20px 25px;
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
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 600;
        }
        
        .logout-btn {
            padding: 8px 20px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .logout-btn:hover {
            background: #c82333;
            color: white;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: white;
            margin: 0 auto 15px;
        }
        
        .stat-icon.blue { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.green { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-icon.orange { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .stat-icon.cyan { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        
        .stat-card h3 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            color: #333;
        }
        
        .stat-card p {
            color: #999;
            font-size: 14px;
            margin: 0;
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .action-btn {
            background: white;
            padding: 25px 20px;
            border-radius: 15px;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        
        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .action-btn i {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .action-btn.hadir i { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .action-btn.izin i { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .action-btn.sakit i { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .action-btn.alpha i { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }
        
        .action-btn span {
            color: #333;
            font-weight: 600;
            font-size: 14px;
        }
        
        .content-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .section-header h5 {
            margin: 0;
            color: #333;
            font-weight: 600;
        }
        
        .jadwal-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        
        .jadwal-card h5 {
            margin: 0 0 10px 0;
            color: #333;
            font-weight: 600;
        }
        
        .jadwal-card p {
            margin: 0;
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
        .badge-warning { background: #fff3cd; color: #856404; }
        
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
            
            .user-info {
                flex-direction: column;
                width: 100%;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
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
                <a href="dashboard.php" class="nav-link <?php echo $current_page == 'dashboard' ? 'active' : ''; ?>">
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
                <a href="jadwal-saya.php" class="nav-link">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Jadwal Saya</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="top-bar">
            <div>
                <h4>Dashboard Siswa</h4>
                <small style="color: #999;">Selamat datang, <?php echo $_SESSION['full_name']; ?>! • <?php echo $siswa['class_name'] ?? '-'; ?></small>
            </div>
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($_SESSION['full_name'], 0, 1)); ?>
                </div>
                <div>
                    <strong style="display: block; font-size: 14px;"><?php echo $_SESSION['full_name']; ?></strong>
                    <small style="color: #999;">NIS: <?php echo $siswa['nis'] ?? '-'; ?></small>
                </div>
                <a href="../auth/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
        
        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <h3><?php echo $stat_bulan['total_jadwal']; ?></h3>
                <p>Total Jadwal</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3><?php echo $stat_bulan['total_hadir']; ?></h3>
                <p>Hadir</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h3><?php echo $stat_bulan['total_alpha']; ?></h3>
                <p>Alpha</p>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon cyan">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3><?php echo $persentase_hadir; ?>%</h3>
                <p>Persentase Hadir</p>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="content-section">
            <div class="section-header">
                <h5><i class="fas fa-bolt"></i> Menu Cepat</h5>
            </div>
            
            <div class="quick-actions">
                <a href="absensi-hadir.php" class="action-btn hadir">
                    <i><i class="fas fa-check"></i></i>
                    <span>Absensi Hadir</span>
                </a>
                
                <a href="pengajuan-izin.php?type=izin" class="action-btn izin">
                    <i><i class="fas fa-file-signature"></i></i>
                    <span>Pengajuan Izin</span>
                </a>
                
                <a href="pengajuan-izin.php?type=sakit" class="action-btn sakit">
                    <i><i class="fas fa-notes-medical"></i></i>
                    <span>Pengajuan Sakit</span>
                </a>
                
                <a href="riwayat.php" class="action-btn alpha">
                    <i><i class="fas fa-history"></i></i>
                    <span>Riwayat</span>
                </a>
            </div>
        </div>
        
        <!-- Jadwal Hari Ini -->
        <div class="content-section">
            <div class="section-header">
                <h5><i class="fas fa-calendar-day"></i> Jadwal Piket Hari Ini</h5>
                <small style="color: #999;"><?php echo format_tanggal_indonesia($today); ?></small>
            </div>
            
            <?php if (count($jadwal_hari_ini) > 0): ?>
                <?php foreach ($jadwal_hari_ini as $jadwal): ?>
                <div class="jadwal-card">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div>
                            <h5>Shift <?php echo ucfirst($jadwal['shift']); ?></h5>
                            <p><i class="fas fa-clock"></i> <?php echo $jadwal['day_name']; ?>, <?php echo format_tanggal_indonesia($jadwal['schedule_date']); ?></p>
                        </div>
                        <div>
                            <?php if ($jadwal['attendance_id']): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> Sudah Absen
                                </span>
                                <p style="margin-top: 5px; font-size: 12px; color: #666;">
                                    Check-in: <?php echo $jadwal['check_in_time']; ?>
                                </p>
                            <?php else: ?>
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock"></i> Belum Absen
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>Anda tidak terjadwal piket hari ini</p>
                </div>
            <?php endif; ?>
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