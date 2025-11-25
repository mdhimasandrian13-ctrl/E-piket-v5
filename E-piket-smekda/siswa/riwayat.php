<?php
/**
 * ============================================
 * E-PIKET SMEKDA - Riwayat Absensi Siswa
 * ============================================
 * File: siswa/riwayat.php
 * Deskripsi: Halaman riwayat absensi lengkap
 * ============================================
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$student_id = $_SESSION['user_id'];

// Filter
$filter_bulan = isset($_GET['bulan']) ? escape($_GET['bulan']) : date('Y-m');
$filter_status = isset($_GET['status']) ? escape($_GET['status']) : '';

// Parse bulan
$bulan_parts = explode('-', $filter_bulan);
$tahun = $bulan_parts[0];
$bulan = $bulan_parts[1];

// Query riwayat
$where = "WHERE s.student_id = '$student_id' 
          AND YEAR(s.schedule_date) = '$tahun' 
          AND MONTH(s.schedule_date) = '$bulan'";

if (!empty($filter_status)) {
    $where .= " AND a.status = '$filter_status'";
}

$riwayat = fetch_all("SELECT s.schedule_date, s.day_name, s.shift, 
                      a.status, a.check_in_time, a.check_out_time, a.notes,
                      c.class_name
                      FROM schedules s
                      LEFT JOIN attendances a ON s.id = a.schedule_id AND a.attendance_date = s.schedule_date
                      JOIN classes c ON s.class_id = c.id
                      $where
                      ORDER BY s.schedule_date DESC");

// Statistik bulan ini
$stat = fetch_single("SELECT 
                      COUNT(*) as total,
                      SUM(CASE WHEN a.status = 'hadir' THEN 1 ELSE 0 END) as hadir,
                      SUM(CASE WHEN a.status = 'izin' THEN 1 ELSE 0 END) as izin,
                      SUM(CASE WHEN a.status = 'sakit' THEN 1 ELSE 0 END) as sakit,
                      SUM(CASE WHEN a.status = 'alpha' THEN 1 ELSE 0 END) as alpha,
                      SUM(CASE WHEN a.status IS NULL THEN 1 ELSE 0 END) as belum_absen
                      FROM schedules s
                      LEFT JOIN attendances a ON s.id = a.schedule_id AND a.attendance_date = s.schedule_date
                      WHERE s.student_id = '$student_id'
                      AND YEAR(s.schedule_date) = '$tahun'
                      AND MONTH(s.schedule_date) = '$bulan'");

if (!$stat) {
    $stat = array('total' => 0, 'hadir' => 0, 'izin' => 0, 'sakit' => 0, 'alpha' => 0, 'belum_absen' => 0);
}

$persentase = $stat['total'] > 0 ? round(($stat['hadir'] / $stat['total']) * 100, 2) : 0;

$current_page = 'riwayat';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Absensi - E-piket SMEKDA</title>
    
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .stat-card h3 {
            margin: 0 0 5px 0;
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }
        
        .stat-card p {
            margin: 0;
            color: #999;
            font-size: 13px;
        }
        
        .stat-card.blue { border-top: 3px solid #667eea; }
        .stat-card.green { border-top: 3px solid #11998e; }
        .stat-card.cyan { border-top: 3px solid #4facfe; }
        .stat-card.pink { border-top: 3px solid #f093fb; }
        .stat-card.orange { border-top: 3px solid #f5576c; }
        .stat-card.yellow { border-top: 3px solid #ffc107; }
        
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
        
        .table-custom {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table-custom thead {
            background: #f8f9fa;
        }
        
        .table-custom th {
            padding: 15px 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 13px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .table-custom td {
            padding: 15px 12px;
            border-bottom: 1px solid #f0f0f0;
            color: #333;
            font-size: 14px;
        }
        
        .table-custom tbody tr:hover {
            background: #f8f9fa;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-warning { background: #fff3cd; color: #856404; }
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
        
        .row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .col {
            flex: 1;
            min-width: 200px;
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
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .col {
                min-width: 100%;
            }
            
            .table-custom th,
            .table-custom td {
                padding: 10px 8px;
                font-size: 12px;
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
                <a href="riwayat.php" class="nav-link <?php echo $current_page == 'riwayat' ? 'active' : ''; ?>">
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
        <div class="top-bar">
            <div>
                <h4><i class="fas fa-history"></i> Riwayat Absensi</h4>
                <small style="color: #999;">Lihat riwayat kehadiran piket Anda</small>
            </div>
            <a href="dashboard.php" class="btn-primary-custom">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        
        <!-- Statistik -->
        <div class="stats-grid">
            <div class="stat-card blue">
                <h3><?php echo $stat['total']; ?></h3>
                <p>Total Jadwal</p>
            </div>
            <div class="stat-card green">
                <h3><?php echo $stat['hadir']; ?></h3>
                <p>Hadir</p>
            </div>
            <div class="stat-card cyan">
                <h3><?php echo $stat['izin']; ?></h3>
                <p>Izin</p>
            </div>
            <div class="stat-card pink">
                <h3><?php echo $stat['sakit']; ?></h3>
                <p>Sakit</p>
            </div>
            <div class="stat-card orange">
                <h3><?php echo $stat['alpha']; ?></h3>
                <p>Alpha</p>
            </div>
            <div class="stat-card yellow">
                <h3><?php echo $persentase; ?>%</h3>
                <p>Persentase</p>
            </div>
        </div>
        
        <!-- Filter & Table -->
        <div class="content-section">
            <div class="section-header">
                <h5><i class="fas fa-filter"></i> Filter Riwayat</h5>
            </div>
            
            <div class="filter-section">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #666; font-size: 14px;">
                                Bulan
                            </label>
                            <input type="month" name="bulan" class="form-control" value="<?php echo $filter_bulan; ?>">
                        </div>
                        <div class="col">
                            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #666; font-size: 14px;">
                                Status
                            </label>
                            <select name="status" class="form-control">
                                <option value="">-- Semua Status --</option>
                                <option value="hadir" <?php echo $filter_status == 'hadir' ? 'selected' : ''; ?>>Hadir</option>
                                <option value="izin" <?php echo $filter_status == 'izin' ? 'selected' : ''; ?>>Izin</option>
                                <option value="sakit" <?php echo $filter_status == 'sakit' ? 'selected' : ''; ?>>Sakit</option>
                                <option value="alpha" <?php echo $filter_status == 'alpha' ? 'selected' : ''; ?>>Alpha</option>
                            </select>
                        </div>
                        <div class="col" style="display: flex; align-items: flex-end;">
                            <button type="submit" class="btn-primary-custom" style="width: 100%;">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="section-header">
                <h5><i class="fas fa-list"></i> Data Riwayat (<?php echo count($riwayat); ?>)</h5>
            </div>
            
            <?php if (count($riwayat) > 0): ?>
            <div style="overflow-x: auto;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Tanggal</th>
                            <th>Hari</th>
                            <th>Shift</th>
                            <th>Check-in</th>
                            <th>Check-out</th>
                            <th>Status</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($riwayat as $r): 
                            $badge_class = 'badge-secondary';
                            $status_text = 'Belum Absen';
                            
                            if ($r['status'] == 'hadir') {
                                $badge_class = 'badge-success';
                                $status_text = 'Hadir';
                            } elseif ($r['status'] == 'izin') {
                                $badge_class = 'badge-info';
                                $status_text = 'Izin';
                            } elseif ($r['status'] == 'sakit') {
                                $badge_class = 'badge-info';
                                $status_text = 'Sakit';
                            } elseif ($r['status'] == 'alpha') {
                                $badge_class = 'badge-danger';
                                $status_text = 'Alpha';
                            }
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo format_tanggal_indonesia($r['schedule_date']); ?></td>
                            <td><?php echo $r['day_name']; ?></td>
                            <td><?php echo ucfirst($r['shift']); ?></td>
                            <td><?php echo $r['check_in_time'] ?? '-'; ?></td>
                            <td><?php echo $r['check_out_time'] ?? '-'; ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span></td>
                            <td><?php echo $r['notes'] ? substr($r['notes'], 0, 30) . '...' : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>Tidak ada riwayat untuk filter yang dipilih</p>
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