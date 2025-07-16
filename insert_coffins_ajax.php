<?php
// Suppress PHP errors from being output
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'php_errors.log');

session_start();
include 'db.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => '', 'codes' => []];

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in.']);
    exit;
}

$username = $_SESSION['username'];
try {
    $stmtUser = $conn->prepare("SELECT Name FROM login_details WHERE Username = ?");
    $stmtUser->execute([$username]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    $updatedBy = $user ? $user['Name'] : 'Unknown';
} catch (Exception $e) {
    error_log("User query error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Failed to fetch user data.']);
    exit;
}

// Get POST data
$branch = $_POST['branch'] ?? '';
$storage = $_POST['storage'] ?? '';
$arrival_date = !empty($_POST['arrival_date']) ? $_POST['arrival_date'] : date('Y-m-d');
$coffin_types = $_POST['coffin_type'] ?? [];
$statuses = $_POST['status'] ?? [];
$action_dates = $_POST['action_date'] ?? [];

// Validate inputs
if (!$branch || !$storage || !$arrival_date) {
    echo json_encode(['success' => false, 'message' => 'Branch, storage, or arrival date is missing.']);
    exit;
}

// Allowed statuses
$valid_statuses = ['In-stock', 'Damage', 'Write-off', 'Own coffin'];

// Validate storage
$allowedStoreBranches = ['Hlotse', 'Lithoteng'];
if (!in_array($branch, $allowedStoreBranches) && strtolower($storage) === 'store room') {
    echo json_encode(['success' => false, 'message' => 'Invalid Storage: Store room is only available for Hlotse and Lithoteng.']);
    exit;
}

// Region mapping
$regionMap = [
    'Central' => ['MASERU', 'TY', 'KOLONYAMA', 'MANTSEBO', 'MAJANE', 'LITHOTENG', 'MOTSEKUOA', 'MAKHAKHE', 'NYAKOSOBA', 'SEMONGKONG', 'TSAKHOLO'],
    'North' => ['BOKONG', 'HLOTSE', 'MAPUTSOE', 'MARAKABEI', 'RAMPAI', 'MOEKETSANE', 'MPHOROSANE', 'PITSENG'],
    'South' => ['MOHALE\'S HOEK', 'MPHAKI', 'QUTHING', 'MT. MOOROSI', 'THABANA MORENA'],
    'Highlands' => ['MANTSONYANE', 'MOKHOTLONG', 'QACHA\'S NEK', 'THABA TSEKA']
];

function getRegion($branch, $regionMap) {
    $branch = strtoupper(trim($branch));
    foreach ($regionMap as $region => $branches) {
        if (in_array($branch, $branches)) {
            return $region;
        }
    }
    return 'UNKNOWN';
}

function generatePrefix($name) {
    $name = strtoupper(trim($name));
    $specialCases = [
        'COFFIN' => 'COF',
        'CASKET' => 'CAS',
        'CANNONDALE' => 'CA',
        'PECAN' => 'PE',
        'LTL' => 'LTL',
        '6*6' => '6*6',
        '2.6' => '2.6'
    ];

    $words = explode(' ', $name);
    $prefix = '';
    foreach ($words as $word) {
        if (isset($specialCases[$word])) {
            $prefix .= $specialCases[$word];
        } else {
            $prefix .= preg_replace('/[^A-Z0-9.\-]/', '', $word[0] ?? '');
        }
    }
    return $prefix;
}

$region = getRegion($branch, $regionMap);
$codes = [];

try {
    // Start transaction
    $conn->beginTransaction();

    // Validate array lengths
    if (count($coffin_types) !== count($statuses) || count($coffin_types) !== count($action_dates)) {
        echo json_encode(['success' => false, 'message' => 'Mismatched input array lengths.']);
        $conn->rollBack();
        exit;
    }

    $inserted = 0;
    for ($i = 0; $i < count($coffin_types); $i++) {
        $coffin_type = trim($coffin_types[$i]);
        if ($coffin_type === '') {
            $codes[] = '';
            continue;
        }

        $status = trim($statuses[$i]);
        $action_date = !empty($action_dates[$i]) ? $action_dates[$i] : date('Y-m-d');

        // Validate status
        if (!in_array($status, $valid_statuses)) {
            echo json_encode(['success' => false, 'message' => "Invalid status: $status"]);
            $conn->rollBack();
            exit;
        }

        // Generate unique coffin code
        $dateObj = new DateTime($arrival_date);
        $codeDatePart = $dateObj->format('dMy');
        $prefix = generatePrefix($coffin_type);
        $codeBase = $prefix . '-' . $codeDatePart;

        $monthYearPattern = $dateObj->format('M') . $dateObj->format('y');
        $stmt = $conn->prepare("SELECT code FROM coffins WHERE code LIKE ?");
        $stmt->execute([$prefix . '-%' . $monthYearPattern . '-%']);
        $existingCodes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $maxSuffix = 0;
        foreach ($existingCodes as $existingCode) {
            $parts = explode('-', $existingCode);
            $suffix = end($parts);
            if (is_numeric($suffix)) {
                $maxSuffix = max($maxSuffix, (int)$suffix);
            }
        }

        $newSuffix = str_pad($maxSuffix + 1, 3, '0', STR_PAD_LEFT);
        $code = $codeBase . '-' . $newSuffix;
        $codes[] = $code;

        // Log data for debugging
        error_log("Attempting to insert into coffins: code=$code, coffin_type=$coffin_type, region=$region, branch=$branch, storage=$storage, status=$status, transfer_location='', previous_location='', arrival_date=$arrival_date, action_date=$action_date");

        // Insert into coffins
        try {
            $stmt = $conn->prepare("
                INSERT INTO coffins (
                    region, branch, coffin_type, code, storage, status,
                    transfer_location, previous_location, arrival_date, action_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $region, $branch, $coffin_type, $code, $storage, $status,
                '', '', $arrival_date, $action_date
            ]);
        } catch (PDOException $e) {
            error_log("Coffins insert error for code $code: " . $e->getMessage());
            throw new PDOException("Failed to insert into coffins: " . $e->getMessage());
        }

        // Insert into coffin_trail
        try {
            $stmt = $conn->prepare("
                INSERT INTO coffin_trail (
                    region, branch, coffin_type, code, storage, status,
                    previous_location, arrival_date, action_date,
                    updated_by, action_performed, updated_at
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $region, $branch, $coffin_type, $code, $storage, $status,
                '', $arrival_date, $action_date, $updatedBy, 'Inserted'
            ]);
        } catch (PDOException $e) {
            error_log("Coffin_trail insert error for code $code: " . $e->getMessage());
            throw new PDOException("Failed to insert into coffin_trail: " . $e->getMessage());
        }

        $inserted++;
    }

    if ($inserted > 0) {
        $conn->commit();
        $response['success'] = true;
        $response['message'] = "$inserted coffin(s) inserted successfully.";
        $response['codes'] = $codes;
    } else {
        $conn->rollBack();
        $response['success'] = false;
        $response['message'] = 'No valid coffin data provided.';
    }
} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Insert error: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = 'Database error: ' . $e->getMessage();
} catch (Exception $e) {
    $conn->rollBack();
    error_log("General error: " . $e->getMessage());
    $response['success'] = false;
    $response['message'] = 'Insert failed: ' . $e->getMessage();
}

echo json_encode($response);
exit;
?>