<?php
// 1. Header Keamanan & Format (Penting untuk mengatasi CORS)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include 'koneksi.php';

// Cek Metode Request
$method = $_SERVER['REQUEST_METHOD'];
$response = [];

// Fungsi pembantu untuk mengambil input (baik dari Form-Data maupun JSON Raw)
function get_input() {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        $input = $_POST;
    }
    return $input;
}

switch ($method) {
    case 'GET':
        // --- READ: Mengambil data ---
        $query = mysqli_query($koneksi, "SELECT * FROM users");
        $users = [];
        while ($row = mysqli_fetch_assoc($query)) {
            $users[] = $row;
        }
        $response = ["status" => "success", "data" => $users];
        break;

    case 'POST':
        // --- CREATE: Menambah data ---
        $input = get_input();
        $nama = $input['nama'] ?? null;
        $sandi = $input['sandi'] ?? null;

        if ($nama && $sandi) {
            $q = mysqli_query($koneksi, "INSERT INTO users (nama, sandi) VALUES ('$nama', '$sandi')");
            if ($q) {
                http_response_code(201);
                $response = ["status" => "success", "message" => "Data berhasil ditambah"];
            } else {
                $response = ["status" => "error", "message" => mysqli_error($koneksi)];
            }
        } else {
            $response = ["status" => "error", "message" => "Nama dan sandi harus diisi"];
        }
        break;

    case 'PUT':
        // --- UPDATE: Mengubah data ---
        $input = get_input();
        $id = $_GET['id'] ?? ($input['id'] ?? null);
        $nama = $input['nama'] ?? null;
        $sandi = $input['sandi'] ?? null;

        if ($id && $nama && $sandi) {
            $q = mysqli_query($koneksi, "UPDATE users SET nama='$nama', sandi='$sandi' WHERE id=$id");
            if ($q) {
                $response = ["status" => "success", "message" => "Data ID $id berhasil diperbarui"];
            } else {
                $response = ["status" => "error", "message" => mysqli_error($koneksi)];
            }
        } else {
            $response = ["status" => "error", "message" => "ID, nama, dan sandi harus lengkap"];
        }
        break;

    case 'DELETE':
        // --- DELETE: Menghapus data ---
        $id = $_GET['id'] ?? null;
        if ($id) {
            $q = mysqli_query($koneksi, "DELETE FROM users WHERE id=$id");
            if ($q) {
                $response = ["status" => "success", "message" => "Data ID $id berhasil dihapus"];
            } else {
                $response = ["status" => "error", "message" => mysqli_error($koneksi)];
            }
        } else {
            $response = ["status" => "error", "message" => "ID tidak disertakan"];
        }
        break;

    case 'OPTIONS':
        // Mengatasi preflight request dari browser
        http_response_code(200);
        exit;

    default:
        $response = ["status" => "error", "message" => "Metode HTTP tidak diizinkan"];
        break;
}

// Tampilkan output akhir
echo json_encode($response, JSON_PRETTY_PRINT);
