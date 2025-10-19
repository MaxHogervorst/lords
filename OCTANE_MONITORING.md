# Octane Performance Monitoring

## Expected Resource Usage (512MB DigitalOcean Droplet)

### Memory (Without Supervisord)
- **Baseline**: 180-250MB (normal - app stays in memory)
- **Under Load**: 250-350MB
- **⚠️ Warning**: >400MB sustained
- **🚨 Critical**: >480MB (approaching limit)

### CPU
- **Idle**: 5-15% (workers + Nightwatch)
- **Light Traffic**: 20-40%
- **Heavy Traffic**: 60-80%
- **⚠️ Warning**: >90% sustained
- **🚨 Critical**: 100% for >5 minutes

### Why Higher Than PHP-FPM?

| Metric | PHP-FPM | Octane | Reason |
|--------|---------|--------|--------|
| **Memory** | ~100MB baseline | ~250MB baseline | App stays in memory (this is GOOD!) |
| **CPU** | 0% idle | 5-10% idle | Multiple workers + Nightwatch |
| **Response Time** | 50-100ms | 10-30ms | ⚡ Much faster! |

## Monitoring Commands

### Check Memory Usage
```bash
# Via DigitalOcean CLI or App Platform dashboard
# Look for: "Memory Usage"

# If you can SSH:
free -m
docker stats lords-app --no-stream
```

### Check Octane Worker Status
```bash
# In production console
php artisan octane:status

# Shows:
# - Number of workers
# - Requests handled
# - Memory per worker
```

### Check for Memory Leaks

**Signs of a memory leak:**
- Memory constantly increasing (not plateauing)
- Memory grows 10-20MB per hour
- Eventually crashes with OOM

**Normal behavior:**
- Memory increases on first requests
- Plateaus after ~10-20 minutes
- Stays steady or slightly increases
- Workers restart at 500 requests (by design)

### Check Worker Restarts
```bash
# Look at supervisor logs
docker-compose logs app | grep "Worker.*restart"

# Frequent restarts = potential issue
# Restarts every 500 requests = NORMAL (configured behavior)
```

## Optimization Options

### If Memory is Too High (>400MB sustained)

**Option 1: Reduce Workers**
```bash
# In production env or docker-compose
OCTANE_WORKERS=1  # Instead of auto (2 workers)
```

**Option 2: Reduce Max Requests**
```bash
# In supervisord.conf
--max-requests=250  # Instead of 500
```

**Option 3: Reduce Worker Memory**
```ini
# In production.ini
memory_limit=128M  # Instead of 256M
```

### If CPU is Too High (>80% sustained)

**Option 1: Check for Infinite Loops**
```bash
# Look for processes consuming CPU
docker exec -it lords-app top
```

**Option 2: Reduce Workers**
```bash
OCTANE_WORKERS=1  # Fewer workers = less CPU
```

**Option 3: Check Database Queries**
```bash
# Use Nightwatch to identify slow queries
# Dashboard → Slow Queries
```

## Current Configuration (512MB Droplet)

```yaml
Workers: 1 (manually set)
Memory per worker: 256MB
Max requests: 500
OPcache: 128MB
Process manager: Native shell (no Supervisord)
Total memory budget: ~512MB

Breakdown:
- System: ~50MB
- Octane Worker: ~150MB
- Nightwatch: ~40MB
- OPcache: ~128MB
- Redis/DB clients: ~50MB
- Buffer: ~94MB
Total: ~512MB ✅ Perfect fit!

Saved ~20MB by removing Supervisord overhead!
```

## Recommended Actions

### Week 1: Monitor Baseline
- Check memory/CPU daily
- Look for steady state vs growth
- Note traffic patterns

### Week 2: Optimize if Needed
- If memory >450MB: Reduce to 1 worker
- If CPU >80%: Investigate slow queries
- If response times slow: Check Nightwatch

### Ongoing: Use Nightwatch
- Monitor request times
- Track memory trends
- Alert on anomalies

## When to Scale Up

### Upgrade to 1GB Droplet When:
- Memory consistently >400MB
- CPU consistently >70%
- Traffic growing 50%+
- Want to add more features

### Benefits of 1GB:
- Run 2-3 workers comfortably
- Better performance under load
- More headroom for spikes

## Performance Gains with Octane

Despite higher baseline resource usage, you should see:
- ✅ 3-4x faster response times
- ✅ Better performance under load
- ✅ More consistent latency
- ✅ Handle more concurrent users

**The trade-off is worth it!** Higher baseline memory for much better performance.

## Troubleshooting

### Memory keeps growing
- Check for memory leaks (static arrays, closures)
- Review OCTANE_BEST_PRACTICES.md
- Reduce max-requests to restart workers more often

### CPU always high
- Check for N+1 queries (Nightwatch will show these)
- Look for infinite loops or long-running operations
- Consider adding queues for heavy tasks

### Workers keep crashing
- Check logs: `docker-compose logs app`
- Look for fatal errors or exceptions
- May need to increase memory_limit

### Slower than expected
- Check database connection pooling
- Verify OPcache is enabled
- Review Nightwatch for slow queries
- Check Redis connectivity

---

**Remember:** Higher resource usage is expected and normal with Octane!
The key is that it should be **stable** and **consistent**, not constantly growing.
