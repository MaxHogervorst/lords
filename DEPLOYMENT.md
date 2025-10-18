# Digital Ocean App Platform Deployment Guide

## Prerequisites

1. **Digital Ocean Account** - Sign up at https://www.digitalocean.com
2. **GitHub Account** - Your code must be in a GitHub repository
3. **doctl CLI** (optional) - For command-line deployments: https://docs.digitalocean.com/reference/doctl/

## Cost Estimate

**Development/Testing:**
- App (basic-xxs): $5/month
- MySQL Database (dev): $15/month
- Redis (dev): $15/month
- **Total: ~$35/month**

**Production:**
- App (basic-xs or higher): $12-48/month
- MySQL Database (production): $15-240/month
- Redis (production): $15-240/month
- **Total: ~$42-528/month** (depending on scale)

---

## Step 1: Prepare Your Repository

### 1.1 Push to GitHub

```bash
# Make sure you're on the upgrade branch
git branch

# Push your code to GitHub
git push origin upgrade
```

### 1.2 Update App Configuration

Edit `.do/app.yaml` and update:
```yaml
github:
  branch: upgrade  # ✅ Already updated
  repo: maxhogervorst/lords  # ✅ Already updated
```

---

## Step 2: Generate Application Key

```bash
# Generate a new app key for production
php artisan key:generate --show
```

Copy the output (e.g., `base64:xxxxxxxxxxxxx...`). You'll need this for the deployment.

---

## Step 3: Deploy via Digital Ocean Dashboard

### 3.1 Create New App

1. Go to https://cloud.digitalocean.com/apps
2. Click **"Create App"**
3. Choose **"GitHub"** as source
4. Authorize Digital Ocean to access your GitHub
5. Select repository: `maxhogervorst/lords`
6. Select branch: `upgrade`
7. Click **"Next"**

### 3.2 Configure Resources

DO will auto-detect the `.do/app.yaml` configuration. Review:

- **Web Service**: lords-web (Basic XXS, $5/mo)
- **Database**: MySQL 8 (Dev tier, $15/mo)
- **Database**: Redis (Dev tier, $15/mo)

Click **"Next"**

### 3.3 Set Environment Variables

Click **"Edit"** next to the web service and add/update:

#### Required Secrets:
```
APP_KEY=base64:xxxxxxxxxxxxx  (from Step 2)
SEPA_CREDITOR_NAME=Your Organization Name
SEPA_CREDITOR_IBAN=NLxxXXXXxxxxxxxxxxxx
SEPA_CREDITOR_BIC=ABNANL2A
SEPA_CREDITOR_ID=NL12ZZZ123456789
```

#### Optional Email Configuration:
```
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

Click **"Save"**

### 3.4 Set App Info

- **App Name**: lords
- **Region**: Amsterdam 3 (or your preferred region)

Click **"Next"** then **"Create Resources"**

---

## Step 4: Deploy via Command Line (Alternative)

If you prefer CLI deployment:

```bash
# Install doctl
brew install doctl  # macOS
# or download from: https://docs.digitalocean.com/reference/doctl/

# Authenticate
doctl auth init

# Create app from spec
doctl apps create --spec .do/app.yaml

# Get app ID
doctl apps list

# Update environment variables
doctl apps update YOUR_APP_ID --spec .do/app.yaml
```

---

## Step 5: Monitor Deployment

### 5.1 Watch Build Logs

1. Go to your app in the DO dashboard
2. Click on **"Runtime Logs"** tab
3. Watch for:
   - ✅ Building Docker image
   - ✅ Installing dependencies
   - ✅ Running migrations
   - ✅ Caching routes/config/views
   - ✅ Health check passing

### 5.2 Check Health

After deployment completes:
- Health check endpoint: `https://your-app.ondigitalocean.app/`
- Should see your login page

---

## Step 6: Post-Deployment Setup

### 6.1 Create Admin User

```bash
# Connect to your app console (in DO dashboard: Console tab)
# Or use doctl:
doctl apps logs YOUR_APP_ID --type run

# Run artisan command via console
php artisan tinker

# Create admin user
$user = new App\Models\User();
$user->email = 'admin@example.com';
$user->password = bcrypt('secure-password');
$user->is_admin = true;
$user->save();
exit
```

### 6.2 Configure Storage

If you need persistent file storage for SEPA files:

1. In DO Dashboard, go to **Spaces** (Object Storage)
2. Create a new Space: `lords-storage`
3. Add environment variables:
```
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=ams3
AWS_BUCKET=lords-storage
AWS_ENDPOINT=https://ams3.digitaloceanspaces.com
```

4. Update `config/filesystems.php` to use DO Spaces

---

## Step 7: Configure Custom Domain (Optional)

### 7.1 Add Domain in DO

1. Go to app settings
2. Click **"Domains"** tab
3. Click **"Add Domain"**
4. Enter: `lords.yourdomain.com`

### 7.2 Update DNS

Add CNAME record to your DNS provider:
```
Type: CNAME
Name: lords
Value: your-app.ondigitalocean.app
TTL: 3600
```

### 7.3 Update APP_URL

Update environment variable:
```
APP_URL=https://lords.yourdomain.com
```

---

## Troubleshooting

### Build Fails

**Check logs:**
```bash
doctl apps logs YOUR_APP_ID --type build
```

**Common issues:**
- Missing APP_KEY → Generate with `php artisan key:generate --show`
- Composer dependencies fail → Check composer.json
- Node/npm fails → Check package.json and build scripts

### Health Check Fails

**Check health check logs:**
```bash
doctl apps logs YOUR_APP_ID --type run
```

**Common issues:**
- Database connection → Check DB credentials in environment
- Redis connection → Check Redis credentials
- Permission issues → Check storage/bootstrap/cache permissions
- Migration fails → Check database schema

### Application Errors

**Enable debug mode temporarily:**
```
APP_DEBUG=true
LOG_LEVEL=debug
```

**Check logs:**
1. DO Dashboard → Your App → Runtime Logs
2. Look for PHP errors, SQL errors, or connection issues

### Database Issues

**Reset database (⚠️ destroys all data):**
```bash
# Via console:
php artisan migrate:fresh --force
```

**Run migrations only:**
```bash
php artisan migrate --force
```

---

## Continuous Deployment

### Auto-Deploy on Push

Already configured in `.do/app.yaml`:
```yaml
deploy_on_push: true
```

Every push to `upgrade` branch will:
1. Trigger automatic rebuild
2. Run migrations
3. Deploy new version
4. Run health checks

### Manual Deploy

```bash
# Via CLI
doctl apps create-deployment YOUR_APP_ID

# Via Dashboard
# Go to your app → Deployments → Create Deployment
```

---

## Monitoring & Maintenance

### View Metrics

In DO Dashboard → Your App → Insights:
- CPU usage
- Memory usage
- HTTP requests
- Response times

### Scale Up

If you need more resources:

1. Go to app settings
2. Edit **Web Service**
3. Change instance size:
   - `basic-xxs` → $5/mo (512MB RAM)
   - `basic-xs` → $12/mo (1GB RAM)
   - `basic-s` → $24/mo (2GB RAM)

### Backup Database

DO automatically backs up databases, but you can create manual snapshots:

1. Go to **Databases** → Your MySQL cluster
2. Click **"Backups"** tab
3. Click **"Take Snapshot"**

---

## Security Checklist

- [x] APP_DEBUG=false in production
- [x] Strong APP_KEY set
- [x] Database credentials are secrets
- [x] HTTPS enabled (automatic with DO)
- [ ] Rate limiting configured
- [ ] CORS configured if needed
- [ ] Environment variables never in code
- [ ] Regular security updates

---

## Support

- **Digital Ocean Docs**: https://docs.digitalocean.com/products/app-platform/
- **Laravel Docs**: https://laravel.com/docs
- **App Issues**: Check GitHub Issues or create new one

---

## Cost Optimization Tips

1. **Use Dev Tier** for databases during development ($15/mo vs $25+/mo)
2. **Scale down** app instance if traffic is low
3. **Use Spaces** instead of local storage for files
4. **Enable caching** (Redis) to reduce database load
5. **Monitor usage** regularly to avoid overage charges
