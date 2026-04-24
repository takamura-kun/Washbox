# WashBox - Development Guidelines

## Code Quality Standards

### PHP Backend Patterns

**Namespace Organization**
- All classes use proper namespace declarations (e.g., `App\Http\Controllers\Admin`)
- Organized by functional domain (Controllers, Models, Services, etc.)

**Class Structure**
- Clear separation of concerns with dedicated sections marked by comment blocks
- Consistent use of visibility modifiers (public, protected, private)
- Logical grouping: relationships → scopes → accessors → methods

**Eloquent Model Conventions**
- `$fillable` array explicitly defines mass-assignable attributes
- `$casts` array for type casting (datetime, array, boolean)
- Relationships defined with proper return types and foreign keys
- Scopes prefixed with `scope` and chainable for query building

**Method Naming**
- Scopes use descriptive names: `scopeUnclaimed()`, `scopeByBranch()`, `scopeReadyForDisposal()`
- Factory methods use `create*` prefix: `createLaundryReceived()`, `createPickupSubmitted()`
- Helper methods use verb-noun pattern: `markAsRecovered()`, `updateDaysUnclaimed()`
- Boolean methods use `is*` or `can*` prefix: `isRead()`, `canBeDisposed()`

**Documentation**
- PHPDoc comments for public methods with parameter and return types
- Section headers using comment blocks for logical grouping
- Inline comments for complex logic or business rules

### JavaScript/React Native Patterns

**Component Structure**
- Functional components with hooks (useState, useEffect, useCallback, useRef)
- Clear separation of concerns: data fetching, state management, rendering
- Organized sections with comment headers (e.g., `// ─── Data Fetching ───`)

**Styling Approach**
- Centralized color system (COLORS object) for consistent theming
- StyleSheet.create() for performance optimization
- Responsive design using Dimensions.get('window')
- Gradient components for modern UI effects

**State Management**
- useState for local component state
- useCallback for memoized callbacks to prevent unnecessary re-renders
- useRef for persistent values across renders
- Animated API for smooth transitions

**Data Fetching**
- Async/await pattern with try-catch error handling
- AsyncStorage for local persistence
- Fallback to cached data on network errors
- Token-based authentication with Bearer scheme

**Naming Conventions**
- Helper functions use camelCase: `getGreeting()`, `getStatusColor()`, `formatStatus()`
- Constants use UPPER_SNAKE_CASE: `SCREEN_WIDTH`, `STORAGE_KEYS`
- Component names use PascalCase: `ServiceCard`, `LaundryCard`, `PromoCard`

### Database Query Patterns

**Query Optimization**
- Eager loading with `with()` to prevent N+1 queries
- Relationship counting with `withCount()` for aggregations
- Conditional relationships using closures for filtering
- Pagination with `paginate()` for large datasets

**Aggregation**
- `sum()`, `avg()`, `count()` for calculations
- `groupBy()` with `select()` for grouped results
- `havingRaw()` for complex filtering on aggregates
- Raw SQL expressions with `DB::raw()` when needed

**Date Handling**
- Carbon library for date manipulation
- `whereBetween()` for date range queries
- `whereMonth()`, `whereYear()` for temporal filtering
- `now()` for current timestamp

## Semantic Patterns

### Notification System
- **Factory Methods**: Static methods create and send notifications atomically
- **Type System**: Notification types map to icons and colors for UI consistency
- **FCM Integration**: Automatic push notification sending with status tracking
- **Retry Logic**: Failed notifications can be retried with `retryFcm()`
- **Bulk Operations**: `broadcastToAll()` and `broadcastToBranch()` for mass notifications

### Unclaimed Laundry Management
- **Status Tracking**: Three states (unclaimed, recovered, disposed) with timestamps
- **Urgency Levels**: Dynamic urgency calculation based on days unclaimed
- **Storage Fees**: Automatic calculation after 7-day threshold
- **Audit Trail**: User tracking for recovery and disposal actions
- **Statistics**: Branch-level and global stats for reporting

### Report Generation
- **Date Filtering**: Flexible date range selection with preset filters
- **Aggregation**: Multi-level grouping (by service, branch, payment method)
- **Export**: CSV export functionality for all report types
- **Metrics**: Calculated fields for analysis (percentages, averages, trends)
- **Pagination**: Large datasets paginated for performance

### Mobile UI Components
- **Card-Based Layout**: Consistent card styling with shadows and borders
- **Gradient Backgrounds**: Professional gradient system for visual hierarchy
- **Status Indicators**: Color-coded status badges for quick scanning
- **Empty States**: Helpful empty state cards with CTAs
- **Animations**: Fade and slide animations for smooth transitions

## Architectural Patterns

### Service Layer
- Business logic encapsulated in service classes
- Controllers delegate to services for cleaner separation
- Services handle complex operations and external integrations

### Observer Pattern
- Model observers trigger automatic actions on state changes
- Used for notifications, logging, and data synchronization

### Repository Pattern (Implicit)
- Eloquent models act as repositories
- Query logic encapsulated in scopes
- Relationships define data access paths

### Factory Pattern
- Static factory methods for object creation
- `createAndSend()` pattern for notifications
- `syncFromLaundry()` for model synchronization

### Strategy Pattern
- Different notification types use same interface
- Urgency levels determine message content and styling
- Status-based behavior changes (recovered vs disposed)

## Best Practices

### Error Handling
- Try-catch blocks for external service calls
- Graceful fallbacks (e.g., cached data on network failure)
- Logging of errors with context information
- User-friendly error messages

### Performance Optimization
- Query optimization with eager loading
- Pagination for large datasets
- Caching with AsyncStorage on mobile
- Memoization with useCallback for expensive operations
- Time-sliced execution for long-running tasks

### Security
- Input validation on all user inputs
- SQL injection prevention via Eloquent ORM
- XSS protection through proper escaping
- CSRF protection via Laravel middleware
- Secure token storage in AsyncStorage

### Code Organization
- Logical grouping of related functionality
- Clear naming conventions for discoverability
- Consistent formatting and indentation
- Comprehensive comments for complex logic

### Testing Considerations
- Scopes designed for easy query testing
- Factory methods for test data creation
- Stateless service methods for unit testing
- Mock-friendly dependency injection

## Common Code Idioms

### Chaining Queries
```php
$query->where('status', 'unclaimed')
    ->where('days_unclaimed', '>=', 14)
    ->with('laundry', 'customer')
    ->orderBy('created_at', 'desc')
    ->paginate(50);
```

### Conditional Relationships
```php
$query->with(['laundries' => function($q) use ($startDate, $endDate) {
    $q->whereBetween('created_at', [$startDate, $endDate]);
}])
```

### Map and Filter Collections
```php
$branches->map(function($branch) {
    return ['name' => $branch->name, 'revenue' => $branch->revenue];
})->filter(fn($b) => $b['revenue'] > 0)->values();
```

### Ternary and Null Coalescing
```php
$value = $customer->ratings?->avg('rating') ?? 0;
$status = $days >= 14 ? 'critical' : ($days >= 7 ? 'urgent' : 'normal');
```

### Async Data Loading
```javascript
const fetchAllData = async () => {
    try {
        await Promise.all([
            fetchCustomer(),
            fetchLaundries(),
            fetchPromotions(),
        ]);
    } catch (error) {
        console.error('Error:', error);
    }
};
```

### Conditional Rendering
```javascript
{activeLaundries.length > 0 ? (
    <View>
        {activeLaundries.map(laundry => (
            <LaundryCard key={laundry.id} laundry={laundry} />
        ))}
    </View>
) : (
    <EmptyStateCard />
)}
```

## Frequently Used Annotations

### PHP Attributes/Docblocks
- `@param` - Parameter documentation
- `@return` - Return type documentation
- `@throws` - Exception documentation
- `use HasFactory` - Trait for model factories

### JavaScript Comments
- `// ─── Section Header ───` - Visual section markers
- `// TODO:` - Future improvements
- `// NOTE:` - Important information
- `// HACK:` - Temporary workarounds

## Performance Considerations

### Database
- Use indexes on frequently queried columns
- Avoid N+1 queries with eager loading
- Batch operations for bulk updates
- Use raw queries only when necessary

### Frontend
- Lazy load images and components
- Memoize expensive computations
- Debounce search and filter operations
- Use virtual scrolling for large lists

### Mobile
- Minimize bundle size
- Cache API responses locally
- Use background tasks for heavy operations
- Optimize animations for 60fps
