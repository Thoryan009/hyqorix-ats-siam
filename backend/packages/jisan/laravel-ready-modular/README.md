
# 📦 Laravel Ready Modular

> 🚀 A clean, scalable, ready-to-use modular architecture generator for Laravel applications.

Laravel Ready Modular helps you generate fully structured modules with Controller, Service, Repository, Request, Resource, Migration, Seeder and more — following clean architecture and best practices.

---
## How to Integrate the package

Add this to `composer.json` 

 ```php
 "require": {
        "jisan/laravel-ready-modular": "^1.0",
    },
```
Then run `composer update`


## ✨ Features

- ✅ Modular folder structure inside `app/Modules`
- ✅ CRUD-ready architecture
- ✅ Service layer support (plain / cache)
- ✅ Sub-entity support inside a module
- ✅ Optional module linking
- ✅ Module-based migration & seeding
- ✅ Clean separation of concerns
- ✅ Scalable enterprise-ready structure

---



## 📂 Generated Module Structure

When you run:

```bash
php artisan make:module Product
```

It generates:

```
app/
 └── Modules/
     └── Product/
         ├── Config/
         │   └── cache.php
         ├── Controllers/
         │   └── ProductController.php
         ├── Migrations/
         │   └── 2024_XX_XX_create_products_table.php
         ├── Models/
         │   └── Product.php
         ├── Repositories/
         │   └── ProductRepository.php
         ├── Requests/
         │   └── ProductRequest.php
         ├── Resources/
         │   └── ProductResource.php
         ├── Routes/
         │   └── api.php
         ├── Seeders/
         │   └── ProductSeeder.php
         └── Services/
             └── ProductService.php
         └── ModuleServiceProvider.php
             
        
```


## 🚀 Usage

---

### 🧱 Create Module

```bash
php artisan make:module ModuleName
```

### Available Options

| Option | Description |
|--------|------------|
| `--type=crud` | Module type (`crud`, `auth`, `setting`) |
| `--service=cache` | Service type (`plain`, `cache`) |
| `--link=User` | Link module with existing module |

### Example

```bash
php artisan make:module Product --type=crud --service=cache
```

---

### 🔗 Linked Module Example

```bash
php artisan make:module Order --link=User
```

If the linked module does not exist, execution will safely stop.

---

## 🧩 Create Sub-Entity Inside Module

Generate a child entity inside an existing module:

```bash
php artisan make:sub-entity Product ProductDetail
```

### Options

| Option | Description |
|--------|------------|
| `--service=plain ` | Service type `plain`, `cache` (Default) |
| `--short` | Exclude Model, Migration, Seeder files |

### Example

```bash
php artisan make:sub-entity Product ProductDetail 
```

 Generated inside:

```
app/Modules/Product/
 ├── Controllers/ProductDetailController.php
 ├── Models/ProductDetail.php
 ├── Services/ProductDetailService.php
 ├── Repositories/ProductDetailRepository.php
 ├── Requests/ProductDetailRequest.php
 ├── Resources/ProductDetailResource.php
 └── Seeders/ProductDetailSeeder.php
```

---

## 📦 Install a Module (Migrate + Seed)

```bash
php artisan module:install Product
```

This will:

1. Run module migrations
2. Execute module seeder

---

## 🌱 Run Seeder Only

```bash
php artisan module:seed Product
```

Seeder class format:

```
App\Modules\Product\Seeders\ProductSeeder
```

---

## 🏗 Architecture Philosophy

Laravel Ready Modular follows:

- Controller → Service → Repository pattern
- Clean separation of responsibilities
- Config-driven cache support
- Module-based migration isolation
- Scalable enterprise structure

This ensures:

- Better maintainability
- Independent module logic
- Easy feature scaling
- Team-friendly architecture

---

## 📌 Module Types

| Type | Description |
|------|------------|
| `crud` | Standard CRUD module |
| `auth` | Authentication-related module |
| `setting` | Configuration-based module |

---

## 🧠 Service Types

| Service | Description |
|---------|------------|
| `plain` | Basic service |
| `cache` | Service with caching support |

---

## 🛡 Safety Checks

- Prevents duplicate module creation
- Validates linked module existence
- Validates sub-entity parent module
- Ensures seeder exists before execution

---

## 📁 Base Module Location

All modules are created inside:

```
app/Modules/
```

---

## 🎯 Why Use This Package?

- Avoid repetitive boilerplate
- Enforce architectural consistency
- Speed up backend development
- Clean enterprise-level structure
- Production-ready modular system

---

## 👨‍💻 Author

**Jisan**

---

## 📜 License

MIT License
