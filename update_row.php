<?php
session_start();
include 'db.php';
header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // 1. Validate input
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || !isset($data['code'])) {
        throw new Exception("Invalid input data.");
    }

    $code = $data['code'];
    $statusChanged = false;

    // 2. Get logged-in user's full name
    if (!isset($_SESSION['username'])) {
        throw new Exception("User not logged in.");
    }

    $username = $_SESSION['username'];
    $stmtUser = $conn->prepare("SELECT Name FROM login_details WHERE Username = ?");
    $stmtUser->execute([$username]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $updatedBy = $user ? $user['Name'] : 'Unknown';

    // 3. Get current coffin row
    $stmt = $conn->prepare("SELECT * FROM coffins WHERE code = ?");
    $stmt->execute([$code]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$current) {
        throw new Exception("Coffin with code $code not found.");
    }

    // 4. Handle transfer logic
    if (!empty($data['transfer_location'])) {
        $data['previous_location'] = $current['branch'];
        $data['branch'] = $data['transfer_location'];
    }

    // 5. Auto-update region
    $regionMap = [
        'Central' => ['MASERU', 'TY', 'KOLONYAMA', 'MANTSEBO', 'MAJANE', 'LITHOTENG', 'MOTSEKUOA', 'MAKHAKHE', 'NYAKOSOBA', 'SEMONGKONG', 'TSAKHOLO'],
        'North' => ['BOKONG', 'HLOTSE', 'MAPUTSOE', 'MARAKABEI', 'RAMPAI', 'MOEKETSANE', 'MPHOROSANE', 'PITSENG'],
        'South' => ['MOHALE\'S HOEK', 'MPHAKI', 'QUTHING', 'MT. MOOROSI', 'THABANA MORENA'],
        'Highlands' => ['MANTSONYANE', 'MOKHOTLONG', 'QACHA\'S NEK', 'THABA TSEKA']
    ];
    if (isset($data['branch'])) {
        $branchUpper = strtoupper(trim($data['branch']));
        foreach ($regionMap as $region => $branches) {
            if (in_array($branchUpper, $branches)) {
                $data['region'] = $region;
                break;
            }
        }
    }

    // 6. Auto-update storage
    $allowedStorageBranches = ['HLOTSE', 'LITHOTENG'];
    if (isset($data['branch'])) {
        $branchUpper = strtoupper(trim($data['branch']));
        if (!in_array($branchUpper, $allowedStorageBranches)) {
            $data['storage'] = 'Show room';
        }
    }

    // 7. Update the main table
    $fields = [
        'region', 'branch', 'coffin_type', 'code', 'storage', 'status',
        'transfer_location', 'previous_location', 'arrival_date'
    ];

    $updates = [];
    $values = [];

    foreach ($fields as $field) {
        if (isset($data[$field])) {
            $updates[] = "$field = ?";
            $values[] = $data[$field];
            if ($field === 'status') {
                $statusChanged = true;
            }
        }
    }

    if ($statusChanged) {
        $updates[] = "action_date = ?";
        $values[] = date('Y-m-d');
    }

    if (empty($updates)) {
        throw new Exception("No fields to update.");
    }

    $values[] = $code;
    $sql = "UPDATE coffins SET " . implode(', ', $updates) . " WHERE code = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($values);

    // 8. Calculate durations
    $arrivalDate = $data['arrival_date'] ?? $current['arrival_date'];
    $actionDate = $statusChanged ? date('Y-m-d') : ($data['action_date'] ?? $current['action_date']);

    $inStoreDuration = $arrivalDate ? (new DateTime($arrivalDate))->diff(new DateTime())->days : null;
    $actionDuration = $actionDate ? (new DateTime($actionDate))->diff(new DateTime())->days : null;

    // 9. Insert into coffin_trail
    $trailStmt = $conn->prepare("
        INSERT INTO coffin_trail (
            region, branch, coffin_type, code, storage, arrival_date, status,
            transfer_location, previous_location, action_date,
            in_store_duration, action_duration, action_performed,
            updated_by, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $trailStmt->execute([
        $data['region'], $data['branch'], $data['coffin_type'], $data['code'], $data['storage'],
        $arrivalDate, $data['status'], $data['transfer_location'], $data['previous_location'],
        $actionDate, $inStoreDuration, $actionDuration,
        'Edited', $updatedBy
    ]);

    // 10. Return success
    $response['success'] = true;
    $response['message'] = 'Coffin updated and trail recorded.';

} catch (Exception $e) {
    $response['message'] = 'Update failed: ' . $e->getMessage();
}

echo json_encode($response);
