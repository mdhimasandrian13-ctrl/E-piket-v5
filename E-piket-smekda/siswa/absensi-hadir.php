<?php
/**
 * ============================================
 * E-PIKET SMEKDA - Absensi Hadir Siswa
 * ============================================
 * File: siswa/absensi-hadir.php
 * Deskripsi: Halaman untuk absensi hadir
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

$message = '';
$message_type = '';

// ============================================
// PROSES ABSENSI HADIR
// ============================================
if (isset($_POST['absensi_hadir'])) {
    $schedule_id = escape($_POST['schedule_id']);
    
    // Validasi jadwal
    $schedule = fetch_single("SELECT * FROM schedules WHERE id = '$schedule_id' AND student_id = '$student_id' AND schedule_date = '$today'");
    
    if (!$schedule) {
        $message = "Jadwal tidak valid!";
        $message_type = "danger";
    } else {
        // Cek apakah sudah absen
        $cek_absen = fetch_single("SELECT id FROM attendances WHERE schedule_id = '$schedule_id' AND attendance_date = '$today'");
        
        if ($cek_absen) {
            $message = "Anda sudah melakukan absensi untuk jadwal ini!";
            $message_type = "warning";
        } else {
            $check_in_time = date('H:i:s');
            $query = "INSERT INTO attendances (schedule_id, student_id, attendance_date, check_in_time, status) 
                      VALUES ('$schedule_id', '$student_id', '$today', '$check_in_time', 'hadir')";
            
            if (query($query)) {
                $message = "Absensi hadir berhasil! Check-in: $check_in_time";
                $message_type = "success";
            } else {
                $message = "Gagal melakukan absensi!";
                $message_type = "danger";
            }
        }
    }
}

// Ambil jadwal hari ini
$jadwal_hari_ini = fetch_all("SELECT s.*, a.id as attendance_id, a.status, a.check_in_time 
                              FROM schedules s
                              LEFT JOIN attendances a ON s.id = a.schedule_id AND a.attendance_date = '$today'
                              WHERE s.student_id = '$student_id' AND s.schedule_date = '$today'
                              ORDER BY s.shift");

$current_page = 'absensi-hadir';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Absensi Hadir - E-piket SMEKDA</title>
    
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        
        .jadwal-card {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 15px;
            border-left: 4px solid #11998e;
        }
        
        .jadwal-card.done {
            border-left-color: #28a745;
            opacity: 0.7;
        }
        
        .jadwal-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .jadwal-info h5 {
            margin: 0 0 8px 0;
            color: #333;
            font-weight: 600;
            font-size: 18px;
        }
        
        .jadwal-info p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
        
        .badge {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        
        .btn-absensi {
            width: 100%;
            padding: 12px 25px;
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-absensi:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(17, 153, 142, 0.3);
        }
        
        .btn-absensi:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
        
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
        
        .time-display {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            margin-bottom: 25px;
        }
        
        .time-display h2 {
            font-size: 48px;
            font-weight: 700;
            margin: 0;
        }
        
        .time-display p {
            margin: 10px 0 0 0;
            opacity: 0.9;
            font-size: 18px;
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
            
            .time-display h2 {
                font-size: 36px;
            }
            
            .jadwal-header {
                flex-direction: column;
                gap: 10px;
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
                <a href="absensi-hadir.php" class="nav-link <?php echo $current_page == 'absensi-hadir' ? 'active' : ''; ?>">
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
        <div class="top-bar">
            <div>
                <h4><i class="fas fa-check-circle"></i> Absensi Hadir</h4>
                <small style="color: #999;">Lakukan absensi kehadiran piket</small>
            </div>
            <a href="dashboard.php" class="btn-primary-custom">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : ($message_type == 'warning' ? 'exclamation-triangle' : 'exclamation-circle'); ?>"></i>
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <!-- Time Display -->
        <div class="time-display">
            <h2 id="currentTime">00:00:00</h2>
            <p id="currentDate"><?php echo format_tanggal_indonesia($today); ?></p>
        </div>
        
        <!-- Jadwal Hari Ini -->
        <div class="content-section">
            <div class="section-header">
                <h5><i class="fas fa-list"></i> Jadwal Piket Hari Ini</h5>
            </div>
            
            <?php if (count($jadwal_hari_ini) > 0): ?>
                <?php foreach ($jadwal_hari_ini as $jadwal): ?>
                <div class="jadwal-card <?php echo $jadwal['attendance_id'] ? 'done' : ''; ?>">
                    <div class="jadwal-header">
                        <div class="jadwal-info">
                            <h5><i class="fas fa-clock"></i> Shift <?php echo ucfirst($jadwal['shift']); ?></h5>
                            <p><i class="fas fa-calendar"></i> <?php echo $jadwal['day_name']; ?>, <?php echo format_tanggal_indonesia($jadwal['schedule_date']); ?></p>
                        </div>
                        <div>
                            <?php if ($jadwal['attendance_id']): ?>
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> Sudah Absen
                                </span>
                            <?php else: ?>
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock"></i> Belum Absen
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($jadwal['attendance_id']): ?>
                        <div style="background: white; padding: 15px; border-radius: 8px;">
                            <p style="margin: 0; color: #666; font-size: 14px;">
                                <i class="fas fa-check-circle" style="color: #28a745;"></i> 
                                <strong>Berhasil check-in:</strong> <?php echo $jadwal['check_in_time']; ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <input type="hidden" name="schedule_id" value="<?php echo $jadwal['id']; ?>">
                            <button type="submit" name="absensi_hadir" class="btn-absensi" onclick="return confirm('Yakin ingin melakukan absensi hadir sekarang?')">
                                <i class="fas fa-fingerprint"></i> Absensi Sekarang
                            </button>
                        </form>
                    <?php endif; ?>
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
        
        // Update time
        function updateTime() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('currentTime').textContent = `${hours}:${minutes}:${seconds}`;
        }
        
        setInterval(updateTime, 1000);
        updateTime();
        
        // Auto-hide alert
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>