<?php
/**
 * ============================================
 * E-PIKET SMEKDA - Monitoring Piket Guru
 * ============================================
 * File: guru/monitoring.php
 * Deskripsi: Monitoring detail jadwal dan absensi piket
 * ============================================
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'guru') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/database.php';

$guru_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Ambil kelas yang diampu
$kelas_diampu = fetch_all("SELECT * FROM classes WHERE homeroom_teacher_id = '$guru_id' AND is_active = 1");

if (count($kelas_diampu) == 0) {
    $kelas_ids = array();
} else {
    $kelas_ids = array_column($kelas_diampu, 'id');
}

// Filter tanggal
$filter_date = isset($_GET['filter_date']) ? escape($_GET['filter_date']) : $today;
$filter_class = isset($_GET['filter_class']) ? escape($_GET['filter_class']) : '';

// Proses input absensi manual
$message = '';
$message_type = '';

if (isset($_POST['input_absensi'])) {
    $schedule_id = escape($_POST['schedule_id']);
    $status = escape($_POST['status']);
    $notes = escape($_POST['notes']);
    
    // Ambil data schedule
    $schedule = fetch_single("SELECT * FROM schedules WHERE id = '$schedule_id'");
    $student_id = $schedule['student_id'];
    $attendance_date = $schedule['schedule_date'];
    
    // Cek apakah sudah ada record
    $cek_absen = fetch_single("SELECT id FROM attendances WHERE schedule_id = '$schedule_id' AND attendance_date = '$attendance_date'");
    
    if ($cek_absen) {
        // Update
        $query = "UPDATE attendances SET 
                  status = '$status',
                  notes = '$notes',
                  verified_by = '$guru_id',
                  verified_at = NOW()
                  WHERE id = '{$cek_absen['id']}'";
    } else {
        // Insert
        $query = "INSERT INTO attendances (schedule_id, student_id, attendance_date, status, notes, verified_by, verified_at) 
                  VALUES ('$schedule_id', '$student_id', '$attendance_date', '$status', '$notes', '$guru_id', NOW())";
    }
    
    if (query($query)) {
        $message = "Absensi berhasil diinput!";
        $message_type = "success";
    } else {
        $message = "Gagal menginput absensi!";
        $message_type = "danger";
    }
}

// Query data monitoring
$where = "WHERE 1=1";

if (count($kelas_ids) > 0) {
    $kelas_str = implode(',', $kelas_ids);
    $where .= " AND s.class_id IN ($kelas_str)";
}

if (!empty($filter_class)) {
    $where .= " AND s.class_id = '$filter_class'";
}

$where .= " AND s.schedule_date = '$filter_date'";

$jadwal_monitoring = fetch_all("SELECT s.*, 
                                u.full_name, 
                                u.nis, 
                                c.class_name,
                                a.id as attendance_id,
                                a.status as status_absen,
                                a.check_in_time,
                                a.check_out_time,
                                a.notes as attendance_notes
                                FROM schedules s
                                JOIN users u ON s.student_id = u.id
                                JOIN classes c ON s.class_id = c.id
                                LEFT JOIN attendances a ON s.id = a.schedule_id AND s.schedule_date = a.attendance_date
                                $where 
                                ORDER BY c.class_name, u.full_name");

// Statistik untuk tanggal terpilih
$total_jadwal = count($jadwal_monitoring);
$hadir = count(array_filter($jadwal_monitoring, fn($j) => $j['status_absen'] == 'hadir'));
$izin = count(array_filter($jadwal_monitoring, fn($j) => $j['status_absen'] == 'izin'));
$sakit = count(array_filter($jadwal_monitoring, fn($j) => $j['status_absen'] == 'sakit'));
$alpha = count(array_filter($jadwal_monitoring, fn($j) => $j['status_absen'] == 'alpha'));
$belum_absen = count(array_filter($jadwal_monitoring, fn($j) => $j['status_absen'] == null));

$current_page = 'monitoring';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Piket - E-piket SMEKDA</title>
    
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
        
        .stats-mini {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        
        .stat-mini-card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .stat-mini-card h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }
        
        .stat-mini-card p {
            margin: 5px 0 0 0;
            font-size: 12px;
            color: #999;
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
        
        .filter-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
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
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-light { background: #e2e3e5; color: #383d41; }
        
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            margin-right: 5px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            color: white;
        }
        
        .btn-edit {
            background: #ffc107;
        }
        
        .btn-edit:hover {
            background: #e0a800;
            color: white;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            overflow-y: auto;
        }
        
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .modal-header h5 {
            margin: 0;
            color: #333;
            font-weight: 600;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
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
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
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
        
        .row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .col {
            flex: 1;
            min-width: 150px;
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
            
            .col {
                min-width: 100%;
            }
            
            .stats-mini {
                grid-template-columns: repeat(2, 1fr);
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
            <p>SMEKDA Guru</p>
        </div>
        
        <ul class="nav-menu">
            <li class="nav-item">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="monitoring.php" class="nav-link <?php echo $current_page == 'monitoring' ? 'active' : ''; ?>">
                    <i class="fas fa-binoculars"></i>
                    <span>Monitoring Piket</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="laporan.php" class="nav-link">
                    <i class="fas fa-chart-bar"></i>
                    <span>Laporan</span>
                </a>
            </li>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="top-bar">
            <div>
                <h4><i class="fas fa-binoculars"></i> Monitoring Piket</h4>
                <small style="color: #999;">Detail monitoring jadwal dan absensi piket</small>
            </div>
            <a href="dashboard.php" class="btn-primary-custom">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
        
        <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo $message; ?>
        </div>
        <?php endif; ?>
        
        <!-- Filter Section -->
        <div class="content-section">
            <div class="filter-section">
                <form method="GET" action="">
                    <div class="row">
                        <div class="col">
                            <label style="color: #666; font-weight: 500; font-size: 14px; margin-bottom: 8px; display: block;">Pilih Tanggal</label>
                            <input type="date" name="filter_date" class="form-control" value="<?php echo $filter_date; ?>">
                        </div>
                        <div class="col">
                            <label style="color: #666; font-weight: 500; font-size: 14px; margin-bottom: 8px; display: block;">Pilih Kelas</label>
                            <select name="filter_class" class="form-control">
                                <option value="">-- Semua Kelas --</option>
                                <?php foreach ($kelas_diampu as $kelas): ?>
                                    <option value="<?php echo $kelas['id']; ?>" <?php echo $filter_class == $kelas['id'] ? 'selected' : ''; ?>>
                                        <?php echo $kelas['class_name']; ?>
                                    </option>
                                <?php endforeach; ?>
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
        </div>
        
        <!-- Statistik Mini -->
        <div class="stats-mini">
            <div class="stat-mini-card">
                <h3><?php echo $total_jadwal; ?></h3>
                <p>Total Jadwal</p>
            </div>
            <div class="stat-mini-card" style="border-top: 3px solid #28a745;">
                <h3><?php echo $hadir; ?></h3>
                <p>Hadir</p>
            </div>
            <div class="stat-mini-card" style="border-top: 3px solid #0c5460;">
                <h3><?php echo $izin + $sakit; ?></h3>
                <p>Izin/Sakit</p>
            </div>
            <div class="stat-mini-card" style="border-top: 3px solid #721c24;">
                <h3><?php echo $alpha; ?></h3>
                <p>Alpha</p>
            </div>
            <div class="stat-mini-card" style="border-top: 3px solid #856404;">
                <h3><?php echo $belum_absen; ?></h3>
                <p>Belum Absen</p>
            </div>
        </div>
        
        <!-- Tabel Monitoring -->
        <div class="content-section">
            <div class="section-header">
                <h5><i class="fas fa-list"></i> Daftar Piket (<?php echo format_tanggal_indonesia($filter_date); ?>)</h5>
            </div>
            
            <?php if (count($jadwal_monitoring) > 0): ?>
            <div style="overflow-x: auto;">
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kelas</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th>Shift</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        foreach ($jadwal_monitoring as $jadwal): 
                            $badge_class = 'badge-warning';
                            $status_text = 'Belum Absen';
                            
                            if ($jadwal['status_absen'] == 'hadir') {
                                $badge_class = 'badge-success';
                                $status_text = 'Hadir';
                            } elseif ($jadwal['status_absen'] == 'alpha') {
                                $badge_class = 'badge-danger';
                                $status_text = 'Alpha';
                            } elseif ($jadwal['status_absen'] == 'izin') {
                                $badge_class = 'badge-info';
                                $status_text = 'Izin';
                            } elseif ($jadwal['status_absen'] == 'sakit') {
                                $badge_class = 'badge-info';
                                $status_text = 'Sakit';
                            }
                        ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo $jadwal['class_name']; ?></td>
                            <td><?php echo $jadwal['nis']; ?></td>
                            <td><?php echo $jadwal['full_name']; ?></td>
                            <td><?php echo ucfirst($jadwal['shift']); ?></td>
                            <td><?php echo $jadwal['check_in_time'] ?? '-'; ?></td>
                            <td><?php echo $jadwal['check_out_time'] ?? '-'; ?></td>
                            <td><span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span></td>
                            <td>
                                <button class="btn-action btn-edit" onclick="openAbsensiModal(<?php echo $jadwal['id']; ?>, '<?php echo addslashes($jadwal['full_name']); ?>', '<?php echo $jadwal['status_absen']; ?>', '<?php echo addslashes($jadwal['attendance_notes']); ?>')">
                                    <i class="fas fa-edit"></i> Input
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 40px; color: #999;">
                <i class="fas fa-inbox" style="font-size: 48px; margin-bottom: 15px; opacity: 0.5;"></i>
                <p>Tidak ada jadwal piket untuk tanggal ini</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Modal Input Absensi -->
    <div id="modalAbsensi" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h5><i class="fas fa-clipboard-list"></i> Input Absensi Piket</h5>
                <button class="close-modal" onclick="closeModal('modalAbsensi')">&times;</button>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="schedule_id" id="schedule_id">
                
                <div class="form-group">
                    <label>Nama Siswa</label>
                    <input type="text" id="nama_siswa" class="form-control" readonly style="background: #f8f9fa;">
                </div>
                
                <div class="form-group">
                    <label>Status <span style="color: red;">*</span></label>
                    <select name="status" class="form-control" required>
                        <option value="">-- Pilih Status --</option>
                        <option value="hadir">Hadir</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpha">Alpha</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Catatan</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Masukkan catatan jika ada..."></textarea>
                </div>
                
                <button type="submit" name="input_absensi" class="btn-primary-custom" style="width: 100%;">
                    <i class="fas fa-save"></i> Simpan Absensi
                </button>
            </form>
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
        
        function openAbsensiModal(scheduleId, namaSiswa, status, notes) {
            document.getElementById('schedule_id').value = scheduleId;
            document.getElementById('nama_siswa').value = namaSiswa;
            document.querySelector('select[name="status"]').value = status || '';
            document.querySelector('textarea[name="notes"]').value = notes || '';
            document.getElementById('modalAbsensi').classList.add('show');
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('show');
        }
        
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('show');
            }
        }
        
        setTimeout(() => {
            const alert = document.querySelector('.alert');
            if (alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
        
        // Fitur tambahan: Konfirmasi sebelum input absensi
        document.querySelector('form[method="POST"]').addEventListener('submit', function(e) {
            const status = document.querySelector('select[name="status"]').value;
            const namaSiswa = document.getElementById('nama_siswa').value;
            
            if (status === 'alpha') {
                if (!confirm(`Yakin ingin menandai ${namaSiswa} sebagai ALPHA?`)) {
                    e.preventDefault();
                    return false;
                }
            }
        });
        
        // Fitur: Auto-select status dari parameter
        function setStatusFromModal(status) {
            if (status) {
                document.querySelector('select[name="status"]').value = status;
            }
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // ESC untuk close modal
            if (e.key === 'Escape') {
                const modal = document.querySelector('.modal.show');
                if (modal) {
                    modal.classList.remove('show');
                }
            }
        });
        
        // Highlight baris yang belum absen
        document.addEventListener('DOMContentLoaded', function() {
            const rows = document.querySelectorAll('.table-custom tbody tr');
            rows.forEach(row => {
                const badge = row.querySelector('.badge-warning');
                if (badge && badge.textContent === 'Belum Absen') {
                    row.style.backgroundColor = '#fff3cd20';
                }
            });
        });
        
        // Filter real-time untuk tabel (search)
        function addSearchFeature() {
            const searchInput = document.getElementById('searchTable');
            if (searchInput) {
                searchInput.addEventListener('keyup', function() {
                    const filter = this.value.toLowerCase();
                    const rows = document.querySelectorAll('.table-custom tbody tr');
                    
                    rows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (text.includes(filter)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }
        }
        
        // Export ke CSV function (optional)
        function exportToCSV() {
            const table = document.querySelector('.table-custom');
            let csv = [];
            const rows = table.querySelectorAll('tr');
            
            rows.forEach(row => {
                const cols = row.querySelectorAll('td, th');
                const csvRow = [];
                cols.forEach(col => {
                    // Skip kolom aksi
                    if (col.cellIndex !== cols.length - 1) {
                        csvRow.push(col.textContent.trim());
                    }
                });
                csv.push(csvRow.join(','));
            });
            
            // Download CSV
            const csvContent = csv.join('\n');
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', 'monitoring_piket_<?php echo $filter_date; ?>.csv');
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
        
        // Print function
        function printMonitoring() {
            window.print();
        }
        
        // Refresh otomatis setiap 5 menit (optional, bisa dihapus jika tidak diperlukan)
        // setInterval(function() {
        //     location.reload();
        // }, 300000); // 5 menit
        
        // Tooltip untuk status badge
        document.querySelectorAll('.badge').forEach(badge => {
            badge.style.cursor = 'help';
            badge.title = 'Klik untuk melihat detail';
        });
        
        // Animasi loading saat submit form
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                }
            });
        });
        
        // Update timestamp display
        function updateTimestamp() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID');
            const timestampEl = document.getElementById('currentTime');
            if (timestampEl) {
                timestampEl.textContent = timeString;
            }
        }
        
        // Update setiap detik
        setInterval(updateTimestamp, 1000);
        
        // Validasi form
        function validateForm() {
            const status = document.querySelector('select[name="status"]').value;
            if (!status) {
                alert('Status wajib dipilih!');
                return false;
            }
            return true;
        }
        
        // Notification permission (untuk future feature)
        if ('Notification' in window && Notification.permission === 'default') {
            // Tidak otomatis minta permission, tunggu user action
        }
        
        // Service Worker untuk offline capability (advanced feature)
        if ('serviceWorker' in navigator) {
            // Bisa ditambahkan untuk PWA
        }
        
        console.log('✅ Monitoring Piket System loaded successfully');
        console.log('📊 Total Jadwal: <?php echo $total_jadwal; ?>');
        console.log('✅ Hadir: <?php echo $hadir; ?>');
        console.log('⚠️ Belum Absen: <?php echo $belum_absen; ?>');
    </script>
    
    <!-- Print Styles -->
    <style media="print">
        .hamburger-btn,
        .sidebar,
        .sidebar-overlay,
        .btn-primary-custom,
        .btn-action,
        .filter-section,
        .top-bar a {
            display: none !important;
        }
        
        .main-content {
            padding: 20px;
            padding-top: 20px;
        }
        
        .content-section {
            box-shadow: none;
            page-break-inside: avoid;
        }
        
        .stats-mini {
            grid-template-columns: repeat(5, 1fr);
            margin-bottom: 20px;
        }
        
        .table-custom {
            font-size: 11px;
        }
        
        .table-custom th,
        .table-custom td {
            padding: 8px 6px;
        }
        
        body {
            background: white;
        }
        
        @page {
            margin: 1cm;
        }
        
        .top-bar {
            border: 1px solid #ddd;
            margin-bottom: 15px;
        }
        
        .top-bar h4 {
            font-size: 18px;
        }
    </style>
    
    <!-- Additional Features Script -->
    <script>
        // Context menu custom (right click)
        document.addEventListener('contextmenu', function(e) {
            if (e.target.closest('.table-custom tbody tr')) {
                e.preventDefault();
                // Bisa ditambahkan custom context menu
            }
        });
        
        // Double click untuk quick edit
        document.querySelectorAll('.table-custom tbody tr').forEach(row => {
            row.addEventListener('dblclick', function() {
                const editBtn = this.querySelector('.btn-edit');
                if (editBtn) {
                    editBtn.click();
                }
            });
        });
        
        // Auto focus pada status select saat modal dibuka
        const modalAbsensi = document.getElementById('modalAbsensi');
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.target.classList.contains('show')) {
                    setTimeout(() => {
                        document.querySelector('select[name="status"]').focus();
                    }, 100);
                }
            });
        });
        
        observer.observe(modalAbsensi, {
            attributes: true,
            attributeFilter: ['class']
        });
        
        // Keyboard navigation untuk modal
        document.querySelector('select[name="status"]').addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('textarea[name="notes"]').focus();
            }
        });
        
        // Quick action dengan keyboard
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + F untuk focus ke filter
            if ((e.ctrlKey || e.metaKey) && e.key === 'f') {
                e.preventDefault();
                const filterDate = document.querySelector('input[name="filter_date"]');
                if (filterDate) {
                    filterDate.focus();
                }
            }
            
            // Ctrl/Cmd + P untuk print
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                // Default browser print sudah handle ini
            }
        });
        
        // Status counter update
        function updateStatistics() {
            const badges = document.querySelectorAll('.badge');
            let stats = {
                hadir: 0,
                izin: 0,
                sakit: 0,
                alpha: 0,
                belum: 0
            };
            
            badges.forEach(badge => {
                const text = badge.textContent.toLowerCase();
                if (text.includes('hadir')) stats.hadir++;
                else if (text.includes('izin')) stats.izin++;
                else if (text.includes('sakit')) stats.sakit++;
                else if (text.includes('alpha')) stats.alpha++;
                else if (text.includes('belum')) stats.belum++;
            });
            
            return stats;
        }
        
        // Log activity untuk debugging
        console.log('📱 User Agent:', navigator.userAgent);
        console.log('🌐 Online Status:', navigator.onLine ? 'Online' : 'Offline');
        console.log('📍 Current Page: Monitoring Piket');
        console.log('📅 Filter Date: <?php echo $filter_date; ?>');
        
        // Network status monitor
        window.addEventListener('online', function() {
            console.log('✅ Koneksi internet tersedia');
        });
        
        window.addEventListener('offline', function() {
            console.warn('⚠️ Tidak ada koneksi internet');
            alert('Peringatan: Koneksi internet terputus. Beberapa fitur mungkin tidak berfungsi.');
        });
    </script>
</body>
</html>