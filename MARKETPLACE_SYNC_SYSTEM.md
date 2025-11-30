# Marketplace Sync System Documentation

## Overview

The marketplace sync system ensures that items shown on the **homepage highlights** always match the items displayed on the **full marketplace page**. Both pages pull data from the same centralized database query, eliminating data inconsistencies and ensuring automatic updates whenever items are posted, edited, or deleted.

## Architecture

### Shared Query Function

**Location:** `includes/functions.php`

**Function:** `getMarketplaceListings($db, $options = [])`

This is the single source of truth for all marketplace listings. Both the homepage and marketplace page use this function to retrieve data.

```php
$marketplaceData = getMarketplaceListings($db, [
    'userCampusId' => $userCampusId,
    'currentState' => $userCurrentState,
    'limit' => 12,
    'offset' => 0,
    'search' => '',
    'category' => '',
    'minPrice' => 0,
    'maxPrice' => 0,
    'condition' => '',
    'isFree' => false,
    'isAvailableToday' => false,
    'sortBy' => 'newest',
    'includeBookmarks' => true,
    'currentUserId' => 0
]);

// Returns:
// [
//     'listings' => [...],
//     'total' => count
// ]
```

## Implementation

### 1. Homepage (index.php)

The homepage fetches 12 items and displays only 8 on the marketplace highlights section.

```php
// Fetch marketplace listings using shared query function
$marketplaceData = getMarketplaceListings($db, [
    'userCampusId' => $userCampusId,
    'currentState' => $userCurrentState,
    'limit' => 12,
    'offset' => 0,
    'sortBy' => 'newest',
    'includeBookmarks' => isLoggedIn(),
    'currentUserId' => isLoggedIn() ? getCurrentUserId() : 0
]);

$allListings = $marketplaceData['listings'];

// Display only 8 items
<?php foreach (array_slice($allListings, 0, 8) as $item): ?>
    <!-- Card rendering -->
<?php endforeach; ?>
```

**Key Features:**
- Fetches latest 12 items
- Displays only 8 items
- Uses same sorting and filtering logic as marketplace page
- Automatically includes bookmark status if user is logged in

### 2. Marketplace Page (pages/marketplace.php)

The marketplace page applies user-selected filters and displays all results with pagination.

```php
// Fetch listings using shared query function
$marketplaceData = getMarketplaceListings($db, [
    'userCampusId' => $userCampusId,
    'currentState' => $userCurrentState,
    'limit' => $perPage,
    'offset' => $offset,
    'search' => $search,
    'category' => $category,
    'minPrice' => $minPrice,
    'maxPrice' => $maxPrice,
    'condition' => $condition,
    'isFree' => $isFree,
    'isAvailableToday' => $isAvailableToday,
    'sortBy' => $sortBy,
    'includeBookmarks' => isLoggedIn(),
    'currentUserId' => isLoggedIn() ? getCurrentUserId() : 0
]);

$listings = $marketplaceData['listings'];
$totalRows = $marketplaceData['total'];
```

**Key Features:**
- Supports all filter options (search, category, price range, condition, etc.)
- Includes pagination
- Respects user-selected sort order
- Integrates with bookmark system

## Query Logic

### 1. Base Query Structure

The shared function builds queries with these components:

```sql
SELECT l.*, u.first_name, u.last_name, c.category_name,
       (primary image subquery) as primary_image,
       (optional bookmark status) as is_bookmarked
FROM listings l
JOIN users u ON l.user_id = u.user_id
JOIN categories c ON l.category_id = c.category_id
WHERE [dynamic conditions]
ORDER BY [sort order]
LIMIT ? OFFSET ?
```

### 2. Dynamic WHERE Conditions

Conditions are built based on filters:

- **Search:** `(l.title LIKE ? OR l.description LIKE ?)`
- **Category:** `c.category_name = ?`
- **Price Range:** `l.price >= ? AND l.price <= ?`
- **Condition:** `l.condition_status = ?`
- **Free Items:** `l.is_free = 1`
- **Available Today:** `l.is_available_today = 1`

### 3. Campus-Aware Logic

The system respects user location and campus:

**Not Logged In / No Campus:**
- Shows all active listings globally

**Logged In / Has Campus / No GPS:**
- Shows only listings from registered campus

**Logged In / Has Campus / GPS Available:**
- Mix logic: 60% from registered campus + 40% from current state
- Items are shuffled to provide variety

### 4. Sort Options

Supported sort orders:

- `newest` (default): `l.posted_at DESC`
- `oldest`: `l.posted_at ASC`
- `price_low`: `l.price ASC`
- `price_high`: `l.price DESC`
- `popular`: `l.views_count DESC`

## Automatic Sync Mechanism

### How It Works

1. **Single Database Query:** Both pages execute the same query logic through `getMarketplaceListings()`
2. **No Hardcoding:** All data comes directly from the database
3. **Real-Time Updates:**
   - When a seller posts a new item → it appears in marketplace highlights on next page load
   - When a seller edits an item → changes reflect immediately
   - When a seller deletes an item → it disappears from both pages

### Updated Endpoints

These endpoints automatically trigger updates across all pages:

- **Post Item:** `includes/listing/create-listing.php`
- **Edit Item:** `includes/listing/update-listing.php`
- **Delete Item:** `includes/listing/delete-listing.php`
- **Mark Sold:** `includes/listing/mark-sold.php`

All of these modify the `listings` table, which is queried by `getMarketplaceListings()`.

## Benefits

✅ **Data Consistency:** Homepage and marketplace page show identical data
✅ **No Duplication:** Single query function reduces code maintenance
✅ **Automatic Sync:** Changes propagate immediately without manual intervention
✅ **Scalable:** Easily add new filters or features in one place
✅ **Performance:** Efficient query with indexed joins
✅ **User Experience:** Users see consistent data across the site

## Example Usage Scenarios

### Scenario 1: New Item Posted
1. Seller posts item via "Post Item" form
2. Item inserted into `listings` table
3. On next page refresh:
   - Homepage shows new item in marketplace highlights
   - Marketplace page shows new item with correct sort order
   - Both pages updated automatically (no code changes needed)

### Scenario 2: Item Edited
1. Seller edits item (price, title, description, etc.)
2. Item updated in `listings` table
3. On next page refresh:
   - Homepage displays updated information
   - Marketplace page shows updated data
   - Changes reflected consistently

### Scenario 3: Item Deleted
1. Seller deletes item
2. Item status changed to 'inactive' in `listings` table
3. On next page refresh:
   - Item disappears from homepage marketplace highlights
   - Item disappears from marketplace page
   - Both pages stay in sync

## Testing Checklist

- [ ] Homepage displays 8 marketplace items
- [ ] Marketplace page displays full list with pagination
- [ ] Post new item → appears in both pages
- [ ] Edit item (price/title) → changes in both pages
- [ ] Delete item → removed from both pages
- [ ] Bookmark item → status shows correctly
- [ ] Filter by category → works on marketplace page
- [ ] Sort by newest/oldest/price → works on marketplace page
- [ ] Search functionality → works correctly
- [ ] Campus-aware logic → respects user location

## Files Modified

- `includes/functions.php` - Added `getMarketplaceListings()` function
- `index.php` - Updated to use shared query function
- `pages/marketplace.php` - Refactored to use shared query function

## Future Enhancements

- Add AJAX refresh to update homepage in real-time
- Implement caching for frequently accessed queries
- Add analytics to track item view counts
- Consider adding trending algorithms for personalization
