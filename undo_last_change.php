<?php
session_start();
include 'db.php';

header('Content-Type: application/json');

$username = $_SESSION['username'] ?? 'unknown';

try {
    // Find last trail record by user
    $stmt = $conn->prepare("SELECT * FROM coffin_trail WHERE changed_by = ? ORDER BY changed_at DESC LIMIT 1");
    $stmt->execute([$username]);
    $lastTrail = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$lastTrail) {
        echo json_encode(['success'=>false, 'message'=>'No changes found to undo']);
        exit;
    }

    $coffin_id = $lastTrail['coffin_id'];
    $action_type = $lastTrail['action_type'];

    $conn->beginTransaction();

    if ($action_type === 'Insert') {
        // Undo insert by deleting the coffin
        $stmtDel = $conn->prepare("DELETE FROM coffins WHERE id = ?");
        $stmtDel->execute([$coffin_id]);
    } elseif ($action_type === 'Update') {
        // Undo update by restoring the previous state
        // Find the previous trail before this one
        $stmtPrev = $conn->prepare("SELECT * FROM coffin_trail WHERE coffin_id = ? AND changed_at < ? ORDER BY changed_at DESC LIMIT 1");
        $stmtPrev->execute([$coffin_id, $lastTrail['changed_at']]);
        $prevTrail = $stmtPrev->fetch(PDO::FETCH_ASSOC);

        if ($prevTrail) {
            $stmtUpdate = $conn->prepare("UPDATE coffins SET branch=?, storage=?, arrival_date=?, coffin_type=?, status=?, transfer_location=?, action_date=?, updated_by=?, updated_at=NOW() WHERE id=?");
            $stmtUpdate->execute([
                $prevTrail['branch'],
                $prevTrail['storage'],
                $prevTrail['arrival_date'],
                $prevTrail['coffin_type'],
                $prevTrail['status'],
                $prevTrail['transfer_location'],
                $prevTrail['action_date'],
                $username,
                $coffin_id
            ]);
        } else {
            // No previous trail found, maybe delete the coffin or do nothing
            // Here let's do nothing, just notify user
            $conn->rollBack();
            echo json_encode(['success'=>false, 'message'=>'No previous state to revert to']);
            exit;
        }
    } elseif ($action_type === 'Delete') {
        // Undo delete by restoring the coffin from trail info
        $stmtInsert = $conn->prepare("INSERT INTO coffins (id, branch, storage, arrival_date, coffin_type, status, transfer_location, action_date, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmtInsert->execute([
            $coffin_id,
            $lastTrail['branch'],
            $lastTrail['storage'],
            $lastTrail['arrival_date'],
            $lastTrail['coffin_type'],
            $lastTrail['status'],
            $lastTrail['transfer_location'],
            $lastTrail['action_date'],
            $username
        ]);
    }

    // Delete this trail record (undo it)
    $stmtDelTrail = $conn->prepare("DELETE FROM coffin_trail WHERE id = ?");
    $stmtDelTrail->execute([$lastTrail['id']]);

    $conn->commit();

    echo json_encode(['success'=>true, 'message'=>'Last change undone successfully']);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(['success'=>false, 'message'=>'Error: '.$e->getMessage()]);
}
