<?php
// Contains logic for region detection, code generation, etc.

function getRegion($branch) {
    $central = ['Maseru','TY','Kolonyama','Mantsebo','Majane','Lithoteng','Motsekuoa','Makhakhe','Nyakosoba','Semonkong','Tsakholo'];
    $north = ['Bokong','Hlotse','Maputsoe','Marakabei','Rampai','Moeketsane','Mphorosane','Pitseng'];
    $south = ['Mohale\'s Hoek','Mphaki','Quthing','Mt. Moorosi','Thabana Morena'];
    $highlands = ['Mantsonyane','Mokhotlong','Qacha\'s Nek','Thaba Tseka'];

    if (in_array($branch, $central)) return 'Central';
    if (in_array($branch, $north)) return 'North';
    if (in_array($branch, $south)) return 'South';
    if (in_array($branch, $highlands)) return 'Highlands';
    return 'Unknown';
}

function extractModel($name) {
    return strtoupper(preg_replace('/[^0-9A-Z.]/i', '', str_replace(['COFFIN', 'CASKET'], '', $name)));
}

function getInitials($name) {
    preg_match_all('/\b\w/', $name, $matches);
    return strtoupper(implode('', $matches[0]));
}

function generateCode($conn, $coffin_type) {
    $coffin_type = strtoupper(trim($coffin_type));
    $today = date('dM y');  // e.g. 23Jun25
    $month = date('M y');   // e.g. Jun 25

    // Determine prefix
    if (strpos($coffin_type, 'COFFIN') !== false) {
        $prefix = 'COF' . extractModel($coffin_type);
    } elseif (strpos($coffin_type, 'CASKET') !== false) {
        $prefix = 'CAS' . extractModel($coffin_type);
    } elseif (stripos($coffin_type, 'LTL') === 0) {
        $prefix = 'LTL';
    } else {
        $prefix = getInitials($coffin_type);
    }

    // Fetch all serial numbers used this month for this prefix
    $likePattern = "$prefix-$month%";
    $stmt = $conn->prepare("SELECT code FROM coffins WHERE code LIKE ?");
    $stmt->execute(["$likePattern"]);
    $usedSerials = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (preg_match('/-(\d{3})$/', $row['code'], $match)) {
            $usedSerials[] = (int)$match[1];
        }
    }

    // Find the next available serial number
    $next = 1;
    while (in_array($next, $usedSerials)) {
        $next++;
    }

    $serial = str_pad($next, 3, '0', STR_PAD_LEFT);
    return "$prefix-$today-$serial";
}


function date_diff_days($start, $end) {
    $startDate = new DateTime($start);
    $endDate = new DateTime($end);
    return $startDate->diff($endDate)->days;
}
