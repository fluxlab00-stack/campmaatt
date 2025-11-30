<?php
/**
 * Utility Functions
 * Common helper functions used throughout the application
 */

/**
 * Get base path for links based on current location
 * @return string
 */
function getBasePath() {
    // Determine if we're in root or pages directory
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    if (strpos($scriptPath, '/pages/') !== false) {
        return '../';
    }
    return '';
}

/**
 * Generate URL with correct base path
 * @param string $path Relative path
 * @return string
 */
function baseUrl($path = '') {
    return getBasePath() . ltrim($path, '/');
}

/**
 * Sanitize input data
 * @param string $data Input data
 * @return string
 */
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Validate email address
 * @param string $email Email to validate
 * @return bool
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validate phone number (Nigerian format)
 * @param string $phone Phone number
 * @return bool
 */
function validatePhone($phone) {
    // Remove spaces and special characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    // Check if it's a valid Nigerian phone number (11 digits starting with 0 or 10 digits)
    return preg_match('/^(0[789][01]\d{8}|[789][01]\d{8})$/', $phone);
}

/**
 * Generate WhatsApp link from phone number
 * @param string $phone Phone number
 * @return string
 */
function generateWhatsAppLink($phone) {
    // Remove spaces and special characters
    $phone = preg_replace('/[^0-9]/', '', $phone);
    
    // Add country code if not present
    if (substr($phone, 0, 3) !== '234' && substr($phone, 0, 1) === '0') {
        $phone = '234' . substr($phone, 1);
    } elseif (substr($phone, 0, 3) !== '234') {
        $phone = '234' . $phone;
    }
    
    return WHATSAPP_BASE_URL . $phone;
}

/**
 * Format price to Nigerian Naira
 * @param float $price Price amount
 * @return string
 */
function formatPrice($price) {
    if ($price == 0) {
        return 'Free';
    }
    return '₦' . number_format($price, 2);
}

/**
 * Get time ago format
 * @param string $datetime Datetime string
 * @return string
 */
function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;
    
    $periods = [
        'year' => 31536000,
        'month' => 2592000,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1
    ];
    
    foreach ($periods as $key => $value) {
        $count = floor($difference / $value);
        if ($count > 0) {
            return $count . ' ' . $key . ($count > 1 ? 's' : '') . ' ago';
        }
    }
    
    return 'just now';
}

/**
 * Upload image file
 * @param array $file File from $_FILES
 * @param string $directory Target directory (listings, profiles, lost_found)
 * @return array ['success' => bool, 'filename' => string, 'error' => string]
 */
function uploadImage($file, $directory = 'listings') {
    $response = ['success' => false, 'filename' => '', 'error' => ''];
    
    // Check if file was uploaded
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        $response['error'] = 'No file uploaded';
        return $response;
    }
    
    // Validate file size
    if ($file['size'] > MAX_FILE_SIZE) {
        $response['error'] = 'File size exceeds maximum allowed (5MB)';
        return $response;
    }
    
    // Validate file type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        $response['error'] = 'Invalid file type. Only JPEG, PNG, and GIF are allowed';
        return $response;
    }
    
    // Generate unique filename
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    $targetPath = UPLOAD_PATH . $directory . '/' . $filename;
    
    // Create directory if it doesn't exist
    if (!is_dir(UPLOAD_PATH . $directory)) {
        mkdir(UPLOAD_PATH . $directory, 0755, true);
    }
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        $response['success'] = true;
        $response['filename'] = 'assets/uploads/' . $directory . '/' . $filename;
    } else {
        $response['error'] = 'Failed to upload file';
    }
    
    return $response;
}

/**
 * Delete uploaded file
 * @param string $filepath File path relative to root
 * @return bool
 */
function deleteFile($filepath) {
    $fullPath = __DIR__ . '/../' . $filepath;
    if (file_exists($fullPath) && is_file($fullPath)) {
        return unlink($fullPath);
    }
    return false;
}

/**
 * Paginate results
 * @param int $totalItems Total number of items
 * @param int $currentPage Current page number
 * @param int $itemsPerPage Items per page
 * @return array ['total_pages', 'offset', 'current_page']
 */
function paginate($totalItems, $currentPage = 1, $itemsPerPage = ITEMS_PER_PAGE) {
    $totalPages = ceil($totalItems / $itemsPerPage);
    $currentPage = max(1, min($currentPage, $totalPages));
    $offset = ($currentPage - 1) * $itemsPerPage;
    
    return [
        'total_pages' => $totalPages,
        'offset' => $offset,
        'current_page' => $currentPage,
        'items_per_page' => $itemsPerPage
    ];
}

/**
 * Generate random string
 * @param int $length Length of string
 * @return string
 */
function generateRandomString($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

/**
 * Redirect to URL
 * @param string $url URL to redirect to
 */
function redirect($url) {
    header("Location: " . $url);
    exit();
}

/**
 * Get full URL path
 * @param string $path Relative path
 * @return string
 */
function url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

/**
 * Truncate text
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add
 * @return string
 */
function truncate($text, $length = 100, $suffix = '...') {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Check if string is valid JSON
 * @param string $string String to check
 * @return bool
 */
function isJson($string) {
    json_decode($string);
    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * Fetch marketplace listings with shared query logic
 * Ensures homepage and marketplace page show the same data
 * 
 * @param Database $db Database instance
 * @param array $options Query options
 * @return array Array with 'listings' and 'total' keys
 */
function getMarketplaceListings($db, $options = []) {
    // Extract options with defaults
    $userCampusId = $options['userCampusId'] ?? null;
    $currentState = $options['currentState'] ?? null;
    $limit = $options['limit'] ?? ITEMS_PER_PAGE;
    $offset = $options['offset'] ?? 0;
    $search = $options['search'] ?? '';
    $category = $options['category'] ?? '';
    $minPrice = $options['minPrice'] ?? 0;
    $maxPrice = $options['maxPrice'] ?? 0;
    $condition = $options['condition'] ?? '';
    $isFree = $options['isFree'] ?? false;
    $isAvailableToday = $options['isAvailableToday'] ?? false;
    $sortBy = $options['sortBy'] ?? 'newest';
    $includeBookmarks = $options['includeBookmarks'] ?? false;
    $currentUserId = $options['currentUserId'] ?? 0;
    
    // Build WHERE conditions
    $whereConditions = ["l.status = 'active'"];
    $params = [];
    $types = "";
    
    // Search filter
    if (!empty($search)) {
        $whereConditions[] = "(l.title LIKE ? OR l.description LIKE ?)";
        $searchParam = "%{$search}%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $types .= "ss";
    }
    
    // Category filter
    if (!empty($category)) {
        $whereConditions[] = "c.category_name = ?";
        $params[] = $category;
        $types .= "s";
    }
    
    // Price range filters
    if ($minPrice > 0) {
        $whereConditions[] = "l.price >= ?";
        $params[] = $minPrice;
        $types .= "d";
    }
    
    if ($maxPrice > 0) {
        $whereConditions[] = "l.price <= ?";
        $params[] = $maxPrice;
        $types .= "d";
    }
    
    // Condition filter
    if (!empty($condition)) {
        $whereConditions[] = "l.condition_status = ?";
        $params[] = $condition;
        $types .= "s";
    }
    
    // Free items filter
    if ($isFree) {
        $whereConditions[] = "l.is_free = 1";
    }
    
    // Available today filter
    if ($isAvailableToday) {
        $whereConditions[] = "l.is_available_today = 1";
    }
    
    $whereClause = implode(' AND ', $whereConditions);
    
    // Determine sort order
    $orderBy = match($sortBy) {
        'price_low' => 'l.price ASC',
        'price_high' => 'l.price DESC',
        'oldest' => 'l.posted_at ASC',
        'popular' => 'l.views_count DESC',
        default => 'l.posted_at DESC'
    };
    
    // Build the base query
    $selectClause = "SELECT l.*, u.first_name, u.last_name, c.category_name,
        (SELECT image_url FROM listing_images WHERE listing_id = l.listing_id AND is_primary = 1 LIMIT 1) as primary_image";
    
    // Add bookmark status if user is logged in
    if ($includeBookmarks && $currentUserId > 0) {
        $selectClause .= ", (SELECT bookmark_id FROM bookmarks WHERE user_id = {$currentUserId} AND listing_id = l.listing_id) as is_bookmarked";
    }
    
    $selectClause .= " FROM listings l
        JOIN users u ON l.user_id = u.user_id
        JOIN categories c ON l.category_id = c.category_id";
    
    // Get total count - different logic based on campus awareness
    if (!$userCampusId) {
        // Not logged in or no campus: count all
        $countQuery = "SELECT COUNT(*) as total FROM listings l 
            JOIN categories c ON l.category_id = c.category_id 
            JOIN users u ON l.user_id = u.user_id
            WHERE {$whereClause}";
        
        if (!empty($params)) {
            $stmt = $db->prepare($countQuery, $types, $params);
            $stmt->execute();
        } else {
            $stmt = $db->getConnection()->prepare($countQuery);
            $stmt->execute();
        }
        
        $totalResult = $stmt->get_result();
        $totalRows = 0;
        if ($totalResult) {
            $row = $totalResult->fetch_assoc();
            $totalRows = $row['total'] ?? 0;
        }
        $stmt->close();
    } else {
        // Campus-aware counting
        if (!$currentState) {
            // GPS unavailable: count only from registered campus
            $countQuery = "SELECT COUNT(*) as total FROM listings l 
                JOIN users u ON l.user_id = u.user_id
                WHERE u.campus_id = {$userCampusId} AND l.status = 'active'";
            $res = $db->query($countQuery);
            $totalRows = $res ? (int)($res->fetch_assoc()['total'] ?? 0) : 0;
        } else {
            // GPS available: count from both sources
            $countQuery1 = "SELECT COUNT(*) as cnt FROM listings l 
                JOIN users u ON l.user_id = u.user_id
                WHERE u.campus_id = {$userCampusId} AND l.status = 'active'";
            $res1 = $db->query($countQuery1);
            $totalA = $res1 ? (int)($res1->fetch_assoc()['cnt'] ?? 0) : 0;
            
            $totalB = 0;
            if ($currentState) {
                $campRes = $db->prepare("SELECT campus_id FROM campuses WHERE state = ?", "s", [$currentState]);
                $campIds = [];
                if ($campRes) {
                    $campRes->execute();
                    $cres = $campRes->get_result();
                    while ($cr = $cres->fetch_assoc()) $campIds[] = (int)$cr['campus_id'];
                    $campRes->close();
                }
                
                if (!empty($campIds)) {
                    $in = implode(',', array_map('intval', $campIds));
                    $countQuery2 = "SELECT COUNT(*) as cnt FROM listings l 
                        JOIN users u ON l.user_id = u.user_id
                        WHERE u.campus_id IN ({$in}) AND l.status = 'active'";
                    $res2 = $db->query($countQuery2);
                    $totalB = $res2 ? (int)($res2->fetch_assoc()['cnt'] ?? 0) : 0;
                }
            }
            $totalRows = $totalA + $totalB;
        }
    }
    
    // Fetch listings
    $listings = [];
    
    if (!$userCampusId) {
        // Not campus-aware: simple query with pagination
        $query = $selectClause . " WHERE {$whereClause} ORDER BY {$orderBy} LIMIT ? OFFSET ?";
        
        $allParams = array_merge($params, [$limit, $offset]);
        $allTypes = $types . "ii";
        
        $stmt = $db->prepare($query, $allTypes, $allParams);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $listings[] = $row;
        }
        $stmt->close();
    } else {
        // Campus-aware: apply location logic
        if (!$currentState) {
            // GPS unavailable: show only campus listings
            $query = $selectClause . " WHERE u.campus_id = {$userCampusId} AND l.status = 'active'
                ORDER BY {$orderBy} LIMIT ? OFFSET ?";
            
            $stmt = $db->prepare($query, "ii", [$limit, $offset]);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $listings[] = $row;
            }
            $stmt->close();
        } else {
            // GPS available: mix 60% campus + 40% current state
            $neededTotal = $offset + $limit;
            $needA = (int)ceil($neededTotal * 0.6);
            $needB = max(10, $neededTotal - $needA);
            
            // Fetch from registered campus
            $queryA = $selectClause . " WHERE u.campus_id = {$userCampusId} AND l.status = 'active'
                ORDER BY {$orderBy} LIMIT ?";
            $stmtA = $db->prepare($queryA, "i", [$needA]);
            $stmtA->execute();
            $resA = $stmtA->get_result();
            $itemsA = [];
            while ($r = $resA->fetch_assoc()) $itemsA[] = $r;
            $stmtA->close();
            
            // Fetch from current state campuses
            $itemsB = [];
            $campRes = $db->prepare("SELECT campus_id FROM campuses WHERE state = ?", "s", [$currentState]);
            $campIds = [];
            if ($campRes) {
                $campRes->execute();
                $cres = $campRes->get_result();
                while ($cr = $cres->fetch_assoc()) $campIds[] = (int)$cr['campus_id'];
                $campRes->close();
            }
            
            if (!empty($campIds)) {
                $in = implode(',', array_map('intval', $campIds));
                $queryB = $selectClause . " WHERE u.campus_id IN ({$in}) AND l.status = 'active'
                    ORDER BY {$orderBy} LIMIT ?";
                $stmtB = $db->prepare($queryB, "i", [$needB]);
                $stmtB->execute();
                $resB = $stmtB->get_result();
                while ($r = $resB->fetch_assoc()) $itemsB[] = $r;
                $stmtB->close();
            }
            
            // Merge, shuffle, and slice
            $merged = array_merge($itemsA, $itemsB);
            if (!empty($merged)) shuffle($merged);
            $listings = array_slice($merged, $offset, $limit);
        }
    }
    
    return [
        'listings' => $listings,
        'total' => $totalRows
    ];
}

