# Filament Dynamic Menu

Database-driven dynamic navigation menus for Filament panels with multi-tenancy support.

Alih-alih mendefinisikan navigasi secara statis di `PanelProvider`, package ini memungkinkan kamu mengelola sidebar menu langsung dari Filament admin panel — termasuk grup, item, visibility berbasis role/permission, badge, dan pengaturan sidebar.

---

## Persyaratan

- PHP `^8.2`
- Laravel `^11.0`
- Filament `^5.0`

---

## Langkah Instalasi

### 1. Install via Composer

```bash
composer require susantokun/filament-dynamic-menu
```

### 2. Jalankan Perintah Install

```bash
php artisan filament-dynamic-menu:install
```

Perintah ini akan:
- Mempublish file konfigurasi (`config/filament-dynamic-menu.php`)
- Mempublish file migrasi ke `database/migrations/`
- Mempublish file terjemahan (EN & ID)
- Mempublish file views
- Menanyakan apakah ingin langsung menjalankan migrasi

> Gunakan opsi `--force` jika kamu ingin menimpa file yang sudah ada:
> ```bash
> php artisan filament-dynamic-menu:install --force
> ```

### 3. Aktifkan Dynamic Menu

Buka `.env` dan tambahkan:

```env
FILAMENT_DYNAMIC_MENU_ENABLED=true
```

Atau ubah langsung di `config/filament-dynamic-menu.php`:

```php
'enabled' => true,
```

### 4. Daftarkan Plugin di PanelProvider

Buka `app/Providers/Filament/AdminPanelProvider.php` (atau PanelProvider yang kamu gunakan), lalu tambahkan `FilamentDynamicMenuPlugin` ke `->plugins()`:

```php
<?php

namespace App\Providers\Filament;

use Filament\Panel;
use Filament\PanelProvider;
use Susantokun\FilamentDynamicMenu\FilamentDynamicMenuPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->plugins([
                FilamentDynamicMenuPlugin::make(),
            ]);
    }
}
```

> **Selesai!** Cukup tambahkan plugin, semua resource, page, cluster, dan navigasi otomatis terdaftar. Tidak perlu trait, tidak perlu daftar manual resource/page satu per satu.

### 5. (Opsional) Konfigurasi Role & Permission

Jika kamu menggunakan `spatie/laravel-permission`, tentukan model Role dan Permission di `config/filament-dynamic-menu.php`:

```php
'role_model' => \Spatie\Permission\Models\Role::class,
'permission_model' => \Spatie\Permission\Models\Permission::class,
```

Jika kamu menggunakan `bezhansalleh/filament-shield`, package ini akan otomatis me-resolve nama permission dari Resource/Page/Cluster target.

---

## Integrasi Legacy (Trait)

Jika kamu membutuhkan kontrol lebih granular (misalnya panel berbeda dengan konfigurasi berbeda), gunakan trait `HasDynamicMenu`:

Ubah `registration_mode` di config ke `auto`:

```php
'registration_mode' => 'auto',
```

Lalu di PanelProvider:

```php
use Susantokun\FilamentDynamicMenu\Traits\HasDynamicMenu;

class AdminPanelProvider extends PanelProvider
{
    use HasDynamicMenu;

    public function panel(Panel $panel): Panel
    {
        return $this->dynamicMenuSidebar(
            $this->dynamicMenu($panel)
        );
    }
}
```

---

## Hasil Akhir

Setelah semua langkah selesai, buka Filament admin panel kamu. Di sidebar akan muncul **Settings > Menu Settings** yang berisi:

| Menu | Fungsi |
|------|--------|
| **Groups** | Kelola grup navigasi (nama, icon, urutan, visibility, role) |
| **Items** | Kelola item navigasi (Resource, Page, Cluster, URL, Separator) |
| **Sidebar** | Pengaturan sidebar: collapsible groups, sidebar collapse |

Saat pertama kali, jika belum ada data menu, package akan otomatis menjalankan `DefaultMenuSeeder` yang membuat:

- **Dashboard** — item di root sidebar
- **Settings** — grup navigasi yang berisi:
  - **Menu Settings** — akses ke cluster management menu
---

## Konfigurasi Penting

| Key | Default | Keterangan |
|-----|---------|------------|
| `enabled` | `false` | Master switch dynamic menu |
| `tenant_mode` | `single` | `single`, `stancl`, atau `custom` |
| `tenant_model` | `null` | FQCN model tenant (untuk mode `custom`) |
| `cache.ttl` | `86400` | Cache TTL dalam detik (24 jam) |
| `shield_integration` | `true` | Auto-resolve permission Shield |
| `auto_seed_on_empty` | `true` | Auto-seed saat data menu kosong |
| `registration_mode` | `plugin` | `plugin` (rekomendasi) atau `auto` (legacy) |
| `panel_id` | `admin` | ID panel (hanya untuk mode `auto`) |

---

## Facade

Package ini menyediakan facade `DynamicMenu`:

```php
use Susantokun\FilamentDynamicMenu\Facades\DynamicMenu;

DynamicMenu::isEnabled();   // cek apakah dynamic menu aktif
DynamicMenu::tenantMode();  // mode tenant yang digunakan
DynamicMenu::getSidebar();  // ambil pengaturan sidebar tenant
DynamicMenu::clearCache();  // hapus cache menu
```

---

## Multi-Tenancy

Package mendukung 3 mode tenant:

- **`single`** — Tidak ada tenant, menu berlaku global.
- **`stancl`** — Menggunakan package `stancl/tenancy`.
- **`custom`** — Menggunakan model tenant kustom kamu sendiri. Set `tenant_model` di config.

Saat mode selain `single`, semua tabel akan memiliki kolom `tenant_id` dan data menu akan di-scope per tenant.

---

## Lisensi

MIT
