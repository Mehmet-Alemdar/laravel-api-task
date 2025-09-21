# Laravel Comment API - Kurulum Rehberi

## 🚀 Kurulum Seçenekleri

Bu proje hem **lokal PostgreSQL** hem de **Docker** ortamında çalışacak şekilde yapılandırılmıştır.

---

## 📊 Lokal Kurulum (PostgreSQL)

### Gereksinimler
- PHP 8.1+
- PostgreSQL 13+
- Composer
- Redis (opsiyonel, performans için önerilen)

### 1. Projeyi Klonlayın
```bash
git clone <repository-url>
cd laravel-api-task
```

### 2. Bağımlılıkları Yükleyin
```bash
composer install
```

### 3. Environment Dosyasını Hazırlayın
```bash
cp env.local.example .env
```

### 4. PostgreSQL Veritabanı Oluşturun
```bash
# PostgreSQL'e bağlanın
psql -U postgres

# Veritabanı oluşturun
CREATE DATABASE laravel_api_local;

# Kullanıcı oluşturun (opsiyonel)
CREATE USER laravel_user WITH PASSWORD 'password';
GRANT ALL PRIVILEGES ON DATABASE laravel_api_local TO laravel_user;
```

### 5. .env Dosyasını Düzenleyin
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=laravel_api_local
DB_USERNAME=postgres  # veya laravel_user
DB_PASSWORD=password   # gerçek şifrenizi girin
```

### 6. Uygulama Anahtarı Oluşturun
```bash
php artisan key:generate
```

### 7. Veritabanı Migrasyonları ve Seed
```bash
php artisan migrate --seed
```

### 8. Uygulamayı Başlatın
```bash
# Ana uygulama
php artisan serve

# Queue worker (ayrı terminal)
php artisan queue:work
```

### 🚀 Redis ile Performans Artırımı (Opsiyonel)
```bash
# Redis'i başlatın
redis-server

# .env dosyasında aktifleştirin:
CACHE_STORE=redis
QUEUE_CONNECTION=redis
```

---

## 🐳 Docker Kurulum

### Gereksinimler
- Docker
- Docker Compose

### 1. Projeyi Klonlayın
```bash
git clone <repository-url>
cd laravel-api-task
```

### 2. Docker ile Başlatın
```bash
docker-compose up -d
```

### 3. Uygulama Anahtarı Oluşturun
```bash
docker-compose exec app php artisan key:generate
```

### 4. Veritabanı Migrasyonları
```bash
docker-compose exec app php artisan migrate --seed
```

### ✅ Docker Avantajları
- ✅ PostgreSQL otomatik kurulur
- ✅ Redis otomatik kurulur ve yapılandırılır
- ✅ Queue worker otomatik çalışır
- ✅ Nginx otomatik yapılandırılır
- ✅ Tüm cache testleri çalışır

---

## 🧪 Test Çalıştırma

### Lokal Ortamda
```bash
# Tüm testler
php artisan test

# Sadece Feature testler
php artisan test --testsuite=Feature

# Sadece Unit testler
php artisan test --testsuite=Unit
```

### Docker Ortamında
```bash
# Tüm testler
docker-compose exec app php artisan test

# Cache testleri de dahil (Redis sayesinde)
docker-compose exec app php artisan test --filter=cache
```

---

## 📋 API Endpoints

### Authentication
```bash
POST /api/auth/login
```

### Articles
```bash
GET  /api/articles
GET  /api/articles/{id}
```

### Comments
```bash
GET  /api/articles/{id}/comments    # Public
POST /api/articles/{id}/comments    # Auth Required + Rate Limited
```

---

## ⚙️ Konfigürasyon

### Comment Sistemi
```env
# Yasaklı kelimeler
BANNED_KEYWORDS="spam,badword,offensive"

# Cache süresi (dakika)
COMMENTS_CACHE_TTL=60

# Rate limit (dakika başına)
COMMENT_RATE_LIMIT=10
```

### Queue İşleme
- **Lokal**: Database driver (PostgreSQL)
- **Docker**: Redis driver (daha hızlı)

### Cache Sistemi
- **Lokal**: Database cache (PostgreSQL) veya Redis
- **Docker**: Redis cache (cache tags destekli)

---

## 🔧 Sorun Giderme

### PostgreSQL Bağlantı Problemi
```bash
# PostgreSQL servisinin çalıştığını kontrol edin
sudo systemctl status postgresql

# Bağlantı testı
psql -U postgres -h 127.0.0.1 -d laravel_api_local
```

### Queue İşleme Problemi
```bash
# Queue tablosunu kontrol edin
php artisan queue:monitor

# Failed jobs
php artisan queue:failed
```

### Cache Problemi
```bash
# Cache temizle
php artisan cache:clear

# Config cache temizle
php artisan config:clear
```

---

## 📈 Performans Önerileri

1. **Redis Kullanın**: Cache ve queue için Redis kullanın
2. **Queue Worker**: Production'da supervisor ile queue worker çalıştırın
3. **Database İndeksleri**: Comments tablosunda gerekli indeksler mevcut
4. **Cache Stratejisi**: Comment listesi sayfalama ile cache'lenir

---

## 🏗️ Geliştirme

### Yeni Test Ekleme
```bash
# Feature test
php artisan make:test NewFeatureTest

# Unit test
php artisan make:test NewUnitTest --unit
```

### Migration Oluşturma
```bash
php artisan make:migration create_new_table
```

### Job Oluşturma
```bash
php artisan make:job NewJobName
```
