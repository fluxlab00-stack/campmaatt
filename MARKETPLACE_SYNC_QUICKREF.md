# Marketplace Sync System - Quick Reference

## What Changed?

✅ **Homepage + Marketplace now use the same database query**
✅ **Automatic sync - no code changes needed when items post/edit/delete**
✅ **No hardcoded items - 100% database driven**

## How to Use

### For Developers

#### Add to any page to fetch marketplace listings:

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
    'includeBookmarks' => isLoggedIn(),
    'currentUserId' => isLoggedIn() ? getCurrentUserId() : 0
]);

$listings = $marketplaceData['listings'];
$total = $marketplaceData['total'];
```

#### For Homepage (shows 8 latest items):

```php
$data = getMarketplaceListings($db, [
    'userCampusId' => $userCampusId,
    'currentState' => $userCurrentState,
    'limit' => 12,
    'offset' => 0
]);

foreach (array_slice($data['listings'], 0, 8) as $item) {
    // Display item
}
```

#### For Marketplace with pagination:

```php
$page = 1;
$perPage = 20;

$data = getMarketplaceListings($db, [
    'userCampusId' => $userCampusId,
    'currentState' => $userCurrentState,
    'limit' => $perPage,
    'offset' => ($page - 1) * $perPage,
    'search' => $_GET['search'] ?? '',
    'category' => $_GET['category'] ?? '',
    'sortBy' => $_GET['sort'] ?? 'newest'
]);

$pagination = paginate($data['total'], $page, $perPage);
```

## Files Modified

| File | Changes | Lines Changed |
|------|---------|---------------|
| `includes/functions.php` | Added `getMarketplaceListings()` | +260 |
| `index.php` | Use shared function for homepage | ~30 |
| `pages/marketplace.php` | Use shared function for marketplace | ~220 |

## Feature Checklist

### Supported Filters
- [x] Search (title + description)
- [x] Category
- [x] Price range (min/max)
- [x] Condition status
- [x] Free items only
- [x] Available today

### Supported Sorts
- [x] Newest first (default)
- [x] Oldest first
- [x] Price: Low to High
- [x] Price: High to Low
- [x] Most Popular

### Campus Awareness
- [x] No campus: show all listings
- [x] Has campus: show campus listings
- [x] GPS + different state: 60% campus + 40% current state

### User Features
- [x] Bookmark integration
- [x] User identification for bookmarks
- [x] Pagination support

## Testing Steps

1. **Homepage Load**
   - Go to `/`
   - Verify 8 marketplace items displayed
   - Note item titles and prices

2. **Marketplace Load**
   - Go to `/pages/marketplace.php`
   - Verify same items appear (if viewing page 1)
   - Should show full list without limit

3. **Post New Item**
   - Click "Post Item" button
   - Submit new listing
   - Return to homepage/marketplace
   - Verify new item appears in both places

4. **Edit Item**
   - Go to user's listings
   - Edit price/title
   - Return to homepage/marketplace
   - Verify changes reflected in both

5. **Delete Item**
   - Delete an item
   - Return to homepage/marketplace
   - Verify item removed from both

6. **Apply Filters**
   - Use marketplace filters (search, category, price)
   - Results should update correctly
   - No sync issues

7. **Pagination**
   - Navigate through pages in marketplace
   - Items should be consistent and non-duplicated

## Troubleshooting

### Homepage shows different items than marketplace
**Solution:** Clear browser cache, verify both pages call `getMarketplaceListings()`

### New item doesn't appear
**Check:**
1. Item status = 'active' in database
2. Page refreshed (not cached)
3. User's campus is correct
4. Item creation succeeded (check error logs)

### Search not working
**Check:**
1. `search` parameter passed to function
2. Search term not empty
3. Database has items matching search

### Pagination broken
**Check:**
1. `limit` and `offset` parameters correct
2. `paginate()` function working
3. Total count is accurate

## Related Files

- `MARKETPLACE_SYNC_SYSTEM.md` - Full technical documentation
- `MARKETPLACE_SYNC_IMPLEMENTATION.md` - Implementation details
- `MARKETPLACE_SYNC_ARCHITECTURE.md` - Architecture diagrams

## Function Reference

### `getMarketplaceListings($db, $options = [])`

```php
/**
 * Fetch marketplace listings with shared query logic
 * Ensures homepage and marketplace page show same data
 * 
 * @param Database $db Database instance
 * @param array $options Query options:
 *   - userCampusId: int (optional) User's campus ID
 *   - currentState: string (optional) User's current state (GPS)
 *   - limit: int (20) Items per page
 *   - offset: int (0) Starting position
 *   - search: string (optional) Search terms
 *   - category: string (optional) Category name
 *   - minPrice: float (optional) Minimum price
 *   - maxPrice: float (optional) Maximum price
 *   - condition: string (optional) Condition status
 *   - isFree: bool (false) Free items only
 *   - isAvailableToday: bool (false) Available today only
 *   - sortBy: string ('newest') Sort order
 *   - includeBookmarks: bool (false) Include bookmark status
 *   - currentUserId: int (0) Current user's ID
 * 
 * @return array ['listings' => [...], 'total' => count]
 */
```

## Example: Adding New Filter

To add a new filter (e.g., "brand"):

1. **Update function call:**
```php
$data = getMarketplaceListings($db, [
    // ... existing options
    'brand' => $_GET['brand'] ?? '',
]);
```

2. **Update function to handle it:**
In `includes/functions.php`, add to WHERE conditions:
```php
if (!empty($options['brand'])) {
    $whereConditions[] = "l.brand = ?";
    $params[] = $options['brand'];
    $types .= "s";
}
```

3. **Done!** Both pages now support the filter

## Performance Notes

- Function uses prepared statements (SQL injection safe)
- Indexed joins on `user_id` and `category_id`
- Subquery for primary image (consider caching if slow)
- Bookmark check uses efficient subquery

## Support & Questions

For questions about the sync system:
1. Check `MARKETPLACE_SYNC_SYSTEM.md` for details
2. Review `MARKETPLACE_SYNC_ARCHITECTURE.md` for flow diagrams
3. Check function documentation in `includes/functions.php`

## Key Takeaway

> **Both the homepage and marketplace page now use the same database query function. Any changes to listings automatically sync across all pages without requiring code modifications.**

This is the single source of truth pattern - query the data once, use it everywhere!
