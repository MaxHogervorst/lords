# Laravel Octane Best Practices & Guidelines

## ✅ Current Setup Status

Your Octane implementation follows best practices:

### Configuration
- ✅ FrankenPHP as the server (best performance)
- ✅ Auto-scaled workers based on CPU cores
- ✅ Max 500 requests per worker (prevents memory leaks)
- ✅ OPcache enabled with CLI support
- ✅ OPcache preloading configured
- ✅ Proper supervisor configuration with graceful shutdown
- ✅ OCTANE_HTTPS configured for production
- ✅ No Request/Container injection in constructors found

---

## 🚨 Critical: Avoiding Memory Leaks

### ❌ NEVER Do This

```php
class UserController extends Controller
{
    // BAD: Request injected in constructor
    public function __construct(
        protected Request $request  // ❌ Will be stale!
    ) {}
}

class ReportService
{
    // BAD: Container injected in constructor
    public function __construct(
        protected Application $app  // ❌ Will be stale!
    ) {}
}

class OrderProcessor
{
    // BAD: Static array accumulates data
    protected static array $orders = [];  // ❌ Never cleared!

    public function process($order) {
        static::$orders[] = $order;  // Memory leak!
    }
}
```

### ✅ Do This Instead

```php
class UserController extends Controller
{
    // GOOD: Inject Request in method
    public function show(Request $request, $id)
    {
        // Request is fresh for each request
    }
}

class ReportService
{
    // GOOD: Use method injection or resolve()
    public function generate()
    {
        $cache = app('cache');  // Fresh each time
        // Or: $cache = resolve(CacheManager::class);
    }
}

class OrderProcessor
{
    // GOOD: Use instance property, or external storage
    protected array $orders = [];  // Cleared per request

    public function process($order) {
        $this->orders[] = $order;
    }
}
```

---

## 🔧 Configuration Details

### Octane Listeners (config/octane.php)

Your configuration uses the default listeners:

**Before Each Request:**
- `prepareApplicationForNextOperation()` - Resets framework state
- `prepareApplicationForNextRequest()` - Prepares for new request
- `EnsureRequestServerPortMatchesScheme` - Validates HTTPS settings

**After Each Request:**
- `FlushUploadedFiles` - Cleans up temp files
- `FlushTemporaryContainerInstances` - Removes scoped bindings
- `FlushArrayCache` - Clears in-memory cache
- `CollectGarbage` - Triggers GC if needed (50MB threshold)

**Custom Listeners:**
If you have app-specific state to reset, add to `RequestTerminated`:

```php
'listeners' => [
    RequestTerminated::class => [
        FlushUploadedFiles::class,
        App\Octane\Listeners\FlushCustomState::class,  // Your custom listener
    ],
],
```

### Warm vs Flush Bindings

**Warm** (Pre-loaded when worker starts):
```php
'warm' => [
    ...Octane::defaultServicesToWarm(),
    // Add services that are expensive to boot:
    // 'redis',
    // 'cache',
],
```

**Flush** (Re-resolved for each request):
```php
'flush' => [
    // Add services that hold request-specific state:
    // 'session',
    // 'auth',
],
```

⚠️ **Default is usually correct** - Only customize if you have specific needs.

---

## 📊 Performance Configuration

### Current Settings

```ini
# OPcache (Dockerfile.production)
opcache.enable_cli=1              # Required for Octane
opcache.memory_consumption=128    # 128MB for opcache
opcache.max_accelerated_files=20000
opcache.preload=/app/preload.php  # Preload core classes

# PHP
memory_limit=256M                 # Per worker
max_execution_time=30             # Request timeout

# Supervisor
workers=auto                      # Auto-scale to CPU cores
max-requests=500                  # Restart after 500 requests
stopwaitsecs=30                   # Graceful shutdown time
```

### Tuning for Different Environments

**512MB RAM (Current):**
```bash
# 1-2 workers, 256MB each
--workers=auto --max-requests=500
```

**1GB RAM:**
```bash
# 2-3 workers, 256MB each
--workers=3 --max-requests=1000
```

**2GB+ RAM:**
```bash
# 4-8 workers, 256MB each
--workers=8 --max-requests=1000
memory_limit=256M
```

---

## 🧪 Testing With Octane

### Local Development

```bash
# With file watching (auto-restart on changes)
php artisan octane:start --watch

# Development mode (no caching between requests)
php artisan octane:start --workers=1 --max-requests=1
```

⚠️ `--max-requests=1` negates Octane benefits - only use for debugging!

### Load Testing

```bash
# Apache Bench
ab -n 1000 -c 10 http://localhost:8080/

# Check memory usage
docker stats lords-app

# Check worker status
docker-compose exec app php artisan octane:status
```

---

## 🔍 Common Issues & Solutions

### Issue: Response Times Slow After Deployment

**Cause:** OPcache not warmed up yet
**Solution:** Wait 1-2 minutes, or make a few requests to warm cache

### Issue: Memory Usage Growing

**Cause:** Memory leak (static arrays, closures capturing large objects)
**Solution:**
1. Check for static properties
2. Review closures in event listeners
3. Lower `--max-requests` value

### Issue: Stale Data Between Requests

**Cause:** Request/Container injected in constructor
**Solution:** Use method injection or `resolve()` in methods

### Issue: Session Not Working

**Cause:** Session driver not compatible with Octane
**Solution:** Use `redis` or `database` session driver (not `file`)

---

## 📝 Code Review Checklist

Before deploying code with Octane:

- [ ] No `Request` injection in constructors
- [ ] No `Application` injection in constructors
- [ ] No static arrays that accumulate data
- [ ] Session driver is `redis` or `database`
- [ ] Cache driver is not `file` (use `redis`)
- [ ] Closures don't capture large objects
- [ ] Event listeners are stateless
- [ ] Jobs/Commands are stateless

---

## 🔗 Resources

- [Laravel Octane Documentation](https://laravel.com/docs/12.x/octane)
- [FrankenPHP Documentation](https://frankenphp.dev/)
- Your Octane config: `config/octane.php`
- Your OPcache preload: `preload.php`

---

## 🚀 Deployment Checklist

- [ ] Set `OCTANE_HTTPS=true` in production .env
- [ ] Set `OCTANE_SERVER=frankenphp` in .env
- [ ] Configure `LOG_STACK=single,nightwatch` for Nightwatch integration
- [ ] Use `redis` for session and cache
- [ ] Run behind HTTPS load balancer (DigitalOcean handles this)
- [ ] Monitor memory with `docker stats`
- [ ] Monitor performance with Laravel Nightwatch
- [ ] Set up alerts for worker crashes

---

## 💡 Pro Tips

1. **Preload Your Models**: Add frequently used models to `preload.php`
2. **Use Redis**: For sessions, cache, and queues in production
3. **Monitor Worker Restarts**: Frequent restarts = memory leak
4. **Test Before Deploy**: Run load tests locally with Docker
5. **Graceful Deployments**: Use `php artisan octane:reload` for zero-downtime

---

Last Updated: 2025-10-19
