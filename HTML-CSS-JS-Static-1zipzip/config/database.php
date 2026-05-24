<?php
class Database {
    private static $db_file = __DIR__ . '/../database.sqlite';
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            try {
                self::$connection = new PDO("sqlite:" . self::$db_file);
                self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$connection->exec("PRAGMA foreign_keys = ON");
                self::createTables();
                self::migrate();
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return self::$connection;
    }

    private static function createTables() {
        $sql = "
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            restaurant_name TEXT NOT NULL,
            subscription_status TEXT DEFAULT 'trial',
            selected_theme TEXT DEFAULT 'dark',
            trial_start DATE,
            trial_end DATE,
            subscription_end DATE,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS themes (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT UNIQUE,
            display_name TEXT,
            css_file TEXT,
            price_monthly REAL,
            is_active BOOLEAN DEFAULT 1
        );

        CREATE TABLE IF NOT EXISTS products (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            name TEXT NOT NULL,
            description TEXT,
            price REAL NOT NULL,
            category TEXT,
            image_url TEXT,
            is_available BOOLEAN DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            slug TEXT NOT NULL,
            name TEXT NOT NULL,
            icon TEXT DEFAULT 'utensils',
            color TEXT DEFAULT '#8b5cf6',
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS subscriptions (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            plan_name TEXT,
            start_date DATE,
            end_date DATE,
            amount REAL,
            payment_id TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id)
        );

        INSERT OR IGNORE INTO themes (name, display_name, css_file, price_monthly) VALUES
        ('dark', 'Modern Dark', 'dark.css', 19.99),
        ('light', 'Light Elegant', 'light.css', 19.99),
        ('street', 'Street Food', 'street.css', 29.99),
        ('fine', 'Fine Dining', 'fine.css', 29.99),
        ('cafe', 'Cafe Style', 'cafe.css', 24.99);
        ";
        self::$connection->exec($sql);
    }

    private static function migrate() {
        $db = self::$connection;
        // subscription_requests table
        $db->exec("CREATE TABLE IF NOT EXISTS subscription_requests (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL,
            plan TEXT NOT NULL,
            duration_months INTEGER NOT NULL DEFAULT 1,
            phone TEXT DEFAULT '',
            notes TEXT DEFAULT '',
            status TEXT DEFAULT 'pending',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )");
        // Add social/video/hero columns
        $cols = array_column($db->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC), 'name');
        foreach (['video_url','social_tiktok','social_instagram','social_snapchat','social_location','social_whatsapp','social_facebook','hero_image'] as $col) {
            if (!in_array($col, $cols)) {
                $db->exec("ALTER TABLE users ADD COLUMN $col TEXT DEFAULT ''");
            }
        }
        // Seed default categories for users who have none
        $users = $db->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
        $defaults = [
            ['main',      'الأطباق الرئيسية', 'utensils',   '#8b5cf6', 1],
            ['appetizer', 'المقبلات',          'leaf',        '#22c55e', 2],
            ['drinks',    'المشروبات',          'coffee',      '#3b82f6', 3],
            ['desserts',  'الحلويات',           'cookie',      '#ec4899', 4],
        ];
        $check = $db->prepare("SELECT COUNT(*) FROM categories WHERE user_id=?");
        $ins   = $db->prepare("INSERT OR IGNORE INTO categories (user_id,slug,name,icon,color,sort_order) VALUES (?,?,?,?,?,?)");
        foreach ($users as $uid) {
            $check->execute([$uid]);
            if ($check->fetchColumn() == 0) {
                foreach ($defaults as $d) {
                    $ins->execute([$uid, $d[0], $d[1], $d[2], $d[3], $d[4]]);
                }
            }
        }
    }
}
?>
