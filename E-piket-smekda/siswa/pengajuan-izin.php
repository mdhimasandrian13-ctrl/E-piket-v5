<?php
/**
 * ============================================
 * E-PIKET SMEKDA - Pengajuan Izin/Sakit
 * ============================================
 * File: siswa/pengajuan-izin.php
 * Deskripsi: Halaman untuk mengajukan izin atau sakit
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

// Tipe pengajuan dari parameter URL
$tipe = isset($_GET['type']) ? $_GET['type'] : 'izin';

// ============================================
// PROSES PENGAJUAN IZIN/SAKIT
// ============================================
if (isset($_POST['submit_pengajuan'])) {
    $schedule_id = escape($_POST['schedule_id']);
    $status_type = escape($_POST['status_type']);
    $alasan = escape($_POST['alasan']);
    
    // Validasi
    if (empty($alasan)) {
        $message = "Alasan harus diisi!";
        $message_type = "danger";
    } else {
        // Cek jadwal
        $schedule = fetch_single("SELECT * FROM schedules WHERE id = '$schedule_id' AND student_id = '$student_id'");
        
        if (!$schedule) {
            $message = "Jadwal tidak valid!";
            $message_type = "danger";
        } else {
            $schedule_date = $schedule['schedule_date'];
            
            // Cek apakah sudah ada absensi
            $cek_absen = fetch_single("SELECT id FROM attendances WHERE schedule_id = '$schedule_id' AND attendance_date = '$schedule_date'");
            
            if ($cek_absen) {
                $message = "Anda sudah melakukan absensi untuk jadwal ini!";
                $message_type = "warning";
            } else {
                $query = "INSERT INTO attendances (schedule_id, student_id, attendance_date, status, notes) 
                          VALUES ('$schedule_id', '$student_id', '$schedule_date', '$status_type', '$alasan')";
                
                if (query($query)) {
                    $message = "Pengajuan " . ucfirst($status_type) . " berhasil dikirim!";
                    $message_type = "success";
                } else {
                    $message = "Gagal mengirim pengajuan!";
                    $message_type = "danger";
                }
            }
        }
    }
}

// Ambil jadwal yang bisa diajukan izin (hari ini dan yang akan datang)
$jadwal_mendatang = fetch_all("SELECT s.*, a.id as attendance_id, a.status
                               FROM schedules s
                               LEFT JOIN attendances a ON s.id = a.schedule_id AND a.attendance_date = s.schedule_date
                               WHERE s.student_id = '$student_id' 
                               AND s.schedule_date >= '$today'
                               ORDER BY s.schedule_date ASC
                               LIMIT 10");

$current_page = 'pengajuan-izin';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengajuan Izin/Sakit - E-piket SMEKDA</title>
    
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
        
        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        
        .type-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 25px;
        }
        
        .type-tab {
            flex: 1;
            padding: 15px;
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            text-align: center;
            text-decoration: none;
            color: #666;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .type-tab:hover {
            border-color: #667eea;
            color: #667eea;
        }
        
        .type-tab.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
        }
        
        .jadwal-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .jadwal-card:hover {
            border-color: #667eea;
            transform: translateY(-2px);
        }
        
        .jadwal-card.selected {
            border-color: #667eea;
            background: #e7f3ff;
        }
        
        .jadwal-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
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
        
        .btn-submit {
            width: 100%;
            padding: 12px 25px;
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 16px;
        }
        
        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.3);
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
            
            .type-tabs {
                flex-direction: column;
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
                <a href="pengajuan-izin.php" class="nav-link <?php echo $current_page == 'pengajuan-izin' ? 'active' : ''; ?>">
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
                <h4><i class="fas fa-file-signature"></i> Pengajuan Izin/Sakit</h4>
                <small style="color: #999;">Ajukan izin atau sakit untuk jadwal piket</small>
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
        
        <!-- Type Tabs -->
        <div class="type-tabs">
            <a href="?type=izin" class="type-tab <?php echo $tipe == 'izin' ? 'active' : ''; ?>">
                <i class="fas fa-file-signature"></i> Izin
            </a>
            <a href="?type=sakit" class="type-tab <?php echo $tipe == 'sakit' ? 'active' : ''; ?>">
                <i class="fas fa-notes-medical"></i> Sakit
            </a>
        </div>
        
        <!-- Form Pengajuan -->
        <div class="content-section">
            <div class="section-header">
                <h5><i class="fas fa-calendar-check"></i> Pilih Jadwal yang Akan Di<?php echo ucfirst($tipe); ?>kan</h5>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Pilih jadwal yang ingin Anda ajukan <strong><?php echo $tipe; ?></strong>, kemudian isi alasan dengan jelas.
            </div>
            
            <?php if (count($jadwal_mendatang) > 0): ?>
                <form method="POST" action="" id="formPengajuan">
                    <input type="hidden" name="status_type" value="<?php echo $tipe; ?>">
                    <input type="hidden" name="schedule_id" id="schedule_id" required>
                    
                    <div style="margin-bottom: 20px;">
                        <?php foreach ($jadwal_mendatang as $jadwal): ?>
                        <div class="jadwal-card <?php echo $jadwal['attendance_id'] ? 'disabled' : ''; ?>" 
                             onclick="<?php echo !$jadwal['attendance_id'] ? 'selectJadwal(' . $jadwal['id'] . ')' : ''; ?>">
                            <div style="display: flex; justify-content: space-between; align-items: start;">
                                <div>
                                    <h5 style="margin: 0 0 8px 0;">
                                        <i class="fas fa-calendar"></i> 
                                        <?php echo format_tanggal_indonesia($jadwal['schedule_date']); ?>
                                    </h5>
                                    <p style="margin: 0; color: #666; font-size: 14px;">
                                        <i class="fas fa-clock"></i> Shift <?php echo ucfirst($jadwal['shift']); ?>
                                    </p>
                                </div>
                                <div>
                                    <?php if ($jadwal['attendance_id']): ?>
                                        <span class="badge <?php echo $jadwal['status'] == 'hadir' ? 'badge-success' : 'badge-info'; ?>">
                                            <?php echo ucfirst($jadwal['status']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">Tersedia</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="form-group">
                        <label>Alasan <?php echo ucfirst($tipe); ?> <span style="color: red;">*</span></label>
                        <textarea name="alasan" class="form-control" rows="4" placeholder="Jelaskan alasan Anda..." required></textarea>
                    </div>
                    
                    <button type="submit" name="submit_pengajuan" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Kirim Pengajuan <?php echo ucfirst($tipe); ?>
                    </button>
                </form>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <p>Tidak ada jadwal yang tersedia untuk pengajuan</p>
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
        
        function selectJadwal(scheduleId) {
            // Remove selected class from all
            document.querySelectorAll('.jadwal-card').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Add selected to clicked
            event.currentTarget.classList.add('selected');
            
            // Set hidden input
            document.getElementById('schedule_id').value = scheduleId;
        }
        
        // Validate form
        document.getElementById('formPengajuan').addEventListener('submit', function(e) {
            const scheduleId = document.getElementById('schedule_id').value;
            if (!scheduleId) {
                e.preventDefault();
                alert('Silakan pilih jadwal terlebih dahulu!');
                return false;
            }
        });
        
        // Auto-hide alert
        setTimeout(() => {
            const alert = document.querySelector('.alert-success, .alert-danger, .alert-warning');
            if (alert && !alert.classList.contains('alert-info')) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    </script>
</body>
</html>