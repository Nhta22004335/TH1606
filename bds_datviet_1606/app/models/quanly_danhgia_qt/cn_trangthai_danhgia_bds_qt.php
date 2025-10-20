<?php
// File: ../../models/cn_danhgia.php

require_once "../../../config/database.php"; 

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Yêu cầu không hợp lệ.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null; // ID của đánh giá (danh_gia_bds.id)
    $action = $_POST['action'] ?? null; // 'hide', 'show', 'delete'

    if ($id && $action) {
        try {
            $pdo = ketnoicsdl();
            $bds_id = null; // Biến để lưu id_bds

            // Bắt đầu transaction
            $pdo->beginTransaction();

            // Lấy id_bds TRƯỚC khi xóa/cập nhật
            $stmt_get_bds = $pdo->prepare("SELECT id_bds FROM danh_gia_bds WHERE id = ?");
            $stmt_get_bds->execute([$id]);
            $bds_id = $stmt_get_bds->fetchColumn();

            if (!$bds_id) {
                throw new Exception("Không tìm thấy đánh giá.");
            }

            if ($action === 'delete') {
                $stmt = $pdo->prepare("DELETE FROM danh_gia_bds WHERE id = ?");
                $stmt->execute([$id]);
                $response['message'] = 'Đã xóa đánh giá.';
            } elseif ($action === 'hide' || $action === 'show') {
                $newStatus = ($action === 'show') ? 'hien' : 'an';
                $stmt = $pdo->prepare("UPDATE danh_gia_bds SET trang_thai = ? WHERE id = ?");
                $stmt->execute([$newStatus, $id]);
                $response['message'] = ($newStatus === 'hien' ? 'Đã hiện' : 'Đã ẩn') . ' đánh giá!';
                $response['new_status'] = $newStatus;
            } else {
                 throw new Exception("Hành động không hỗ trợ.");
            }

            // ==========================================================
            // == THAY ĐỔI LỚN: TÍNH TOÁN VÀ TRẢ VỀ DỮ LIỆU TỔNG HỢP ==
            // ==========================================================
            
            // Tính toán lại điểm TB và Tổng số (chỉ tính các đánh giá đang 'hien')
            // LƯU Ý: Nếu bạn muốn tính TẤT CẢ (kể cả ẩn), hãy bỏ "WHERE trang_thai = 'hien'"
            $sql_agg = "
                SELECT 
                    COUNT(id) AS new_total,
                    ROUND(AVG(diem), 1) AS new_avg
                FROM danh_gia_bds 
                WHERE id_bds = ? 
            "; // Giả sử tính TẤT CẢ đánh giá cho cột bên trái
            
            $stmt_agg = $pdo->prepare($sql_agg);
            $stmt_agg->execute([$bds_id]);
            $agg_data = $stmt_agg->fetch(PDO::FETCH_ASSOC);

            $response['success'] = true;
            $response['new_total_count'] = (int)($agg_data['new_total'] ?? 0);
            $response['new_avg_score'] = (float)($agg_data['new_avg'] ?? 0); // Đảm bảo trả về số, kể cả khi là 0

            // Hoàn tất transaction
            $pdo->commit();

        } catch (Exception $e) { // Dùng Exception chung
            $pdo->rollBack(); // Hoàn tác nếu có lỗi
            error_log("Lỗi trong cn_danhgia.php: " . $e->getMessage());
            $response['message'] = $e->getMessage();
        }
    } else {
        $response['message'] = 'Thiếu ID hoặc hành động.';
    }
}

echo json_encode($response);
?>